<?php
require_once __DIR__ . '/../config/config.php';

/**
 * API de autenticación
 * Endpoints: POST /api/auth/login, POST /api/auth/logout
 */

class AuthAPI {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Iniciar sesión
     */
    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos
        if (empty($data['email']) || empty($data['password'])) {
            Response::validationError([
                'email' => 'El email es requerido',
                'password' => 'La contraseña es requerida'
            ]);
        }
        
        try {
            // Buscar usuario
            $stmt = $this->db->prepare("
                SELECT u.*, r.nombre as rol_nombre, r.permisos
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE u.email = ? AND u.activo = 1
            ");
            $stmt->execute([$data['email']]);
            $usuario = $stmt->fetch();
            
            if (!$usuario || !password_verify($data['password'], $usuario['password'])) {
                Response::error('Credenciales incorrectas', 401);
            }
            
            // Crear payload para JWT
            $payload = [
                'usuario_id' => $usuario['id'],
                'email' => $usuario['email'],
                'rol' => $usuario['rol_nombre']
            ];
            
            // Generar token
            $token = JWT::encode($payload);
            
            // Guardar sesión en base de datos
            $expiraEn = date('Y-m-d H:i:s', time() + JWT_EXPIRATION_TIME);
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            $stmt = $this->db->prepare("
                INSERT INTO sesiones (usuario_id, token, ip_address, user_agent, expira_en)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $usuario['id'],
                $token,
                $ipAddress,
                $userAgent,
                $expiraEn
            ]);
            
            // Actualizar último acceso y guardar session_token
            $stmt = $this->db->prepare("UPDATE usuarios SET ultimo_acceso = NOW(), session_token = ? WHERE id = ?");
            $stmt->execute([$token, $usuario['id']]);
            
            // Respuesta exitosa
            Response::success([
                'token' => $token,
                'tipo' => 'Bearer',
                'expira_en' => JWT_EXPIRATION_TIME,
                'usuario' => [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'email' => $usuario['email'],
                    'rol' => $usuario['rol_nombre'],
                    'permisos' => json_decode($usuario['permisos'], true)
                ]
            ], 'Inicio de sesión exitoso');
            
        } catch (Exception $e) {
            Response::error('Error al iniciar sesión: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        try {
            $token = JWT::getBearerToken();
            
            if (!$token) {
                Response::error('Token no proporcionado', 400);
            }
            
            // Obtener usuario del token
            $payload = JWT::decode($token);
            
            // Desactivar sesión
            $stmt = $this->db->prepare("UPDATE sesiones SET activo = 0 WHERE token = ?");
            $stmt->execute([$token]);
            
            // Limpiar session_token del usuario
            $stmt = $this->db->prepare("UPDATE usuarios SET session_token = NULL WHERE id = ?");
            $stmt->execute([$payload['usuario_id']]);
            
            Response::success(null, 'Sesión cerrada exitosamente');
            
        } catch (Exception $e) {
            Response::error('Error al cerrar sesión: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener usuario actual
     */
    public function me() {
        $auth = new AuthMiddleware();
        $user = $auth->authenticate();
        
        Response::success($user, 'Datos del usuario');
    }
}

// Router simple
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';

$api = new AuthAPI();

if ($method === 'POST' && $path === '/login') {
    $api->login();
} elseif ($method === 'POST' && $path === '/logout') {
    $api->logout();
} elseif ($method === 'GET' && $path === '/me') {
    $api->me();
} else {
    Response::notFound('Endpoint no encontrado');
}
