<?php
require_once __DIR__ . '/../config/config.php';

/**
 * API de Reportes
 */

class ReportesAPI {
    private $db;
    private $auth;
    private $user;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->auth = new AuthMiddleware();
        $this->user = $this->auth->authenticate();
    }
    
    /**
     * Reporte de ventas (diarias, semanales, mensuales)
     */
    public function reporteVentas() {
        $this->auth->requirePermission($this->user, 'reportes', 'ver');
        
        $periodo = $_GET['periodo'] ?? 'diario';
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        
        try {
            $sql = "";
            $params = [];
            
            if ($periodo === 'diario') {
                $sql = "
                    SELECT 
                        DATE(created_at) as fecha,
                        COUNT(*) as num_ventas,
                        SUM(total) as total_ventas,
                        AVG(total) as promedio_venta,
                        SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as ventas_canceladas
                    FROM ventas
                    WHERE DATE(created_at) = ?
                    GROUP BY DATE(created_at)
                ";
                $params = [$fecha];
                
            } elseif ($periodo === 'semanal') {
                $sql = "
                    SELECT 
                        YEARWEEK(created_at, 1) as semana,
                        MIN(DATE(created_at)) as fecha_inicio,
                        MAX(DATE(created_at)) as fecha_fin,
                        COUNT(*) as num_ventas,
                        SUM(total) as total_ventas,
                        AVG(total) as promedio_venta,
                        SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as ventas_canceladas
                    FROM ventas
                    WHERE YEARWEEK(created_at, 1) = YEARWEEK(?, 1)
                    GROUP BY YEARWEEK(created_at, 1)
                ";
                $params = [$fecha];
                
            } elseif ($periodo === 'mensual') {
                $sql = "
                    SELECT 
                        YEAR(created_at) as año,
                        MONTH(created_at) as mes,
                        COUNT(*) as num_ventas,
                        SUM(total) as total_ventas,
                        AVG(total) as promedio_venta,
                        SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as ventas_canceladas
                    FROM ventas
                    WHERE YEAR(created_at) = YEAR(?) AND MONTH(created_at) = MONTH(?)
                    GROUP BY YEAR(created_at), MONTH(created_at)
                ";
                $params = [$fecha, $fecha];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $reporte = $stmt->fetch();
            
            // Obtener desglose por método de pago
            $sqlMetodos = "
                SELECT 
                    mp.nombre as metodo_pago,
                    COUNT(v.id) as num_ventas,
                    SUM(v.total) as total
                FROM ventas v
                INNER JOIN metodos_pago mp ON v.metodo_pago_id = mp.id
                WHERE v.estado = 'completada'
            ";
            
            if ($periodo === 'diario') {
                $sqlMetodos .= " AND DATE(v.created_at) = ?";
            } elseif ($periodo === 'semanal') {
                $sqlMetodos .= " AND YEARWEEK(v.created_at, 1) = YEARWEEK(?, 1)";
            } elseif ($periodo === 'mensual') {
                $sqlMetodos .= " AND YEAR(v.created_at) = YEAR(?) AND MONTH(v.created_at) = MONTH(?)";
            }
            
            $sqlMetodos .= " GROUP BY mp.id, mp.nombre";
            
            $stmt = $this->db->prepare($sqlMetodos);
            $stmt->execute($params);
            $metodosPago = $stmt->fetchAll();
            
            $resultado = [
                'periodo' => $periodo,
                'resumen' => $reporte ?: [
                    'num_ventas' => 0,
                    'total_ventas' => 0,
                    'promedio_venta' => 0,
                    'ventas_canceladas' => 0
                ],
                'metodos_pago' => $metodosPago
            ];
            
            Response::success($resultado, 'Reporte de ventas');
            
        } catch (Exception $e) {
            Response::error('Error al generar reporte de ventas: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Reporte de productos más vendidos
     */
    public function productosTop() {
        $this->auth->requirePermission($this->user, 'reportes', 'ver');
        
        $limite = $_GET['limite'] ?? 10;
        $fecha_inicio = $_GET['fecha_inicio'] ?? null;
        $fecha_fin = $_GET['fecha_fin'] ?? null;
        
        try {
            $sql = "
                SELECT 
                    p.id,
                    p.nombre,
                    c.nombre as categoria,
                    p.tipo_producto,
                    COUNT(dv.id) as num_ventas,
                    SUM(dv.cantidad) as cantidad_total,
                    SUM(dv.subtotal) as total_vendido,
                    p.stock_actual
                FROM productos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                INNER JOIN detalle_ventas dv ON p.id = dv.producto_id
                INNER JOIN ventas v ON dv.venta_id = v.id
                WHERE v.estado = 'completada'
            ";
            
            $params = [];
            
            if ($fecha_inicio && $fecha_fin) {
                $sql .= " AND DATE(v.created_at) BETWEEN ? AND ?";
                $params[] = $fecha_inicio;
                $params[] = $fecha_fin;
            }
            
            $sql .= "
                GROUP BY p.id, p.nombre, c.nombre, p.tipo_producto, p.stock_actual
                ORDER BY total_vendido DESC
                LIMIT ?
            ";
            $params[] = (int)$limite;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $productos = $stmt->fetchAll();
            
            Response::success($productos, 'Productos más vendidos');
            
        } catch (Exception $e) {
            Response::error('Error al generar reporte: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Reporte de productos menos vendidos
     */
    public function productosMenosVendidos() {
        $this->auth->requirePermission($this->user, 'reportes', 'ver');
        
        $limite = $_GET['limite'] ?? 10;
        $fecha_inicio = $_GET['fecha_inicio'] ?? null;
        $fecha_fin = $_GET['fecha_fin'] ?? null;
        
        try {
            $sql = "
                SELECT 
                    p.id,
                    p.nombre,
                    c.nombre as categoria,
                    p.tipo_producto,
                    COALESCE(COUNT(dv.id), 0) as num_ventas,
                    COALESCE(SUM(dv.cantidad), 0) as cantidad_total,
                    COALESCE(SUM(dv.subtotal), 0) as total_vendido,
                    p.stock_actual
                FROM productos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN detalle_ventas dv ON p.id = dv.producto_id
                LEFT JOIN ventas v ON dv.venta_id = v.id AND v.estado = 'completada'
            ";
            
            $params = [];
            
            if ($fecha_inicio && $fecha_fin) {
                $sql .= " AND (v.id IS NULL OR DATE(v.created_at) BETWEEN ? AND ?)";
                $params[] = $fecha_inicio;
                $params[] = $fecha_fin;
            }
            
            $sql .= "
                WHERE p.activo = 1
                GROUP BY p.id, p.nombre, c.nombre, p.tipo_producto, p.stock_actual
                ORDER BY num_ventas ASC, total_vendido ASC
                LIMIT ?
            ";
            $params[] = (int)$limite;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $productos = $stmt->fetchAll();
            
            Response::success($productos, 'Productos menos vendidos');
            
        } catch (Exception $e) {
            Response::error('Error al generar reporte: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Reporte de productos con stock bajo
     */
    public function stockBajo() {
        $this->auth->requirePermission($this->user, 'reportes', 'ver');
        
        try {
            $stmt = $this->db->query("SELECT * FROM vista_stock_bajo");
            $productos = $stmt->fetchAll();
            
            Response::success($productos, 'Productos con stock bajo');
            
        } catch (Exception $e) {
            Response::error('Error al generar reporte: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Corte de caja
     */
    public function corteCaja() {
        $this->auth->requirePermission($this->user, 'reportes', 'ver');
        
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d 00:00:00');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d 23:59:59');
        $usuario_id = $_GET['usuario_id'] ?? $this->user['usuario_id'];
        
        try {
            // Resumen general
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as num_transacciones,
                    SUM(total) as total_ventas,
                    SUM(CASE WHEN mp.nombre = 'Efectivo' THEN v.total ELSE 0 END) as total_efectivo,
                    SUM(CASE WHEN mp.nombre = 'Tarjeta' THEN v.total ELSE 0 END) as total_tarjeta,
                    SUM(CASE WHEN mp.nombre NOT IN ('Efectivo', 'Tarjeta') THEN v.total ELSE 0 END) as total_otros
                FROM ventas v
                INNER JOIN metodos_pago mp ON v.metodo_pago_id = mp.id
                WHERE v.estado = 'completada'
                AND v.created_at BETWEEN ? AND ?
                AND v.usuario_id = ?
            ");
            $stmt->execute([$fecha_inicio, $fecha_fin, $usuario_id]);
            $resumen = $stmt->fetch();
            
            // Desglose de productos a granel vendidos
            $stmt = $this->db->prepare("
                SELECT 
                    p.id,
                    p.nombre,
                    SUM((dv.peso_gramos / 1000) * dv.cantidad) as total_kg_vendidos,
                    SUM(CASE WHEN dv.peso_gramos = 100 THEN dv.cantidad ELSE 0 END) as bolsas_100g,
                    SUM(CASE WHEN dv.peso_gramos = 250 THEN dv.cantidad ELSE 0 END) as bolsas_250g,
                    SUM(CASE WHEN dv.peso_gramos = 500 THEN dv.cantidad ELSE 0 END) as bolsas_500g,
                    SUM(CASE WHEN dv.peso_gramos = 1000 THEN dv.cantidad ELSE 0 END) as bolsas_1kg,
                    SUM(dv.subtotal) as total_venta
                FROM detalle_ventas dv
                INNER JOIN ventas v ON dv.venta_id = v.id
                INNER JOIN productos p ON dv.producto_id = p.id
                WHERE v.estado = 'completada'
                AND v.created_at BETWEEN ? AND ?
                AND v.usuario_id = ?
                AND dv.tipo_venta = 'granel'
                GROUP BY p.id, p.nombre
            ");
            $stmt->execute([$fecha_inicio, $fecha_fin, $usuario_id]);
            $productosGranel = $stmt->fetchAll();
            
            // Desglose de productos por pieza
            $stmt = $this->db->prepare("
                SELECT 
                    p.id,
                    p.nombre,
                    c.nombre as categoria,
                    SUM(dv.cantidad) as cantidad_vendida,
                    SUM(dv.subtotal) as total_venta,
                    COUNT(DISTINCT dv.venta_id) as num_transacciones
                FROM detalle_ventas dv
                INNER JOIN ventas v ON dv.venta_id = v.id
                INNER JOIN productos p ON dv.producto_id = p.id
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE v.estado = 'completada'
                AND v.created_at BETWEEN ? AND ?
                AND v.usuario_id = ?
                AND dv.tipo_venta = 'pieza'
                GROUP BY p.id, p.nombre, c.nombre
                ORDER BY total_venta DESC
            ");
            $stmt->execute([$fecha_inicio, $fecha_fin, $usuario_id]);
            $productosPieza = $stmt->fetchAll();
            
            // Desglose de productos de anaquel
            $stmt = $this->db->prepare("
                SELECT 
                    p.id,
                    p.nombre,
                    c.nombre as categoria,
                    SUM(dv.cantidad) as cantidad_vendida,
                    SUM(dv.subtotal) as total_venta,
                    COUNT(DISTINCT dv.venta_id) as num_transacciones
                FROM detalle_ventas dv
                INNER JOIN ventas v ON dv.venta_id = v.id
                INNER JOIN productos p ON dv.producto_id = p.id
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE v.estado = 'completada'
                AND v.created_at BETWEEN ? AND ?
                AND v.usuario_id = ?
                AND dv.tipo_venta = 'anaquel'
                GROUP BY p.id, p.nombre, c.nombre
                ORDER BY total_venta DESC
            ");
            $stmt->execute([$fecha_inicio, $fecha_fin, $usuario_id]);
            $productosAnaquel = $stmt->fetchAll();
            
            // Guardar corte de caja
            if (isset($_GET['guardar']) && $_GET['guardar'] === '1') {
                $stmt = $this->db->prepare("
                    INSERT INTO cortes_caja (
                        usuario_id, fecha_inicio, fecha_fin, total_ventas,
                        total_efectivo, total_tarjeta, total_otros, num_transacciones, observaciones
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $usuario_id,
                    $fecha_inicio,
                    $fecha_fin,
                    $resumen['total_ventas'],
                    $resumen['total_efectivo'],
                    $resumen['total_tarjeta'],
                    $resumen['total_otros'],
                    $resumen['num_transacciones'],
                    $_GET['observaciones'] ?? null
                ]);
                
                $corteId = $this->db->lastInsertId();
                
                // Guardar detalles de granel
                if (!empty($productosGranel)) {
                    $stmtDetalle = $this->db->prepare("
                        INSERT INTO detalle_corte_granel (
                            corte_id, producto_id, nombre_producto, total_kg_vendidos,
                            bolsas_100g, bolsas_250g, bolsas_500g, bolsas_1kg, total_venta
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    foreach ($productosGranel as $producto) {
                        $stmtDetalle->execute([
                            $corteId,
                            $producto['id'],
                            $producto['nombre'],
                            $producto['total_kg_vendidos'],
                            $producto['bolsas_100g'],
                            $producto['bolsas_250g'],
                            $producto['bolsas_500g'],
                            $producto['bolsas_1kg'],
                            $producto['total_venta']
                        ]);
                    }
                }
            }
            
            $resultado = [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'resumen' => $resumen,
                'productos_granel' => $productosGranel,
                'productos_pieza' => $productosPieza,
                'productos_anaquel' => $productosAnaquel
            ];
            
            Response::success($resultado, 'Corte de caja');
            
        } catch (Exception $e) {
            Response::error('Error al generar corte de caja: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Historial de cortes de caja
     */
    public function historialCortes() {
        $this->auth->requirePermission($this->user, 'reportes', 'ver');
        
        try {
            $stmt = $this->db->query("
                SELECT c.*, u.nombre as usuario_nombre
                FROM cortes_caja c
                INNER JOIN usuarios u ON c.usuario_id = u.id
                ORDER BY c.created_at DESC
                LIMIT 50
            ");
            $cortes = $stmt->fetchAll();
            
            Response::success($cortes, 'Historial de cortes de caja');
            
        } catch (Exception $e) {
            Response::error('Error al obtener historial: ' . $e->getMessage(), 500);
        }
    }
}

// Router
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';

$api = new ReportesAPI();

if ($method === 'GET' && $path === '/ventas') {
    $api->reporteVentas();
} elseif ($method === 'GET' && $path === '/productos-top') {
    $api->productosTop();
} elseif ($method === 'GET' && $path === '/productos-menos-vendidos') {
    $api->productosMenosVendidos();
} elseif ($method === 'GET' && $path === '/stock-bajo') {
    $api->stockBajo();
} elseif ($method === 'GET' && $path === '/corte-caja') {
    $api->corteCaja();
} elseif ($method === 'GET' && $path === '/cortes-historial') {
    $api->historialCortes();
} else {
    Response::notFound('Endpoint no encontrado');
}
