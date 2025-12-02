<?php
require_once __DIR__ . '/../config/config.php';

/**
 * API de gestión de usuarios
 * Solo accesible para el dueño
 */

class UsuariosAPI {
    private $db;
    private $auth;
    private $user;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->auth = new AuthMiddleware();
        $this->user = $this->auth->authenticate();
    }
    
    /**
     * Listar todos los usuarios
     */
    public function listar() {
        $this->auth->requireDueno($this->user);
        
        try {
            $stmt = $this->db->query("
                SELECT u.id, u.nombre, u.email, u.rol_id, u.activo, 
                       u.ultimo_acceso, u.created_at, u.session_token,
                       r.nombre as rol_nombre,
                       CASE 
                           WHEN u.session_token IS NOT NULL THEN 1
                           ELSE 0
                       END as esta_conectado,
                       CASE
                           WHEN u.ultimo_acceso IS NULL THEN 'Nunca'
                           WHEN u.session_token IS NOT NULL AND u.ultimo_acceso > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 'En línea'
                           WHEN u.ultimo_acceso > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'Hace poco'
                           WHEN u.ultimo_acceso > DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 'Hoy'
                           ELSE DATE_FORMAT(u.ultimo_acceso, '%d/%m/%Y %H:%i')
                       END as estado_conexion
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                ORDER BY u.created_at DESC
            ");
            $usuarios = $stmt->fetchAll();
            
            Response::success($usuarios, 'Lista de usuarios');
            
        } catch (Exception $e) {
            Response::error('Error al listar usuarios: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener un usuario por ID
     */
    public function obtener($id) {
        $this->auth->requireDueno($this->user);
        
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.nombre, u.email, u.rol_id, u.activo, 
                       u.ultimo_acceso, u.created_at,
                       r.nombre as rol_nombre, r.permisos
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE u.id = ?
            ");
            $stmt->execute([$id]);
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                Response::notFound('Usuario no encontrado');
            }
            
            $usuario['permisos'] = json_decode($usuario['permisos'], true);
            
            Response::success($usuario, 'Datos del usuario');
            
        } catch (Exception $e) {
            Response::error('Error al obtener usuario: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Crear un nuevo usuario
     */
    public function crear() {
        $this->auth->requireDueno($this->user);
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos
        $errores = [];
        if (empty($data['nombre'])) $errores['nombre'] = 'El nombre es requerido';
        if (empty($data['email'])) {
            $errores['email'] = 'El email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errores['email'] = 'Email inválido';
        }
        if (empty($data['password'])) {
            $errores['password'] = 'La contraseña es requerida';
        } elseif (strlen($data['password']) < 6) {
            $errores['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }
        if (empty($data['rol_id'])) $errores['rol_id'] = 'El rol es requerido';
        
        if (!empty($errores)) {
            Response::validationError($errores);
        }
        
        try {
            // Verificar si el email ya existe
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$data['email']]);
            if ($stmt->fetch()) {
                Response::error('El email ya está registrado', 400);
            }
            
            // Crear usuario
            $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
            $activo = isset($data['activo']) ? (int)$data['activo'] : 1;
            
            $stmt = $this->db->prepare("
                INSERT INTO usuarios (nombre, email, password, rol_id, activo)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['nombre'],
                $data['email'],
                $passwordHash,
                $data['rol_id'],
                $activo
            ]);
            
            $userId = $this->db->lastInsertId();
            
            // Obtener usuario creado
            $stmt = $this->db->prepare("
                SELECT u.id, u.nombre, u.email, u.rol_id, u.activo, 
                       r.nombre as rol_nombre
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $usuario = $stmt->fetch();
            
            Response::success($usuario, 'Usuario creado exitosamente', 201);
            
        } catch (Exception $e) {
            Response::error('Error al crear usuario: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Actualizar un usuario
     */
    public function actualizar($id) {
        $this->auth->requireDueno($this->user);
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            // Verificar que el usuario existe
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                Response::notFound('Usuario no encontrado');
            }
            
            // Construir query dinámicamente
            $campos = [];
            $valores = [];
            
            if (isset($data['nombre'])) {
                $campos[] = "nombre = ?";
                $valores[] = $data['nombre'];
            }
            if (isset($data['email'])) {
                // Verificar que el email no exista en otro usuario
                $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
                $stmt->execute([$data['email'], $id]);
                if ($stmt->fetch()) {
                    Response::error('El email ya está en uso por otro usuario', 400);
                }
                $campos[] = "email = ?";
                $valores[] = $data['email'];
            }
            if (isset($data['password']) && !empty($data['password'])) {
                $campos[] = "password = ?";
                $valores[] = password_hash($data['password'], PASSWORD_BCRYPT);
            }
            if (isset($data['rol_id'])) {
                $campos[] = "rol_id = ?";
                $valores[] = $data['rol_id'];
            }
            if (isset($data['activo'])) {
                $campos[] = "activo = ?";
                $valores[] = (int)$data['activo'];
            }
            
            if (empty($campos)) {
                Response::error('No hay datos para actualizar', 400);
            }
            
            $valores[] = $id;
            
            $sql = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($valores);
            
            // Obtener usuario actualizado
            $this->obtener($id);
            
        } catch (Exception $e) {
            Response::error('Error al actualizar usuario: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Eliminar un usuario (desactivar)
     */
    public function eliminar($id) {
        $this->auth->requireDueno($this->user);
        
        try {
            // No permitir eliminar al propio usuario
            if ($id == $this->user['usuario_id']) {
                Response::error('No puedes eliminar tu propio usuario', 400);
            }
            
            $stmt = $this->db->prepare("UPDATE usuarios SET activo = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                Response::notFound('Usuario no encontrado');
            }
            
            Response::success(null, 'Usuario eliminado exitosamente');
            
        } catch (Exception $e) {
            Response::error('Error al eliminar usuario: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Listar roles disponibles
     */
    public function listarRoles() {
        $this->auth->requireDueno($this->user);
        
        try {
            $stmt = $this->db->query("SELECT * FROM roles ORDER BY nombre");
            $roles = $stmt->fetchAll();
            
            foreach ($roles as &$rol) {
                $rol['permisos'] = json_decode($rol['permisos'], true);
            }
            
            Response::success($roles, 'Lista de roles');
            
        } catch (Exception $e) {
            Response::error('Error al listar roles: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Cerrar sesión de un usuario
     */
    public function cerrarSesion($id) {
        $this->auth->requireDueno($this->user);
        
        try {
            // No permitir cerrar la propia sesión
            if ($id == $this->user['usuario_id']) {
                Response::error('No puedes cerrar tu propia sesión', 400);
            }
            
            // Invalidar el token de sesión del usuario
            $stmt = $this->db->prepare("UPDATE usuarios SET session_token = NULL WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                Response::notFound('Usuario no encontrado');
            }
            
            Response::success(null, 'Sesión del usuario cerrada exitosamente');
            
        } catch (Exception $e) {
            Response::error('Error al cerrar sesión: ' . $e->getMessage(), 500);
        }
    }
}

// Router
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$segments = explode('/', trim($path, '/'));

$api = new UsuariosAPI();

if ($method === 'GET' && $path === '/') {
    $api->listar();
} elseif ($method === 'GET' && $path === '/roles') {
    $api->listarRoles();
} elseif ($method === 'GET' && count($segments) === 1 && is_numeric($segments[0])) {
    $api->obtener($segments[0]);
} elseif ($method === 'POST' && $path === '/') {
    $api->crear();
} elseif ($method === 'POST' && count($segments) === 2 && $segments[1] === 'logout') {
    $api->cerrarSesion($segments[0]);
} elseif ($method === 'PUT' && count($segments) === 1 && is_numeric($segments[0])) {
    $api->actualizar($segments[0]);
} elseif ($method === 'DELETE' && count($segments) === 1 && is_numeric($segments[0])) {
    $api->eliminar($segments[0]);
} else {
    Response::notFound('Endpoint no encontrado');
}
