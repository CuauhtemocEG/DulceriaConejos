<?php
require_once __DIR__ . '/../config/config.php';

/**
 * API de Configuración del Sistema
 */

class ConfiguracionAPI {
    private $db;
    private $auth;
    private $user;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->auth = new AuthMiddleware();
        $this->user = $this->auth->authenticate();
    }
    
    /**
     * Obtener configuración de impresora térmica
     */
    public function obtenerConfiguracionImpresora() {
        try {
            $stmt = $this->db->query("SELECT * FROM configuracion_impresora WHERE id = 1");
            $config = $stmt->fetch();
            
            if (!$config) {
                // Crear configuración por defecto si no existe
                $stmt = $this->db->prepare("
                    INSERT INTO configuracion_impresora (nombre_impresora, habilitada, auto_imprimir, copias) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute(['POS-80', 1, 0, 1]);
                
                $stmt = $this->db->query("SELECT * FROM configuracion_impresora WHERE id = 1");
                $config = $stmt->fetch();
            }
            
            Response::success($config, 'Configuración de impresora');
            
        } catch (Exception $e) {
            Response::error('Error al obtener configuración: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Actualizar configuración de impresora
     */
    public function actualizarConfiguracionImpresora() {
        // Permitir a dueños y administradores actualizar configuración
        $rolNombre = strtolower($this->user['rol'] ?? '');
        $esAdmin = in_array($rolNombre, ['dueño', 'dueno', 'admin', 'administrador']);
        
        if (!$esAdmin && !$this->auth->hasPermission($this->user, 'configuracion', 'editar')) {
            Response::forbidden('No tiene permisos para actualizar la configuración');
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $stmt = $this->db->prepare("
                UPDATE configuracion_impresora 
                SET nombre_impresora = ?,
                    habilitada = ?,
                    auto_imprimir = ?,
                    copias = ?
                WHERE id = 1
            ");
            
            $stmt->execute([
                $data['nombre_impresora'] ?? 'POS-80',
                $data['habilitada'] ?? 1,
                $data['auto_imprimir'] ?? 0,
                $data['copias'] ?? 1
            ]);
            
            Response::success(null, 'Configuración actualizada correctamente');
            
        } catch (Exception $e) {
            Response::error('Error al actualizar configuración: ' . $e->getMessage(), 500);
        }
    }
}

// Routing
$api = new ConfiguracionAPI();
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';

// Eliminar slash inicial
$path = ltrim($path, '/');

if ($method === 'GET' && $path === 'impresora') {
    $api->obtenerConfiguracionImpresora();
} elseif ($method === 'PUT' && $path === 'impresora') {
    $api->actualizarConfiguracionImpresora();
} else {
    Response::notFound('Endpoint no encontrado');
}
?>
