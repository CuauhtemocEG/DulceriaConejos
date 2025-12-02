<?php
require_once __DIR__ . '/../config/config.php';

/**
 * API de gestión de categorías
 */

class CategoriasAPI {
    private $db;
    private $auth;
    private $user;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->auth = new AuthMiddleware();
        $this->user = $this->auth->authenticate();
    }
    
    public function listar() {
        try {
            $activo = $_GET['activo'] ?? null;
            
            $sql = "SELECT * FROM categorias WHERE 1=1";
            $params = [];
            
            if ($activo !== null) {
                $sql .= " AND activo = ?";
                $params[] = $activo;
            }
            
            $sql .= " ORDER BY nombre ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $categorias = $stmt->fetchAll();
            
            Response::success($categorias, 'Lista de categorías');
            
        } catch (Exception $e) {
            Response::error('Error al listar categorías: ' . $e->getMessage(), 500);
        }
    }
}

// Router
$method = $_SERVER['REQUEST_METHOD'];
$api = new CategoriasAPI();

if ($method === 'GET') {
    $api->listar();
} else {
    Response::notFound('Endpoint no encontrado');
}
