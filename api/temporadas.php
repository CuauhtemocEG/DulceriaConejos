<?php
require_once __DIR__ . '/../config/config.php';

/**
 * API de gestión de temporadas
 */

class TemporadasAPI {
    private $db;
    private $auth;
    private $user;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->auth = new AuthMiddleware();
        $this->user = $this->auth->authenticate();
    }
    
    /**
     * Listar todas las temporadas
     */
    public function listar() {
        $this->auth->requirePermission($this->user, 'productos', 'ver');
        
        try {
            $stmt = $this->db->query("
                SELECT 
                    t.*,
                    COUNT(DISTINCT p.id) as num_productos,
                    SUM(CASE WHEN p.activo = 1 THEN 1 ELSE 0 END) as productos_activos
                FROM temporadas t
                LEFT JOIN productos p ON t.id = p.temporada_id
                GROUP BY t.id
                ORDER BY t.activa DESC, t.fecha_inicio DESC
            ");
            $temporadas = $stmt->fetchAll();
            
            Response::success($temporadas, 'Lista de temporadas');
            
        } catch (Exception $e) {
            Response::error('Error al listar temporadas: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener una temporada por ID
     */
    public function obtener($id) {
        $this->auth->requirePermission($this->user, 'productos', 'ver');
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    t.*,
                    COUNT(DISTINCT p.id) as num_productos
                FROM temporadas t
                LEFT JOIN productos p ON t.id = p.temporada_id
                WHERE t.id = ?
                GROUP BY t.id
            ");
            $stmt->execute([$id]);
            $temporada = $stmt->fetch();
            
            if (!$temporada) {
                Response::notFound('Temporada no encontrada');
            }
            
            Response::success($temporada);
            
        } catch (Exception $e) {
            Response::error('Error al obtener temporada: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Crear nueva temporada
     */
    public function crear() {
        $this->auth->requirePermission($this->user, 'temporadas', 'crear');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar
        $errores = [];
        if (empty($data['nombre'])) {
            $errores['nombre'] = 'El nombre es requerido';
        }
        if (empty($data['fecha_inicio'])) {
            $errores['fecha_inicio'] = 'La fecha de inicio es requerida';
        }
        if (empty($data['fecha_fin'])) {
            $errores['fecha_fin'] = 'La fecha de fin es requerida';
        }
        
        if (!empty($errores)) {
            Response::validationError($errores);
        }
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO temporadas (nombre, descripcion, fecha_inicio, fecha_fin, icono, activa)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['nombre'],
                $data['descripcion'] ?? null,
                $data['fecha_inicio'],
                $data['fecha_fin'],
                $data['icono'] ?? '🎉',
                0 // Por defecto inactiva
            ]);
            
            $id = $this->db->lastInsertId();
            
            // Obtener la temporada creada
            $stmt = $this->db->prepare("SELECT * FROM temporadas WHERE id = ?");
            $stmt->execute([$id]);
            $temporada = $stmt->fetch();
            
            Response::success($temporada, 'Temporada creada exitosamente', 201);
            
        } catch (Exception $e) {
            Response::error('Error al crear temporada: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Actualizar temporada
     */
    public function actualizar($id) {
        $this->auth->requirePermission($this->user, 'temporadas', 'editar');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $campos = [];
            $valores = [];
            
            if (isset($data['nombre'])) {
                $campos[] = 'nombre = ?';
                $valores[] = $data['nombre'];
            }
            if (isset($data['descripcion'])) {
                $campos[] = 'descripcion = ?';
                $valores[] = $data['descripcion'];
            }
            if (isset($data['fecha_inicio'])) {
                $campos[] = 'fecha_inicio = ?';
                $valores[] = $data['fecha_inicio'];
            }
            if (isset($data['fecha_fin'])) {
                $campos[] = 'fecha_fin = ?';
                $valores[] = $data['fecha_fin'];
            }
            if (isset($data['icono'])) {
                $campos[] = 'icono = ?';
                $valores[] = $data['icono'];
            }
            
            if (empty($campos)) {
                Response::validationError(['general' => 'No hay campos para actualizar']);
            }
            
            $valores[] = $id;
            
            $stmt = $this->db->prepare("
                UPDATE temporadas 
                SET " . implode(', ', $campos) . "
                WHERE id = ?
            ");
            $stmt->execute($valores);
            
            // Obtener temporada actualizada
            $stmt = $this->db->prepare("SELECT * FROM temporadas WHERE id = ?");
            $stmt->execute([$id]);
            $temporada = $stmt->fetch();
            
            Response::success($temporada, 'Temporada actualizada exitosamente');
            
        } catch (Exception $e) {
            Response::error('Error al actualizar temporada: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Eliminar temporada
     */
    public function eliminar($id) {
        $this->auth->requirePermission($this->user, 'temporadas', 'eliminar');
        
        try {
            // Verificar si tiene productos asignados
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM productos WHERE temporada_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetch()['total'];
            
            if ($count > 0) {
                Response::error('No se puede eliminar la temporada porque tiene productos asignados', 400);
            }
            
            $stmt = $this->db->prepare("DELETE FROM temporadas WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                Response::notFound('Temporada no encontrada');
            }
            
            Response::success(null, 'Temporada eliminada exitosamente');
            
        } catch (Exception $e) {
            Response::error('Error al eliminar temporada: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Activar temporada
     */
    public function activar($id) {
        $this->auth->requirePermission($this->user, 'temporadas', 'activar');
        
        try {
            $this->db->beginTransaction();
            
            // Desactivar todas las temporadas
            $this->db->exec("UPDATE temporadas SET activa = 0");
            
            // Activar la temporada seleccionada
            $stmt = $this->db->prepare("UPDATE temporadas SET activa = 1 WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                $this->db->rollBack();
                Response::notFound('Temporada no encontrada');
            }
            
            $this->db->commit();
            
            // Obtener temporada activada
            $stmt = $this->db->prepare("SELECT * FROM temporadas WHERE id = ?");
            $stmt->execute([$id]);
            $temporada = $stmt->fetch();
            
            Response::success($temporada, 'Temporada activada exitosamente');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error('Error al activar temporada: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Desactivar temporada
     */
    public function desactivar($id) {
        $this->auth->requirePermission($this->user, 'temporadas', 'activar');
        
        try {
            $stmt = $this->db->prepare("UPDATE temporadas SET activa = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                Response::notFound('Temporada no encontrada');
            }
            
            // Obtener temporada desactivada
            $stmt = $this->db->prepare("SELECT * FROM temporadas WHERE id = ?");
            $stmt->execute([$id]);
            $temporada = $stmt->fetch();
            
            Response::success($temporada, 'Temporada desactivada exitosamente');
            
        } catch (Exception $e) {
            Response::error('Error al desactivar temporada: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener productos de una temporada
     */
    public function obtenerProductos($id) {
        $this->auth->requirePermission($this->user, 'productos', 'ver');
        
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p.*,
                    c.nombre as categoria_nombre,
                    CASE 
                        WHEN p.es_temporada = 1 AND p.precio_temporada > p.precio_venta 
                        THEN p.precio_temporada 
                        ELSE p.precio_venta 
                    END as precio_final
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.temporada_id = ? AND p.es_temporada = 1
                ORDER BY p.nombre
            ");
            $stmt->execute([$id]);
            $productos = $stmt->fetchAll();
            
            Response::success($productos, 'Productos de la temporada');
            
        } catch (Exception $e) {
            Response::error('Error al obtener productos: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Agregar producto al listado de temporada
     */
    public function agregarProducto($idTemporada, $idProducto) {
        $this->auth->requirePermission($this->user, 'temporadas', 'editar');
        
        try {
            // Verificar que la temporada existe
            $stmt = $this->db->prepare("SELECT id FROM temporadas WHERE id = ?");
            $stmt->execute([$idTemporada]);
            if (!$stmt->fetch()) {
                Response::notFound('Temporada no encontrada');
            }
            
            // Verificar que el producto existe
            $stmt = $this->db->prepare("SELECT id FROM productos WHERE id = ?");
            $stmt->execute([$idProducto]);
            if (!$stmt->fetch()) {
                Response::notFound('Producto no encontrado');
            }
            
            // Agregar producto al listado (marcarlo como de temporada)
            $stmt = $this->db->prepare("
                UPDATE productos 
                SET es_temporada = 1, temporada_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$idTemporada, $idProducto]);
            
            Response::success(null, 'Producto agregado al listado de temporada');
            
        } catch (Exception $e) {
            Response::error('Error al agregar producto: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Quitar producto del listado de temporada
     */
    public function quitarProducto($idTemporada, $idProducto) {
        $this->auth->requirePermission($this->user, 'temporadas', 'editar');
        
        try {
            // Quitar producto del listado (desmarcarlo como de temporada)
            $stmt = $this->db->prepare("
                UPDATE productos 
                SET es_temporada = 0, temporada_id = NULL, precio_temporada = NULL
                WHERE id = ? AND temporada_id = ?
            ");
            $stmt->execute([$idProducto, $idTemporada]);
            
            if ($stmt->rowCount() === 0) {
                Response::error('El producto no está en este listado de temporada', 400);
            }
            
            Response::success(null, 'Producto quitado del listado de temporada');
            
        } catch (Exception $e) {
            Response::error('Error al quitar producto: ' . $e->getMessage(), 500);
        }
    }
}

// Router
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$segments = explode('/', trim($path, '/'));

try {
    $api = new TemporadasAPI();
    
    // GET /temporadas
    if ($method === 'GET' && $path === '/') {
        $api->listar();
    }
    // GET /temporadas/{id}
    elseif ($method === 'GET' && count($segments) === 1 && is_numeric($segments[0])) {
        $api->obtener($segments[0]);
    }
    // GET /temporadas/{id}/productos
    elseif ($method === 'GET' && count($segments) === 2 && is_numeric($segments[0]) && $segments[1] === 'productos') {
        $api->obtenerProductos($segments[0]);
    }
    // POST /temporadas/{id}/productos/{idProducto}
    elseif ($method === 'POST' && count($segments) === 3 && is_numeric($segments[0]) && $segments[1] === 'productos' && is_numeric($segments[2])) {
        $api->agregarProducto($segments[0], $segments[2]);
    }
    // DELETE /temporadas/{id}/productos/{idProducto}
    elseif ($method === 'DELETE' && count($segments) === 3 && is_numeric($segments[0]) && $segments[1] === 'productos' && is_numeric($segments[2])) {
        $api->quitarProducto($segments[0], $segments[2]);
    }
    // POST /temporadas
    elseif ($method === 'POST' && $path === '/') {
        $api->crear();
    }
    // PUT /temporadas/{id}
    elseif ($method === 'PUT' && count($segments) === 1 && is_numeric($segments[0])) {
        $api->actualizar($segments[0]);
    }
    // PUT /temporadas/{id}/activar
    elseif ($method === 'PUT' && count($segments) === 2 && is_numeric($segments[0]) && $segments[1] === 'activar') {
        $api->activar($segments[0]);
    }
    // PUT /temporadas/{id}/desactivar
    elseif ($method === 'PUT' && count($segments) === 2 && is_numeric($segments[0]) && $segments[1] === 'desactivar') {
        $api->desactivar($segments[0]);
    }
    // DELETE /temporadas/{id}
    elseif ($method === 'DELETE' && count($segments) === 1 && is_numeric($segments[0])) {
        $api->eliminar($segments[0]);
    }
    else {
        Response::notFound('Endpoint no encontrado');
    }
    
} catch (Exception $e) {
    Response::error('Error del servidor: ' . $e->getMessage(), 500);
}
