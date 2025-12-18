<?php
require_once __DIR__ . '/../config/config.php';

/**
 * API de gestión de productos
 */

class ProductosAPI {
    private $db;
    private $auth;
    private $user;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->auth = new AuthMiddleware();
        $this->user = $this->auth->authenticate();
    }
    
    /**
     * Listar productos
     */
    public function listar() {
        // Permitir acceso si tiene permiso de ver productos O permiso de vender en POS
        $tienePermisoProductos = $this->auth->hasPermission($this->user, 'productos', 'ver');
        $tienePermisoPOS = $this->auth->hasPermission($this->user, 'pos', 'vender');
        
        if (!$tienePermisoProductos && !$tienePermisoPOS) {
            Response::error('No tiene permisos para ver productos', 403);
        }
        
        // Parámetros de filtrado
        $categoria = $_GET['categoria'] ?? null;
        $tipo = $_GET['tipo'] ?? null;
        $temporada = $_GET['temporada'] ?? null;
        $activo = isset($_GET['activo']) ? $_GET['activo'] : null; // Por defecto muestra todos
        $stockBajo = $_GET['stock_bajo'] ?? null;
        
        try {
            $sql = "
                SELECT p.*, c.nombre as categoria_nombre, 
                       t.nombre as temporada_nombre, t.activa as temporada_activa,
                       CASE 
                           WHEN p.es_temporada = 1 AND p.precio_temporada > p.precio_venta 
                           THEN p.precio_temporada 
                           ELSE p.precio_venta 
                       END as precio_final
                FROM productos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN temporadas t ON p.temporada_id = t.id
                WHERE 1=1
            ";
            
            $params = [];
            
            if ($activo !== null && $activo !== '') {
                $sql .= " AND p.activo = ?";
                $params[] = $activo;
                
                // Si se pide solo activos, excluir productos de temporadas inactivas
                // Mostrar: productos normales (es_temporada=0) O productos de temporadas activas (temporada_activa=1)
                $sql .= " AND (p.es_temporada = 0 OR (p.es_temporada = 1 AND t.activa = 1))";
            }
            
            if ($categoria) {
                $sql .= " AND p.categoria_id = ?";
                $params[] = $categoria;
            }
            
            if ($tipo) {
                $sql .= " AND p.tipo_producto = ?";
                $params[] = $tipo;
            }
            
            if ($temporada) {
                $sql .= " AND p.temporada_id = ?";
                $params[] = $temporada;
            }
            
            if ($stockBajo) {
                $sql .= " AND p.stock_actual <= p.stock_minimo";
            }
            
            $sql .= " ORDER BY p.nombre ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $productos = $stmt->fetchAll();
            
            // Si es producto a granel, agregar precios por peso
            foreach ($productos as &$producto) {
                if ($producto['tipo_producto'] === 'granel') {
                    $stmt = $this->db->prepare("
                        SELECT * FROM precios_granel 
                        WHERE producto_id = ? 
                        ORDER BY peso_gramos ASC
                    ");
                    $stmt->execute([$producto['id']]);
                    $producto['precios_granel'] = $stmt->fetchAll();
                }
            }
            
            Response::success($productos, 'Lista de productos');
            
        } catch (Exception $e) {
            Response::error('Error al listar productos: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener un producto
     */
    public function obtener($id) {
        // Permitir acceso si tiene permiso de ver productos O permiso de vender en POS
        $tienePermisoProductos = $this->auth->hasPermission($this->user, 'productos', 'ver');
        $tienePermisoPOS = $this->auth->hasPermission($this->user, 'pos', 'vender');
        
        if (!$tienePermisoProductos && !$tienePermisoPOS) {
            Response::error('No tiene permisos para ver productos', 403);
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, c.nombre as categoria_nombre, 
                       t.nombre as temporada_nombre, t.activa as temporada_activa,
                       CASE 
                           WHEN p.es_temporada = 1 AND p.precio_temporada > p.precio_venta 
                           THEN p.precio_temporada 
                           ELSE p.precio_venta 
                       END as precio_final
                FROM productos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN temporadas t ON p.temporada_id = t.id
                WHERE p.id = ?
            ");
            $stmt->execute([$id]);
            $producto = $stmt->fetch();
            
            if (!$producto) {
                Response::notFound('Producto no encontrado');
            }
            
            // Si es producto a granel, agregar precios por peso
            if ($producto['tipo_producto'] === 'granel') {
                $stmt = $this->db->prepare("
                    SELECT * FROM precios_granel 
                    WHERE producto_id = ? 
                    ORDER BY peso_gramos ASC
                ");
                $stmt->execute([$id]);
                $producto['precios_granel'] = $stmt->fetchAll();
            }
            
            Response::success($producto, 'Datos del producto');
            
        } catch (Exception $e) {
            Response::error('Error al obtener producto: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Crear producto
     */
    public function crear() {
        $this->auth->requirePermission($this->user, 'productos', 'crear');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar
        $errores = [];
        if (empty($data['nombre'])) $errores['nombre'] = 'El nombre es requerido';
        if (empty($data['categoria_id'])) $errores['categoria_id'] = 'La categoría es requerida';
        if (empty($data['tipo_producto'])) $errores['tipo_producto'] = 'El tipo es requerido';
        if (!isset($data['precio_compra']) || $data['precio_compra'] < 0) {
            $errores['precio_compra'] = 'El precio de compra es requerido';
        }
        
        if (!empty($errores)) {
            Response::validationError($errores);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Calcular precio de venta y margen según tipo de producto
            $margenGanancia = $data['margen_ganancia'] ?? 30.00;
            
            if ($data['tipo_producto'] === 'pieza') {
                // Pieza: margen de 100% (doble del precio de compra)
                $precioVenta = round($data['precio_compra'] * 2);
                $margenGanancia = 100.00;
            } elseif ($data['tipo_producto'] === 'granel') {
                // Granel: usar el margen del 1kg como base (por defecto 40%)
                $margenGranel1kg = 40.00;
                if (isset($data['margenes_granel'][1000])) {
                    $margenGranel1kg = floatval($data['margenes_granel'][1000]);
                }
                $precioVenta = round($data['precio_compra'] * (1 + ($margenGranel1kg / 100)));
                $margenGanancia = $margenGranel1kg;
            } else {
                // Anaquel: margen por defecto (30%)
                $precioVenta = round($data['precio_compra'] * (1 + ($margenGanancia / 100)));
            }
            
            // Insertar producto
            $stmt = $this->db->prepare("
                INSERT INTO productos (
                    nombre, descripcion, imagen_url, upc, categoria_id, tipo_producto,
                    precio_compra, precio_venta, margen_ganancia,
                    stock_actual, stock_minimo, unidad_medida,
                    es_temporada, temporada_id, precio_temporada, activo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['nombre'],
                $data['descripcion'] ?? null,
                $data['imagen_url'] ?? null,
                $data['upc'] ?? null,
                $data['categoria_id'],
                $data['tipo_producto'],
                $data['precio_compra'],
                $precioVenta,
                $margenGanancia,
                $data['stock_actual'] ?? 0,
                $data['stock_minimo'] ?? 2,
                $data['unidad_medida'] ?? 'piezas',
                $data['es_temporada'] ?? 0,
                $data['temporada_id'] ?? null,
                $data['precio_temporada'] ?? null,
                $data['activo'] ?? 1
            ]);
            
            $productoId = $this->db->lastInsertId();
            
            // Si es producto a granel, crear precios por peso
            if ($data['tipo_producto'] === 'granel') {
                $margenesGranel = $data['margenes_granel'] ?? null;
                $this->crearPreciosGranel($productoId, $data['precio_compra'], $margenesGranel);
            }
            
            $this->db->commit();
            
            // Obtener producto creado
            $this->obtener($productoId);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error('Error al crear producto: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Actualizar producto
     */
    public function actualizar($id) {
        // Verificar permisos
        if ($this->auth->isDueno($this->user)) {
            $this->auth->requirePermission($this->user, 'productos', 'editar');
        } else {
            // Encargado solo puede actualizar precios
            $this->auth->requirePermission($this->user, 'productos', 'actualizar_precio');
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $this->db->beginTransaction();
            
            // Verificar que existe
            $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ?");
            $stmt->execute([$id]);
            $producto = $stmt->fetch();
            
            if (!$producto) {
                Response::notFound('Producto no encontrado');
            }
            
            // Si no es dueño, solo permitir actualizar precios
            if (!$this->auth->isDueno($this->user)) {
                if (isset($data['precio_venta'])) {
                    $stmt = $this->db->prepare("UPDATE productos SET precio_venta = ? WHERE id = ?");
                    $stmt->execute([$data['precio_venta'], $id]);
                    
                    // Recalcular precios de granel si aplica
                    if ($producto['tipo_producto'] === 'granel') {
                        $this->actualizarPreciosGranel($id, $data['precio_venta']);
                    }
                }
                
                $this->db->commit();
                $this->obtener($id);
                return;
            }
            
            // Construir query dinámicamente para dueño
            $campos = [];
            $valores = [];
            
            if (isset($data['nombre'])) {
                $campos[] = "nombre = ?";
                $valores[] = $data['nombre'];
            }
            if (isset($data['descripcion'])) {
                $campos[] = "descripcion = ?";
                $valores[] = $data['descripcion'];
            }
            if (isset($data['imagen_url'])) {
                $campos[] = "imagen_url = ?";
                $valores[] = $data['imagen_url'];
            }
            if (isset($data['upc'])) {
                $campos[] = "upc = ?";
                $valores[] = $data['upc'];
            }
            if (isset($data['categoria_id'])) {
                $campos[] = "categoria_id = ?";
                $valores[] = $data['categoria_id'];
            }
            if (isset($data['tipo_producto'])) {
                $campos[] = "tipo_producto = ?";
                $valores[] = $data['tipo_producto'];
            }
            if (isset($data['precio_compra'])) {
                $campos[] = "precio_compra = ?";
                $valores[] = $data['precio_compra'];
                
                // Si es granel y cambió el precio de compra, actualizar/crear precios
                if ($producto['tipo_producto'] === 'granel') {
                    $margenesGranel = $data['margenes_granel'] ?? null;
                    $this->sincronizarPreciosGranel($id, $data['precio_compra'], $margenesGranel);
                }
            }
            if (isset($data['precio_venta'])) {
                $campos[] = "precio_venta = ?";
                $valores[] = $data['precio_venta'];
            }
            
            // Si solo se actualizaron márgenes de granel sin cambiar precio_compra
            if ($producto['tipo_producto'] === 'granel' && 
                isset($data['margenes_granel']) && 
                !isset($data['precio_compra'])) {
                $this->sincronizarPreciosGranel($id, $producto['precio_compra'], $data['margenes_granel']);
            }
            if (isset($data['margen_ganancia'])) {
                $campos[] = "margen_ganancia = ?";
                $valores[] = $data['margen_ganancia'];
            }
            if (isset($data['stock_actual'])) {
                $campos[] = "stock_actual = ?";
                $valores[] = $data['stock_actual'];
            }
            if (isset($data['stock_minimo'])) {
                $campos[] = "stock_minimo = ?";
                $valores[] = $data['stock_minimo'];
            }
            if (isset($data['unidad_medida'])) {
                $campos[] = "unidad_medida = ?";
                $valores[] = $data['unidad_medida'];
            }
            if (isset($data['es_temporada'])) {
                $campos[] = "es_temporada = ?";
                $valores[] = $data['es_temporada'];
            }
            if (isset($data['temporada_id'])) {
                $campos[] = "temporada_id = ?";
                $valores[] = $data['temporada_id'];
            }
            if (isset($data['precio_temporada'])) {
                $campos[] = "precio_temporada = ?";
                $valores[] = $data['precio_temporada'];
            }
            if (isset($data['activo'])) {
                $campos[] = "activo = ?";
                $valores[] = $data['activo'];
            }
            
            if (empty($campos)) {
                Response::error('No hay datos para actualizar', 400);
            }
            
            $valores[] = $id;
            
            $sql = "UPDATE productos SET " . implode(", ", $campos) . " WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($valores);
            
            $this->db->commit();
            
            $this->obtener($id);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error('Error al actualizar producto: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Eliminar producto
     */
    public function eliminar($id) {
        $this->auth->requirePermission($this->user, 'productos', 'eliminar');
        
        try {
            $stmt = $this->db->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                Response::notFound('Producto no encontrado');
            }
            
            Response::success(null, 'Producto eliminado exitosamente');
            
        } catch (Exception $e) {
            Response::error('Error al eliminar producto: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Crear precios por peso para productos a granel
     */
    /**
     * Crear precios por peso para productos a granel
     */
    private function crearPreciosGranel($productoId, $precioCompra, $margenesPersonalizados = null) {
        // Primero, limpiar cualquier registro huérfano previo para evitar duplicados
        $deleteStmt = $this->db->prepare("DELETE FROM precios_granel WHERE producto_id = ?");
        $deleteStmt->execute([$productoId]);
        
        // Márgenes por defecto
        $pesos = [
            ['gramos' => 100, 'descripcion' => '100 gramos', 'margen' => 50.00],
            ['gramos' => 250, 'descripcion' => '1/4 kg', 'margen' => 15.00],
            ['gramos' => 500, 'descripcion' => '1/2 kg', 'margen' => 10.00],
            ['gramos' => 1000, 'descripcion' => '1 kg', 'margen' => 40.00]
        ];
        
        // Si se proporcionaron márgenes personalizados, usarlos
        if ($margenesPersonalizados && is_array($margenesPersonalizados)) {
            foreach ($pesos as &$peso) {
                if (isset($margenesPersonalizados[$peso['gramos']])) {
                    $peso['margen'] = floatval($margenesPersonalizados[$peso['gramos']]);
                }
            }
        }
        
        // Usar INSERT ... ON DUPLICATE KEY UPDATE para evitar errores
        $stmt = $this->db->prepare("
            INSERT INTO precios_granel (producto_id, peso_gramos, descripcion, margen_adicional, precio_calculado)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                descripcion = VALUES(descripcion),
                margen_adicional = VALUES(margen_adicional),
                precio_calculado = VALUES(precio_calculado)
        ");
        
        foreach ($pesos as $peso) {
            // Calcular precio según la lógica del fleje:
            // 1kg = precio_compra con margen aplicado
            // Otros pesos = precio proporcional de 1kg + su margen adicional
            
            if ($peso['gramos'] == 1000) {
                // 1kg: aplicar margen directamente al precio de compra
                $precioCalculado = round($precioCompra * (1 + ($peso['margen'] / 100)));
            } else {
                // Otros pesos: primero calcular precio de 1kg, luego proporcional + margen adicional
                // Obtener margen de 1kg (buscar en el array de pesos)
                $margen1kg = 40.00; // Default
                foreach ($pesos as $p) {
                    if ($p['gramos'] == 1000) {
                        $margen1kg = $p['margen'];
                        break;
                    }
                }
                
                // Precio de 1kg con su margen
                $precioVenta1kg = $precioCompra * (1 + ($margen1kg / 100));
                
                // Precio proporcional al peso + margen adicional del peso
                $precioBase = ($precioVenta1kg / 1000) * $peso['gramos'];
                $precioCalculado = round($precioBase * (1 + ($peso['margen'] / 100)));
            }
            
            // Ya está redondeado arriba, no necesita round(x, 2)
            
            try {
                $stmt->execute([
                    $productoId,
                    $peso['gramos'],
                    $peso['descripcion'],
                    $peso['margen'],
                    $precioCalculado
                ]);
            } catch (Exception $e) {
                // Si falla un peso, continuar con los demás
                error_log("Error al crear precio granel {$peso['gramos']}g: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Actualizar precios por peso
     */
    private function actualizarPreciosGranel($productoId, $nuevoPrecioBase) {
        $stmt = $this->db->prepare("
            SELECT peso_gramos, margen_adicional 
            FROM precios_granel 
            WHERE producto_id = ?
        ");
        $stmt->execute([$productoId]);
        $precios = $stmt->fetchAll();
        
        $updateStmt = $this->db->prepare("
            UPDATE precios_granel 
            SET precio_calculado = ? 
            WHERE producto_id = ? AND peso_gramos = ?
        ");
        
        foreach ($precios as $precio) {
            $precioCalculado = round(($nuevoPrecioBase / 1000) * $precio['peso_gramos'] * 
                             (1 + ($precio['margen_adicional'] / 100)));
            
            $updateStmt->execute([$precioCalculado, $productoId, $precio['peso_gramos']]);
        }
    }
    
    /**
     * Actualizar precios por peso con márgenes personalizados
     */
    private function actualizarPreciosGranelConMargenes($productoId, $precioCompra, $margenesPersonalizados = null) {
        $stmt = $this->db->prepare("
            SELECT peso_gramos, margen_adicional 
            FROM precios_granel 
            WHERE producto_id = ?
        ");
        $stmt->execute([$productoId]);
        $precios = $stmt->fetchAll();
        
        $updateStmt = $this->db->prepare("
            UPDATE precios_granel 
            SET margen_adicional = ?, precio_calculado = ? 
            WHERE producto_id = ? AND peso_gramos = ?
        ");
        
        foreach ($precios as $precio) {
            $margen = $precio['margen_adicional'];
            
            // Si hay márgenes personalizados, usar ese valor
            if ($margenesPersonalizados && isset($margenesPersonalizados[$precio['peso_gramos']])) {
                $margen = floatval($margenesPersonalizados[$precio['peso_gramos']]);
            }
            
            // Usar la misma lógica que crearPreciosGranel
            if ($precio['peso_gramos'] == 1000) {
                // 1kg: aplicar margen directamente al precio de compra
                $precioCalculado = round($precioCompra * (1 + ($margen / 100)));
            } else {
                // Otros pesos: calcular desde precio de 1kg + margen adicional
                // Primero obtener el margen de 1kg
                $margen1kg = 40.00; // Default
                $stmt1kg = $this->db->prepare("
                    SELECT margen_adicional 
                    FROM precios_granel 
                    WHERE producto_id = ? AND peso_gramos = 1000
                ");
                $stmt1kg->execute([$productoId]);
                $precio1kg = $stmt1kg->fetch();
                if ($precio1kg) {
                    $margen1kg = floatval($precio1kg['margen_adicional']);
                }
                
                // Calcular precio de 1kg con su margen
                $precioVenta1kg = $precioCompra * (1 + ($margen1kg / 100));
                
                // Precio proporcional + margen adicional
                $precioBase = ($precioVenta1kg / 1000) * $precio['peso_gramos'];
                $precioCalculado = round($precioBase * (1 + ($margen / 100)));
            }
            
            // Ya está redondeado arriba
            
            $updateStmt->execute([$margen, $precioCalculado, $productoId, $precio['peso_gramos']]);
        }
    }
    
    /**
     * Sincronizar precios de granel (crear o actualizar)
     * Evita errores de duplicate key al editar productos
     */
    private function sincronizarPreciosGranel($productoId, $precioCompra, $margenesPersonalizados = null) {
        // Verificar si ya existen precios de granel para este producto
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM precios_granel 
            WHERE producto_id = ?
        ");
        $stmt->execute([$productoId]);
        $result = $stmt->fetch();
        
        if ($result['total'] > 0) {
            // Ya existen, actualizar
            $this->actualizarPreciosGranelConMargenes($productoId, $precioCompra, $margenesPersonalizados);
        } else {
            // No existen, crear
            $this->crearPreciosGranel($productoId, $precioCompra, $margenesPersonalizados);
        }
    }
    
    /**
     * Obtener precio de granel para un peso específico
     */
    public function obtenerPrecioGranel($productoId) {
        try {
            $peso = $_GET['peso'] ?? null;
            
            if (!$peso) {
                Response::error('Peso requerido', 400);
                return;
            }
            
            // Buscar precio exacto para ese peso
            $stmt = $this->db->prepare("
                SELECT precio_calculado as precio, peso_gramos, descripcion, margen_adicional
                FROM precios_granel 
                WHERE producto_id = ? AND peso_gramos = ?
            ");
            $stmt->execute([$productoId, $peso]);
            $precioGranel = $stmt->fetch();
            
            if (!$precioGranel) {
                // Si no existe en precios_granel, calcular sobre la marcha
                // Obtener datos del producto
                $stmtProd = $this->db->prepare("
                    SELECT precio_compra, precio_venta, margen_ganancia 
                    FROM productos 
                    WHERE id = ?
                ");
                $stmtProd->execute([$productoId]);
                $producto = $stmtProd->fetch();
                
                if (!$producto) {
                    Response::error('Producto no encontrado', 404);
                    return;
                }
                
                // Márgenes por defecto
                $margenesDefecto = [
                    100 => 50.00,
                    250 => 15.00,
                    500 => 10.00,
                    1000 => floatval($producto['margen_ganancia'])
                ];
                
                $margen = $margenesDefecto[$peso] ?? 40.00;
                
                // Calcular precio según lógica correcta
                if ($peso == 1000) {
                    // 1kg: usar precio_venta directamente
                    $precioCalculado = round(floatval($producto['precio_venta']));
                } else {
                    // Otros pesos: calcular desde precio de 1kg + margen adicional
                    $precioVenta1kg = floatval($producto['precio_venta']);
                    $precioBase = ($precioVenta1kg / 1000) * $peso;
                    $precioCalculado = round($precioBase * (1 + ($margen / 100)));
                }
                
                // Ya está redondeado arriba
                
                $precioGranel = [
                    'precio' => $precioCalculado,
                    'peso_gramos' => $peso,
                    'descripcion' => $peso . 'g',
                    'margen_adicional' => $margen
                ];
            }
            
            Response::success($precioGranel, 'Precio obtenido');
            
        } catch (Exception $e) {
            Response::error('Error al obtener precio: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Registrar movimiento de inventario
     */
    public function movimientoInventario() {
        if (!$this->auth->hasPermission($this->user, 'productos', 'editar')) {
            Response::error('No tiene permisos para ajustar inventario', 403);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validaciones
        if (empty($data['id_producto']) || empty($data['tipo_movimiento']) || 
            !isset($data['cantidad']) || empty($data['motivo'])) {
            Response::error('Datos incompletos', 400);
        }
        
        $idProducto = $data['id_producto'];
        $tipoMovimiento = $data['tipo_movimiento']; // entrada, salida, ajuste
        $cantidad = floatval($data['cantidad']);
        $motivo = $data['motivo'];
        
        if ($cantidad <= 0 && $tipoMovimiento !== 'ajuste') {
            Response::error('La cantidad debe ser mayor a 0', 400);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Obtener producto actual
            $stmt = $this->db->prepare("SELECT stock_actual, nombre, unidad_medida FROM productos WHERE id = ?");
            $stmt->execute([$idProducto]);
            $producto = $stmt->fetch();
            
            if (!$producto) {
                Response::error('Producto no encontrado', 404);
                return;
            }
            
            $stockAnterior = floatval($producto['stock_actual']);
            $stockNuevo = $stockAnterior;
            
            // Calcular nuevo stock según tipo de movimiento
            switch ($tipoMovimiento) {
                case 'entrada':
                    $stockNuevo = $stockAnterior + $cantidad;
                    break;
                case 'salida':
                    $stockNuevo = max(0, $stockAnterior - $cantidad); // No permitir stock negativo
                    break;
                case 'ajuste':
                    $stockNuevo = $cantidad; // Establecer cantidad exacta
                    break;
                default:
                    Response::error('Tipo de movimiento inválido', 400);
                    return;
            }
            
            // Actualizar stock del producto
            $stmt = $this->db->prepare("UPDATE productos SET stock_actual = ? WHERE id = ?");
            $stmt->execute([$stockNuevo, $idProducto]);
            
            // Registrar movimiento en historial
            $stmt = $this->db->prepare("
                INSERT INTO movimientos_inventario 
                (producto_id, tipo_movimiento, cantidad, stock_anterior, stock_nuevo, 
                 justificacion, usuario_id, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $idProducto,
                $tipoMovimiento,
                $cantidad,
                $stockAnterior,
                $stockNuevo,
                $motivo,
                $this->user['usuario_id'] // Cambiar de id_usuario a usuario_id
            ]);
            
            $this->db->commit();
            
            Response::success([
                'producto' => $producto['nombre'],
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'unidad_medida' => $producto['unidad_medida']
            ], 'Movimiento de inventario registrado exitosamente');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error('Error al registrar movimiento: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener historial de movimientos de inventario
     */
    public function historialInventario($productoId) {
        if (!$this->auth->hasPermission($this->user, 'productos', 'ver')) {
            Response::error('No tiene permisos para ver historial', 403);
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT mi.*, u.nombre as usuario_nombre, p.nombre as producto_nombre
                FROM movimientos_inventario mi
                INNER JOIN usuarios u ON mi.usuario_id = u.id
                INNER JOIN productos p ON mi.producto_id = p.id
                WHERE mi.producto_id = ?
                ORDER BY mi.created_at DESC
                LIMIT 50
            ");
            $stmt->execute([$productoId]);
            $movimientos = $stmt->fetchAll();
            
            Response::success($movimientos, 'Historial obtenido');
            
        } catch (Exception $e) {
            Response::error('Error al obtener historial: ' . $e->getMessage(), 500);
        }
    }
}

// Router
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$segments = explode('/', trim($path, '/'));

$api = new ProductosAPI();

if ($method === 'GET' && $path === '/') {
    $api->listar();
} elseif ($method === 'POST' && $path === '/movimiento-inventario') {
    // POST /api/productos.php/movimiento-inventario
    $api->movimientoInventario();
} elseif ($method === 'GET' && count($segments) === 2 && is_numeric($segments[0]) && $segments[1] === 'historial') {
    // GET /api/productos.php/5/historial
    $api->historialInventario($segments[0]);
} elseif ($method === 'GET' && count($segments) === 2 && is_numeric($segments[0]) && $segments[1] === 'precio-granel') {
    // GET /api/productos.php/5/precio-granel?peso=500
    $api->obtenerPrecioGranel($segments[0]);
} elseif ($method === 'GET' && count($segments) === 1 && is_numeric($segments[0])) {
    $api->obtener($segments[0]);
} elseif ($method === 'POST' && $path === '/') {
    $api->crear();
} elseif ($method === 'PUT' && count($segments) === 1 && is_numeric($segments[0])) {
    $api->actualizar($segments[0]);
} elseif ($method === 'DELETE' && count($segments) === 1 && is_numeric($segments[0])) {
    $api->eliminar($segments[0]);
} else {
    Response::notFound('Endpoint no encontrado');
}
