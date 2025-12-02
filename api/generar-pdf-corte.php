<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Generador de PDF para Corte de Caja
 * Genera un documento PDF ejecutivo de alta calidad
 */

class PDFCorteGenerator {
    private $db;
    private $auth;
    private $user;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        
        // Autenticar usando el token del POST o GET
        $token = $_POST['token'] ?? $_GET['token'] ?? null;
        
        if ($token) {
            // Validar token manualmente
            try {
                $stmt = $this->db->prepare("
                    SELECT u.*, r.nombre as rol_nombre, r.permisos as rol_permisos
                    FROM sesiones s
                    INNER JOIN usuarios u ON s.usuario_id = u.id
                    INNER JOIN roles r ON u.rol_id = r.id
                    WHERE s.token = ? AND s.expira_en > NOW() AND s.activo = 1
                ");
                $stmt->execute([$token]);
                $this->user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$this->user) {
                    header('Content-Type: application/json');
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Token inválido o expirado'
                    ]);
                    exit;
                }
            } catch (Exception $e) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error de autenticación: ' . $e->getMessage()
                ]);
                exit;
            }
        } else {
            // Intentar autenticación normal
            $this->auth = new AuthMiddleware();
            $this->user = $this->auth->authenticate();
        }
    }
    
    public function generarPDF() {
        // Verificar permisos de manera flexible
        $rolNombre = strtolower($this->user['rol_nombre'] ?? '');
        $permisos = json_decode($this->user['rol_permisos'] ?? '{}', true);
        $tienePermiso = false;
        
        // Verificar si el usuario es dueño, admin o tiene permisos de reportes
        if (in_array($rolNombre, ['dueño', 'dueno', 'admin', 'administrador'])) {
            $tienePermiso = true;
        } elseif (isset($permisos['reportes'])) {
            // Verificar si tiene el permiso 'ver' en reportes
            if (is_array($permisos['reportes']) && in_array('ver', $permisos['reportes'])) {
                $tienePermiso = true;
            }
        }
        
        if (!$tienePermiso) {
            // Log para debugging
            error_log('DEBUG PDF - Usuario: ' . $this->user['id']);
            error_log('DEBUG PDF - Rol nombre: ' . ($this->user['rol_nombre'] ?? 'no definido'));
            error_log('DEBUG PDF - Rol permisos: ' . ($this->user['rol_permisos'] ?? 'no definidos'));
            error_log('DEBUG PDF - Permisos decodificados: ' . print_r($permisos, true));
            
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'No tiene permisos para generar reportes',
                'debug' => [
                    'usuario_id' => $this->user['id'],
                    'rol_nombre' => $this->user['rol_nombre'] ?? null,
                    'permisos_raw' => $this->user['rol_permisos'] ?? null,
                    'permisos_decoded' => $permisos
                ]
            ]);
            exit;
        }
        
        $fecha_inicio = $_POST['fecha_inicio'] ?? $_GET['fecha_inicio'] ?? date('Y-m-d 00:00:00');
        $fecha_fin = $_POST['fecha_fin'] ?? $_GET['fecha_fin'] ?? date('Y-m-d 23:59:59');
        $usuario_id = $_POST['usuario_id'] ?? $_GET['usuario_id'] ?? $this->user['id'];
        
        try {
            // Obtener datos del corte
            $datos = $this->obtenerDatosCorte($fecha_inicio, $fecha_fin, $usuario_id);
            
            // Generar HTML para PDF
            $html = $this->generarHTML($datos);
            
            // Configurar Dompdf
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('enable_php', false);
            $options->set('chroot', realpath(__DIR__ . '/../'));
            $options->set('enable_html5_parser', true);
            
            // Crear instancia de Dompdf
            $dompdf = new Dompdf($options);
            
            // Cargar HTML
            $dompdf->loadHtml($html);
            
            // Configurar tamaño de página y orientación
            $dompdf->setPaper('Letter', 'portrait');
            
            // Renderizar PDF
            $dompdf->render();
            
            // Enviar PDF al navegador
            $dompdf->stream('Corte_Caja_' . date('Y-m-d_His') . '.pdf', [
                'Attachment' => false  // false = mostrar en navegador, true = descargar
            ]);
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al generar PDF: ' . $e->getMessage()
            ]);
        }
    }
    
    private function obtenerDatosCorte($fecha_inicio, $fecha_fin, $usuario_id) {
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
        
        // Productos a granel
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
        
        // Productos por pieza
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
        
        // Productos de anaquel
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
        
        // Obtener información del usuario
        $stmt = $this->db->prepare("SELECT nombre FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch();
        
        return [
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'usuario' => $usuario,
            'resumen' => $resumen,
            'productos_granel' => $productosGranel,
            'productos_pieza' => $productosPieza,
            'productos_anaquel' => $productosAnaquel
        ];
    }
    
    private function generarHTML($datos) {
        $fechaInicio = date('d/m/Y H:i', strtotime($datos['fecha_inicio']));
        $fechaFin = date('d/m/Y H:i', strtotime($datos['fecha_fin']));
        $nombreUsuario = $datos['usuario']['nombre'];
        
        $resumen = $datos['resumen'];
        $productosGranel = $datos['productos_granel'];
        $productosPieza = $datos['productos_pieza'];
        $productosAnaquel = $datos['productos_anaquel'];
        
        $ticketPromedio = $resumen['num_transacciones'] > 0 
            ? $resumen['total_ventas'] / $resumen['num_transacciones'] 
            : 0;
        
        $porcentajeEfectivo = $resumen['total_ventas'] > 0 
            ? ($resumen['total_efectivo'] / $resumen['total_ventas']) * 100 
            : 0;
        $porcentajeTarjeta = $resumen['total_ventas'] > 0 
            ? ($resumen['total_tarjeta'] / $resumen['total_ventas']) * 100 
            : 0;
        $porcentajeOtros = $resumen['total_ventas'] > 0 
            ? ($resumen['total_otros'] / $resumen['total_ventas']) * 100 
            : 0;
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Corte de Caja - Dulcería Conejos</title>
            <style>
                @page {
                    margin: 20mm 15mm;
                    size: letter;
                }
                
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'DejaVu Sans', Arial, sans-serif;
                    font-size: 8pt;
                    color: #1a1a1a;
                    line-height: 1.2;
                    padding: 10mm;
                }
                
                .header {
                    margin-bottom: 12px;
                    padding: 12px 15px;
                    background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
                    color: white;
                    border-radius: 5px;
                    position: relative;
                    display: table;
                    width: 100%;
                }
                
                .logo-container {
                    display: table-cell;
                    vertical-align: middle;
                    width: 100px;
                    padding-right: 15px;
                }
                
                .logo {
                    max-width: 80px;
                    max-height: 80px;
                    display: block;
                }
                
                .header-text {
                    display: table-cell;
                    vertical-align: middle;
                    color: black;
                }
                
                .header h1 {
                    font-size: 20pt;
                    margin-bottom: 5px;
                    font-weight: bold;
                    letter-spacing: 2px;
                }
                
                .header h2 {
                    font-size: 14pt;
                    font-weight: normal;
                    letter-spacing: 1px;
                }
                
                .info-box {
                    background: #f8f9fa;
                    padding: 6px 8px;
                    border-radius: 3px;
                    margin-bottom: 8px;
                    border: 1px solid #dee2e6;
                }
                
                .info-row {
                    display: inline-block;
                    width: 49%;
                    margin-bottom: 2px;
                    font-size: 7pt;
                }
                
                .info-label {
                    font-weight: bold;
                    color: #495057;
                }
                
                .resumen-grid {
                    display: table;
                    width: 100%;
                    margin-bottom: 8px;
                }
                
                .resumen-card {
                    display: table-cell;
                    width: 33.33%;
                    padding: 6px;
                    text-align: center;
                    border: 2px solid;
                    border-radius: 3px;
                }
                
                .resumen-card.ventas {
                    border-color: #7c3aed;
                    background: #f3e8ff;
                }
                
                .resumen-card.promedio {
                    border-color: #10b981;
                    background: #d1fae5;
                }
                
                .resumen-card.trans {
                    border-color: #3b82f6;
                    background: #dbeafe;
                }
                
                .resumen-card h3 {
                    font-size: 7pt;
                    margin-bottom: 2px;
                    color: #4b5563;
                }
                
                .resumen-card .valor {
                    font-size: 14pt;
                    font-weight: bold;
                    color: #1a1a1a;
                }
                
                .resumen-card .detalle {
                    font-size: 6pt;
                    color: #6b7280;
                    margin-top: 2px;
                }
                
                .section {
                    margin-bottom: 8px;
                    page-break-inside: avoid;
                }
                
                .section-title {
                    background: #4b5563;
                    color: white;
                    padding: 4px 6px;
                    border-radius: 3px;
                    margin-bottom: 4px;
                    font-size: 9pt;
                    font-weight: bold;
                }
                
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 6px;
                    font-size: 7pt;
                }
                
                table th {
                    background: #e5e7eb;
                    padding: 3px 4px;
                    text-align: left;
                    font-weight: bold;
                    border: 1px solid #d1d5db;
                }
                
                table td {
                    padding: 2px 4px;
                    border: 1px solid #e5e7eb;
                }
                
                table tr:nth-child(even) {
                    background: #f9fafb;
                }
                
                .total-row {
                    background: #fef3c7 !important;
                    font-weight: bold;
                }
                
                .text-right {
                    text-align: right;
                }
                
                .text-center {
                    text-align: center;
                }
                
                .footer {
                    margin-top: 10px;
                    padding-top: 6px;
                    border-top: 1px solid #e5e7eb;
                    text-align: center;
                    color: #6b7280;
                    font-size: 6pt;
                }
                
                .firma {
                    margin-top: 15px;
                    text-align: center;
                }
                
                .firma-linea {
                    border-top: 1px solid #000;
                    width: 200px;
                    margin: 0 auto 5px;
                }
                
                .metodos-pago {
                    display: table;
                    width: 100%;
                    margin-bottom: 8px;
                }
                
                .metodo-card {
                    display: table-cell;
                    width: 33.33%;
                    padding: 5px;
                    text-align: center;
                    border: 1px solid #dee2e6;
                }
                
                .metodo-card.efectivo {
                    background: #d1fae5;
                }
                
                .metodo-card.tarjeta {
                    background: #dbeafe;
                }
                
                .metodo-card.otros {
                    background: #f3e8ff;
                }
                
                .metodo-card h4 {
                    font-size: 7pt;
                    margin-bottom: 2px;
                    color: #4b5563;
                }
                
                .metodo-card .monto {
                    font-size: 11pt;
                    font-weight: bold;
                }
                
                .metodo-card .porcentaje {
                    font-size: 6pt;
                    color: #6b7280;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo-container">
                    <img src="<?php echo __DIR__ . '/../public/img/DulceriaConejos.png'; ?>" alt="Dulcería Conejos" class="logo">
                </div>
                <div class="header-text">
                    <h1>Dulcería Conejos</h1>
                    <h2>Corte de Caja</h2>
                </div>
            </div>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Inicio:</span> <?php echo $fechaInicio; ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Fin:</span> <?php echo $fechaFin; ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Cajero:</span> <?php echo htmlspecialchars($nombreUsuario); ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Generado:</span> <?php echo date('d/m/Y H:i'); ?>
                </div>
            </div>
            
            <div class="resumen-grid">
                <div class="resumen-card ventas">
                    <h3>Total Ventas</h3>
                    <div class="valor">$<?php echo number_format($resumen['total_ventas'], 2); ?></div>
                </div>
                <div class="resumen-card promedio">
                    <h3>Ticket Promedio</h3>
                    <div class="valor">$<?php echo number_format($ticketPromedio, 2); ?></div>
                </div>
                <div class="resumen-card trans">
                    <h3>Transacciones</h3>
                    <div class="valor"><?php echo $resumen['num_transacciones']; ?></div>
                </div>
            </div>
            
            <div class="section">
                <h3 class="section-title">Métodos de Pago</h3>
                <div class="metodos-pago">
                    <div class="metodo-card efectivo">
                        <h4>Efectivo</h4>
                        <div class="monto">$<?php echo number_format($resumen['total_efectivo'], 2); ?></div>
                        <div class="porcentaje"><?php echo number_format($porcentajeEfectivo, 1); ?>%</div>
                    </div>
                    <div class="metodo-card tarjeta">
                        <h4>Tarjeta</h4>
                        <div class="monto">$<?php echo number_format($resumen['total_tarjeta'], 2); ?></div>
                        <div class="porcentaje"><?php echo number_format($porcentajeTarjeta, 1); ?>%</div>
                    </div>
                    <div class="metodo-card otros">
                        <h4>Otros</h4>
                        <div class="monto">$<?php echo number_format($resumen['total_otros'], 2); ?></div>
                        <div class="porcentaje"><?php echo number_format($porcentajeOtros, 1); ?>%</div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($productosGranel)): ?>
            <div class="section">
                <h3 class="section-title">Productos a Granel</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">100g</th>
                            <th class="text-center">250g</th>
                            <th class="text-center">500g</th>
                            <th class="text-center">1kg</th>
                            <th class="text-right">Total $</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalGranel = 0;
                        foreach ($productosGranel as $prod): 
                            $totalGranel += $prod['total_venta'];
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                            <td class="text-center"><?php echo $prod['bolsas_100g']; ?></td>
                            <td class="text-center"><?php echo $prod['bolsas_250g']; ?></td>
                            <td class="text-center"><?php echo $prod['bolsas_500g']; ?></td>
                            <td class="text-center"><?php echo $prod['bolsas_1kg']; ?></td>
                            <td class="text-right">$<?php echo number_format($prod['total_venta'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="5" class="text-right">TOTAL:</td>
                            <td class="text-right">$<?php echo number_format($totalGranel, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($productosPieza)): ?>
            <div class="section">
                <h3 class="section-title">Productos por Pieza</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalPieza = 0;
                        $cantidadPieza = 0;
                        foreach ($productosPieza as $prod): 
                            $totalPieza += $prod['total_venta'];
                            $cantidadPieza += $prod['cantidad_vendida'];
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($prod['categoria'] ?? 'N/A'); ?></td>
                            <td class="text-center"><?php echo number_format($prod['cantidad_vendida']); ?></td>
                            <td class="text-right">$<?php echo number_format($prod['total_venta'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="2" class="text-right">TOTAL:</td>
                            <td class="text-center"><?php echo number_format($cantidadPieza); ?></td>
                            <td class="text-right">$<?php echo number_format($totalPieza, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($productosAnaquel)): ?>
            <div class="section">
                <h3 class="section-title">Productos de Anaquel</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalAnaquel = 0;
                        $cantidadAnaquel = 0;
                        foreach ($productosAnaquel as $prod): 
                            $totalAnaquel += $prod['total_venta'];
                            $cantidadAnaquel += $prod['cantidad_vendida'];
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($prod['categoria'] ?? 'N/A'); ?></td>
                            <td class="text-center"><?php echo number_format($prod['cantidad_vendida']); ?></td>
                            <td class="text-right">$<?php echo number_format($prod['total_venta'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="2" class="text-right">TOTAL:</td>
                            <td class="text-center"><?php echo number_format($cantidadAnaquel); ?></td>
                            <td class="text-right">$<?php echo number_format($totalAnaquel, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <div class="firma">
                <div class="firma-linea"></div>
                <p style="font-size: 7pt;"><strong>Firma del Cajero</strong></p>
                <p style="font-size: 7pt;"><?php echo htmlspecialchars($nombreUsuario); ?></p>
            </div>
            
            <div class="footer">
                <p>Dulcería Conejos - Sistema POS | Generado: <?php echo date('d/m/Y H:i:s'); ?></p>
            </div>
            
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}

// Ejecutar
$generator = new PDFCorteGenerator();
$generator->generarPDF();
