<?php
require_once __DIR__ . '/../config/config.php';

/**
 * API del Punto de Venta (POS)
 */

class POSAPI {
    private $db;
    private $auth;
    private $user;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->auth = new AuthMiddleware();
        $this->user = $this->auth->authenticate();
    }
    
    /**
     * Crear una nueva venta
     */
    public function crearVenta() {
        $this->auth->requirePermission($this->user, 'pos', 'vender');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar
        $errores = [];
        if (empty($data['productos']) || !is_array($data['productos'])) {
            $errores['productos'] = 'Debe incluir al menos un producto';
        }
        if (empty($data['metodo_pago_id'])) {
            $errores['metodo_pago_id'] = 'El método de pago es requerido';
        }
        
        if (!empty($errores)) {
            Response::validationError($errores);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Generar folio
            $stmt = $this->db->prepare("CALL generar_folio_venta(@folio)");
            $stmt->execute();
            $stmt = $this->db->query("SELECT @folio as folio");
            $folio = $stmt->fetch()['folio'];
            
            // Calcular totales
            $subtotal = 0;
            $detalles = [];
            
            foreach ($data['productos'] as $item) {
                // Obtener producto
                $stmt = $this->db->prepare("
                    SELECT p.*, 
                           CASE 
                               WHEN p.es_temporada = 1 AND p.precio_temporada > p.precio_venta 
                               THEN p.precio_temporada 
                               ELSE p.precio_venta 
                           END as precio_final
                    FROM productos p
                    WHERE p.id = ? AND p.activo = 1
                ");
                $stmt->execute([$item['producto_id']]);
                $producto = $stmt->fetch();
                
                if (!$producto) {
                    throw new Exception("Producto no encontrado: " . $item['producto_id']);
                }
                
                $precioUnitario = 0;
                $tipoVenta = $producto['tipo_producto'];
                $pesoGramos = null;
                
                // Calcular cantidad requerida de stock según tipo de producto
                $cantidadRequeridaStock = $item['cantidad'];
                if ($producto['tipo_producto'] === 'granel' && isset($item['peso_gramos'])) {
                    // Para granel: convertir gramos a kg
                    $pesoGramos = $item['peso_gramos'];
                    $cantidadRequeridaStock = ($pesoGramos / 1000) * $item['cantidad'];
                }
                
                // Verificar stock
                if ($producto['stock_actual'] < $cantidadRequeridaStock) {
                    $disponible = $producto['stock_actual'];
                    if ($producto['tipo_producto'] === 'granel' && $pesoGramos) {
                        $maxBolsas = floor($disponible / ($pesoGramos / 1000));
                        throw new Exception("Stock insuficiente para: " . $producto['nombre'] . ". Máximo: " . $maxBolsas . " bolsas de " . $pesoGramos . "g");
                    } else {
                        throw new Exception("Stock insuficiente para: " . $producto['nombre'] . ". Disponible: " . $disponible);
                    }
                }
                
                // Calcular precio según tipo de producto
                if ($producto['tipo_producto'] === 'granel') {
                    // Buscar precio según peso
                    $pesoGramos = $item['peso_gramos'] ?? null;
                    
                    if (!$pesoGramos) {
                        throw new Exception("Debe especificar peso_gramos para producto a granel");
                    }
                    
                    $stmt = $this->db->prepare("
                        SELECT precio_calculado 
                        FROM precios_granel 
                        WHERE producto_id = ? AND peso_gramos = ?
                    ");
                    $stmt->execute([$producto['id'], $pesoGramos]);
                    $precioGranel = $stmt->fetch();
                    
                    if ($precioGranel) {
                        $precioUnitario = $precioGranel['precio_calculado'];
                    } else {
                        // Calcular precio proporcional si no existe configuración
                        $precioUnitario = ($producto['precio_final'] / 1000) * $pesoGramos;
                    }
                    
                } elseif ($producto['tipo_producto'] === 'pieza') {
                    // Productos por pieza se venden al doble del precio de compra
                    $precioUnitario = $producto['precio_final'];
                    
                } else {
                    // Productos de anaquel
                    $precioUnitario = $producto['precio_final'];
                }
                
                $itemSubtotal = $precioUnitario * $item['cantidad'];
                $subtotal += $itemSubtotal;
                
                $detalles[] = [
                    'producto_id' => $producto['id'],
                    'nombre_producto' => $producto['nombre'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $itemSubtotal,
                    'tipo_venta' => $tipoVenta,
                    'peso_gramos' => $pesoGramos
                ];
            }
            
            $total = $subtotal; // Aquí podrías aplicar descuentos si los hay
            
            // Calcular pago recibido y cambio (solo para efectivo - metodo_pago_id = 1)
            $pagoRecibido = null;
            $cambio = null;
            if ($data['metodo_pago_id'] == 1 && isset($data['pago_recibido'])) {
                $pagoRecibido = floatval($data['pago_recibido']);
                $cambio = $pagoRecibido - $total;
                
                // Validar que el pago recibido sea suficiente
                if ($pagoRecibido < $total) {
                    throw new Exception("El efectivo recibido ($" . number_format($pagoRecibido, 2) . ") es insuficiente para cubrir el total ($" . number_format($total, 2) . ")");
                }
            }
            
            // Insertar venta
            $stmt = $this->db->prepare("
                INSERT INTO ventas (folio, usuario_id, subtotal, total, pago_recibido, cambio, metodo_pago_id, observaciones)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $folio,
                $this->user['usuario_id'],
                $subtotal,
                $total,
                $pagoRecibido,
                $cambio,
                $data['metodo_pago_id'],
                $data['observaciones'] ?? null
            ]);
            
            $ventaId = $this->db->lastInsertId();
            
            // Insertar detalles y actualizar stock
            $stmtDetalle = $this->db->prepare("
                INSERT INTO detalle_ventas (
                    venta_id, producto_id, nombre_producto, cantidad, 
                    precio_unitario, subtotal, tipo_venta, peso_gramos
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($detalles as $detalle) {
                $stmtDetalle->execute([
                    $ventaId,
                    $detalle['producto_id'],
                    $detalle['nombre_producto'],
                    $detalle['cantidad'],
                    $detalle['precio_unitario'],
                    $detalle['subtotal'],
                    $detalle['tipo_venta'],
                    $detalle['peso_gramos']
                ]);
                
                // Calcular cantidad a descontar del stock
                // Para productos a granel: convertir gramos a kg y multiplicar por cantidad de bolsas
                // Para otros productos: usar cantidad directamente
                $cantidadStock = $detalle['cantidad'];
                if ($detalle['tipo_venta'] === 'granel' && $detalle['peso_gramos']) {
                    // Convertir: (peso en gramos / 1000) * cantidad de bolsas = kg totales
                    $cantidadStock = ($detalle['peso_gramos'] / 1000) * $detalle['cantidad'];
                }
                
                // Actualizar stock usando stored procedure
                $stmt = $this->db->prepare("
                    CALL actualizar_stock_venta(?, ?, ?, ?)
                ");
                $stmt->execute([
                    $detalle['producto_id'],
                    $cantidadStock,  // Ahora envía kg para granel, cantidad para otros
                    $this->user['usuario_id'],
                    $ventaId
                ]);
            }
            
            $this->db->commit();
            
            // Obtener venta completa
            $venta = $this->obtenerVentaCompleta($ventaId);
            
            Response::success($venta, 'Venta realizada exitosamente', 201);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error('Error al procesar venta: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Cancelar una venta
     */
    public function cancelarVenta($id) {
        $this->auth->requirePermission($this->user, 'pos', 'cancelar');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['motivo'])) {
            Response::validationError(['motivo' => 'Debe especificar el motivo de cancelación']);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Obtener venta
            $stmt = $this->db->prepare("SELECT * FROM ventas WHERE id = ?");
            $stmt->execute([$id]);
            $venta = $stmt->fetch();
            
            if (!$venta) {
                Response::notFound('Venta no encontrada');
            }
            
            if ($venta['estado'] === 'cancelada') {
                Response::error('La venta ya está cancelada', 400);
            }
            
            // Obtener detalles de la venta
            $stmt = $this->db->prepare("SELECT * FROM detalle_ventas WHERE venta_id = ?");
            $stmt->execute([$id]);
            $detalles = $stmt->fetchAll();
            
            // Revertir stock
            foreach ($detalles as $detalle) {
                $stmt = $this->db->prepare("
                    UPDATE productos 
                    SET stock_actual = stock_actual + ? 
                    WHERE id = ?
                ");
                $stmt->execute([$detalle['cantidad'], $detalle['producto_id']]);
                
                // Registrar movimiento
                $stmt = $this->db->prepare("
                    INSERT INTO movimientos_inventario (
                        producto_id, tipo_movimiento, cantidad, 
                        stock_anterior, stock_nuevo, usuario_id, venta_id, justificacion
                    )
                    SELECT 
                        id, 'cancelacion', ?, 
                        stock_actual - ?, stock_actual, ?, ?, ?
                    FROM productos WHERE id = ?
                ");
                $stmt->execute([
                    $detalle['cantidad'],
                    $detalle['cantidad'],
                    $this->user['usuario_id'],
                    $id,
                    $data['motivo'],
                    $detalle['producto_id']
                ]);
            }
            
            // Marcar venta como cancelada
            $stmt = $this->db->prepare("
                UPDATE ventas 
                SET estado = 'cancelada', 
                    cancelada_por = ?, 
                    motivo_cancelacion = ?,
                    fecha_cancelacion = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $this->user['usuario_id'],
                $data['motivo'],
                $id
            ]);
            
            $this->db->commit();
            
            Response::success(null, 'Venta cancelada exitosamente');
            
        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error('Error al cancelar venta: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Listar ventas
     */
    public function listarVentas() {
        $this->auth->requirePermission($this->user, 'ventas', 'ver');
        
        try {
            $fecha = $_GET['fecha'] ?? null;
            $estado = $_GET['estado'] ?? null;
            $limite = $_GET['limite'] ?? 50;
            
            $sql = "
                SELECT v.*, u.nombre as vendedor_nombre, mp.nombre as metodo_pago
                FROM ventas v
                INNER JOIN usuarios u ON v.usuario_id = u.id
                INNER JOIN metodos_pago mp ON v.metodo_pago_id = mp.id
                WHERE 1=1
            ";
            
            $params = [];
            
            if ($fecha) {
                $sql .= " AND DATE(v.created_at) = ?";
                $params[] = $fecha;
            }
            
            if ($estado) {
                $sql .= " AND v.estado = ?";
                $params[] = $estado;
            }
            
            $sql .= " ORDER BY v.created_at DESC LIMIT ?";
            $params[] = (int)$limite;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $ventas = $stmt->fetchAll();
            
            Response::success($ventas, 'Lista de ventas');
            
        } catch (Exception $e) {
            Response::error('Error al listar ventas: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obtener una venta completa con detalles
     */
    private function obtenerVentaCompleta($id) {
        $stmt = $this->db->prepare("
            SELECT v.*, u.nombre as vendedor_nombre, mp.nombre as metodo_pago
            FROM ventas v
            INNER JOIN usuarios u ON v.usuario_id = u.id
            INNER JOIN metodos_pago mp ON v.metodo_pago_id = mp.id
            WHERE v.id = ?
        ");
        $stmt->execute([$id]);
        $venta = $stmt->fetch();
        
        $stmt = $this->db->prepare("SELECT * FROM detalle_ventas WHERE venta_id = ?");
        $stmt->execute([$id]);
        $venta['productos'] = $stmt->fetchAll();
        
        return $venta;
    }
    
    /**
     * Obtener una venta por ID
     */
    public function obtenerVenta($id) {
        $this->auth->requirePermission($this->user, 'ventas', 'ver');
        
        try {
            $venta = $this->obtenerVentaCompleta($id);
            
            if (!$venta) {
                Response::notFound('Venta no encontrada');
            }
            
            Response::success($venta, 'Datos de la venta');
            
        } catch (Exception $e) {
            Response::error('Error al obtener venta: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Listar métodos de pago
     */
    public function listarMetodosPago() {
        try {
            $stmt = $this->db->query("SELECT * FROM metodos_pago WHERE activo = 1 ORDER BY nombre");
            $metodos = $stmt->fetchAll();
            
            Response::success($metodos, 'Métodos de pago disponibles');
            
        } catch (Exception $e) {
            Response::error('Error al listar métodos de pago: ' . $e->getMessage(), 500);
        }
    }
}

// Router
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$segments = explode('/', trim($path, '/'));

$api = new POSAPI();

if ($method === 'POST' && $path === '/venta') {
    $api->crearVenta();
} elseif ($method === 'POST' && count($segments) === 2 && $segments[1] === 'cancelar') {
    $api->cancelarVenta($segments[0]);
} elseif ($method === 'GET' && $path === '/ventas') {
    $api->listarVentas();
} elseif ($method === 'GET' && count($segments) === 2 && $segments[0] === 'venta') {
    $api->obtenerVenta($segments[1]);
} elseif ($method === 'GET' && $path === '/metodos-pago') {
    $api->listarMetodosPago();
} else {
    Response::notFound('Endpoint no encontrado');
}
