<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Middleware de autenticación
 */
class AuthMiddleware {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Verificar autenticación
     */
    public function authenticate() {
        try {
            // Obtener token
            $token = JWT::getBearerToken();
            
            if (!$token) {
                Response::unauthorized('Token no proporcionado');
            }
            
            // Decodificar token
            $payload = JWT::decode($token);
            
            // Verificar que el token existe en la base de datos y está activo
            // También verificamos que el session_token del usuario coincida (para forzar cierre de sesión)
            $stmt = $this->db->prepare("
                SELECT s.*, u.id as usuario_id, u.nombre, u.email, u.rol_id, u.session_token, 
                       r.nombre as rol_nombre, r.permisos
                FROM sesiones s
                INNER JOIN usuarios u ON s.usuario_id = u.id
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE s.token = ? AND s.activo = 1 AND s.expira_en > NOW() AND u.activo = 1
            ");
            $stmt->execute([$token]);
            $session = $stmt->fetch();
            
            if (!$session) {
                Response::unauthorized('Sesión inválida o expirada');
            }
            
            // Verificar que el token coincida con el session_token del usuario
            // Si el session_token es NULL, significa que la sesión fue cerrada remotamente
            if ($session['session_token'] !== $token) {
                Response::unauthorized('Tu sesión ha sido cerrada por un administrador');
            }
            
            // Decodificar permisos y extraer estructura
            $permisosData = json_decode($session['permisos'], true);
            
            // Verificar si es estructura nueva (con permisos y visibilidad) o vieja
            if (isset($permisosData['permisos']) && isset($permisosData['visibilidad_menu'])) {
                $permisos = $permisosData['permisos'];
                $visibilidad_menu = $permisosData['visibilidad_menu'];
            } else {
                $permisos = $permisosData;
                $visibilidad_menu = [];
                foreach ($permisosData as $modulo => $perms) {
                    $visibilidad_menu[$modulo] = true;
                }
            }
            
            // Retornar datos del usuario
            return [
                'usuario_id' => $session['usuario_id'],
                'nombre' => $session['nombre'],
                'email' => $session['email'],
                'rol_id' => $session['rol_id'],
                'rol' => $session['rol_nombre'],
                'permisos' => $permisos,
                'visibilidad_menu' => $visibilidad_menu
            ];
            
        } catch (Exception $e) {
            Response::unauthorized($e->getMessage());
        }
    }
    
    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function checkPermission($user, $modulo, $accion) {
        $permisos = $user['permisos'];
        
        if (!isset($permisos[$modulo])) {
            return false;
        }
        
        return in_array($accion, $permisos[$modulo]);
    }
    
    /**
     * Alias de checkPermission para mayor claridad semántica
     */
    public function hasPermission($user, $modulo, $accion) {
        return $this->checkPermission($user, $modulo, $accion);
    }
    
    /**
     * Requerir permiso específico
     */
    public function requirePermission($user, $modulo, $accion) {
        if (!$this->checkPermission($user, $modulo, $accion)) {
            Response::forbidden("No tienes permiso para: $modulo - $accion");
        }
    }
    
    /**
     * Verificar si el usuario es dueño
     */
    public function isDueno($user) {
        return $user['rol'] === 'dueño';
    }
    
    /**
     * Requerir rol de dueño
     */
    public function requireDueno($user) {
        if (!$this->isDueno($user)) {
            Response::forbidden('Solo el dueño puede realizar esta acción');
        }
    }
}
