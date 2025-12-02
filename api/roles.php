<?php
require_once __DIR__ . '/../config/config.php';

/**
 * API de gestión de roles y permisos
 * Solo accesible para el dueño
 */

class RolesAPI {
    private $db;
    private $auth;
    private $user;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->auth = new AuthMiddleware();
        $this->user = $this->auth->authenticate();
    }
    
    /**
     * Listar todos los roles
     */
    public function listar() {
        $this->auth->requireDueno($this->user);
        
        try {
            $stmt = $this->db->query("
                SELECT r.*, 
                       (SELECT COUNT(*) FROM usuarios WHERE rol_id = r.id) as total_usuarios
                FROM roles r
                ORDER BY r.id ASC
            ");
            $roles = $stmt->fetchAll();
            
            // Decodificar permisos JSON y separar estructura
            foreach ($roles as &$rol) {
                $permisosData = json_decode($rol['permisos'], true);
                
                // Verificar si es estructura nueva (con permisos y visibilidad) o vieja (solo permisos)
                if (isset($permisosData['permisos']) && isset($permisosData['visibilidad_menu'])) {
                    // Estructura nueva
                    $rol['permisos'] = $permisosData['permisos'];
                    $rol['visibilidad_menu'] = $permisosData['visibilidad_menu'];
                } else {
                    // Estructura vieja - solo permisos, visibilidad por defecto true
                    $rol['permisos'] = $permisosData;
                    $rol['visibilidad_menu'] = [];
                    // Hacer que todos los módulos sean visibles por defecto
                    foreach ($permisosData as $modulo => $perms) {
                        $rol['visibilidad_menu'][$modulo] = true;
                    }
                }
            }
            
            Response::success($roles, 'Lista de roles');
            
        } catch (Exception $e) {
            Response::error('Error al listar roles: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener un rol por ID
     */
    public function obtener($id) {
        $this->auth->requireDueno($this->user);
        
        try {
            $stmt = $this->db->prepare("
                SELECT r.*,
                       (SELECT COUNT(*) FROM usuarios WHERE rol_id = r.id) as total_usuarios
                FROM roles r
                WHERE r.id = ?
            ");
            $stmt->execute([$id]);
            $rol = $stmt->fetch();
            
            if (!$rol) {
                Response::notFound('Rol no encontrado');
            }
            
            // Decodificar permisos JSON y separar estructura
            $permisosData = json_decode($rol['permisos'], true);
            
            if (isset($permisosData['permisos']) && isset($permisosData['visibilidad_menu'])) {
                // Estructura nueva
                $rol['permisos'] = $permisosData['permisos'];
                $rol['visibilidad_menu'] = $permisosData['visibilidad_menu'];
            } else {
                // Estructura vieja
                $rol['permisos'] = $permisosData;
                $rol['visibilidad_menu'] = [];
                foreach ($permisosData as $modulo => $perms) {
                    $rol['visibilidad_menu'][$modulo] = true;
                }
            }
            
            Response::success($rol, 'Datos del rol');
            
        } catch (Exception $e) {
            Response::error('Error al obtener rol: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Crear un nuevo rol
     */
    public function crear() {
        $this->auth->requireDueno($this->user);
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos
        $errores = [];
        if (empty($data['nombre'])) {
            $errores['nombre'] = 'El nombre es requerido';
        }
        if (empty($data['descripcion'])) {
            $errores['descripcion'] = 'La descripción es requerida';
        }
        if (!isset($data['permisos']) || !is_array($data['permisos'])) {
            $errores['permisos'] = 'Los permisos son requeridos';
        }
        
        if (!empty($errores)) {
            Response::validationError($errores);
        }
        
        try {
            // Verificar si el nombre ya existe
            $stmt = $this->db->prepare("SELECT id FROM roles WHERE nombre = ?");
            $stmt->execute([$data['nombre']]);
            if ($stmt->fetch()) {
                Response::error('El nombre del rol ya está registrado', 400);
            }
            
            // Crear estructura de permisos con visibilidad
            $permisosCompletos = [
                'permisos' => $data['permisos'],
                'visibilidad_menu' => $data['visibilidad_menu'] ?? []
            ];
            
            $permisosJson = json_encode($permisosCompletos, JSON_UNESCAPED_UNICODE);
            
            $stmt = $this->db->prepare("
                INSERT INTO roles (nombre, descripcion, permisos)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $data['nombre'],
                $data['descripcion'],
                $permisosJson
            ]);
            
            $rolId = $this->db->lastInsertId();
            
            // Obtener rol creado
            $this->obtener($rolId);
            
        } catch (Exception $e) {
            Response::error('Error al crear rol: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Actualizar un rol
     */
    public function actualizar($id) {
        $this->auth->requireDueno($this->user);
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            // Verificar que el rol existe
            $stmt = $this->db->prepare("SELECT id, nombre FROM roles WHERE id = ?");
            $stmt->execute([$id]);
            $rolActual = $stmt->fetch();
            
            if (!$rolActual) {
                Response::notFound('Rol no encontrado');
            }
            
            // No permitir editar el rol de dueño (protección)
            if ($rolActual['nombre'] === 'dueño' || $id == 1) {
                Response::error('No se puede modificar el rol de dueño', 403);
            }
            
            // Construir query dinámicamente
            $campos = [];
            $valores = [];
            
            if (isset($data['nombre'])) {
                // Verificar que el nombre no exista en otro rol
                $stmt = $this->db->prepare("SELECT id FROM roles WHERE nombre = ? AND id != ?");
                $stmt->execute([$data['nombre'], $id]);
                if ($stmt->fetch()) {
                    Response::error('El nombre del rol ya está en uso', 400);
                }
                $campos[] = "nombre = ?";
                $valores[] = $data['nombre'];
            }
            
            if (isset($data['descripcion'])) {
                $campos[] = "descripcion = ?";
                $valores[] = $data['descripcion'];
            }
            
            if (isset($data['permisos']) && is_array($data['permisos'])) {
                // Crear estructura de permisos con visibilidad
                $permisosCompletos = [
                    'permisos' => $data['permisos'],
                    'visibilidad_menu' => $data['visibilidad_menu'] ?? []
                ];
                $campos[] = "permisos = ?";
                $valores[] = json_encode($permisosCompletos, JSON_UNESCAPED_UNICODE);
            }
            
            if (empty($campos)) {
                Response::error('No hay datos para actualizar', 400);
            }
            
            $valores[] = $id;
            
            $sql = "UPDATE roles SET " . implode(", ", $campos) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($valores);
            
            // Obtener rol actualizado
            $this->obtener($id);
            
        } catch (Exception $e) {
            Response::error('Error al actualizar rol: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Eliminar un rol
     */
    public function eliminar($id) {
        $this->auth->requireDueno($this->user);
        
        try {
            // Verificar que el rol existe
            $stmt = $this->db->prepare("SELECT nombre FROM roles WHERE id = ?");
            $stmt->execute([$id]);
            $rol = $stmt->fetch();
            
            if (!$rol) {
                Response::notFound('Rol no encontrado');
            }
            
            // No permitir eliminar el rol de dueño
            if ($rol['nombre'] === 'dueño' || $id == 1) {
                Response::error('No se puede eliminar el rol de dueño', 403);
            }
            
            // Verificar que no hay usuarios con este rol
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE rol_id = ?");
            $stmt->execute([$id]);
            $resultado = $stmt->fetch();
            
            if ($resultado['total'] > 0) {
                Response::error('No se puede eliminar el rol porque tiene usuarios asignados', 400);
            }
            
            // Eliminar rol
            $stmt = $this->db->prepare("DELETE FROM roles WHERE id = ?");
            $stmt->execute([$id]);
            
            Response::success(null, 'Rol eliminado exitosamente');
            
        } catch (Exception $e) {
            Response::error('Error al eliminar rol: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener usuarios de un rol específico
     */
    public function obtenerUsuariosDelRol($id) {
        $this->auth->requireDueno($this->user);
        
        try {
            $stmt = $this->db->prepare("
                SELECT u.id, u.nombre, u.email, u.activo, u.ultimo_acceso
                FROM usuarios u
                WHERE u.rol_id = ?
                ORDER BY u.nombre ASC
            ");
            $stmt->execute([$id]);
            $usuarios = $stmt->fetchAll();
            
            Response::success($usuarios, 'Usuarios del rol');
            
        } catch (Exception $e) {
            Response::error('Error al obtener usuarios del rol: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener lista de módulos y permisos disponibles
     */
    public function obtenerModulosDisponibles() {
        $this->auth->requireDueno($this->user);
        
        $modulos = [
            'dashboard' => [
                'nombre' => 'Inicio',
                'descripcion' => 'Panel principal con estadísticas y resumen',
                'permisos' => ['ver'],
                'tiene_menu' => true
            ],
            'productos' => [
                'nombre' => 'Gestión de Productos',
                'descripcion' => 'Gestión del catálogo de productos',
                'permisos' => ['ver', 'crear', 'editar', 'eliminar'],
                'tiene_menu' => true
            ],
            'ventas' => [
                'nombre' => 'Gestión de Ordenes',
                'descripcion' => 'Gestión de ventas y tickets',
                'permisos' => ['ver', 'crear', 'cancelar'],
                'tiene_menu' => true
            ],
            'pos' => [
                'nombre' => 'Punto de Venta (POS)',
                'descripcion' => 'Operaciones de punto de venta',
                'permisos' => ['vender', 'cancelar', 'reimprimir'],
                'tiene_menu' => true
            ],
            'inventario' => [
                'nombre' => 'Gestión de Inventario',
                'descripcion' => 'Control de inventario y stock',
                'permisos' => ['ver', 'ajustar'],
                'tiene_menu' => true
            ],
            'reportes' => [
                'nombre' => 'Reportes de Sistema',
                'descripcion' => 'Generación de reportes',
                'permisos' => ['ver', 'exportar'],
                'tiene_menu' => true
            ],
            'temporadas' => [
                'nombre' => 'Lista de Temporadas',
                'descripcion' => 'Gestión de temporadas especiales',
                'permisos' => ['ver', 'crear', 'editar', 'eliminar', 'activar'],
                'tiene_menu' => true
            ],
            'usuarios' => [
                'nombre' => 'Gestión de Usuarios',
                'descripcion' => 'Administración de usuarios',
                'permisos' => ['ver', 'crear', 'editar', 'eliminar'],
                'tiene_menu' => true
            ]
        ];
        
        Response::success($modulos, 'Módulos disponibles');
    }
}

// Router
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$segments = explode('/', trim($path, '/'));

$api = new RolesAPI();

if ($method === 'GET' && $path === '/') {
    $api->listar();
} elseif ($method === 'GET' && $path === '/modulos') {
    $api->obtenerModulosDisponibles();
} elseif ($method === 'GET' && count($segments) === 2 && is_numeric($segments[0]) && $segments[1] === 'usuarios') {
    $api->obtenerUsuariosDelRol($segments[0]);
} elseif ($method === 'GET' && count($segments) === 1 && is_numeric($segments[0])) {
    $api->obtener($segments[0]);
} elseif ($method === 'POST' && $path === '/') {
    $api->crear();
} elseif ($method === 'PUT' && count($segments) === 1 && is_numeric($segments[0])) {
    $api->actualizar($segments[0]);
} elseif ($method === 'DELETE' && count($segments) === 1 && is_numeric($segments[0])) {
    $api->eliminar($segments[0]);
} else {
    Response::error('Endpoint no encontrado', 404);
}
