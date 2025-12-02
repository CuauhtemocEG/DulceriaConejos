<?php
require_once __DIR__ . '/../config/config.php';

/**
 * API de Tickets
 */

class TicketsAPI {
    private $db;
    private $auth;
    private $user;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->auth = new AuthMiddleware();
        $this->user = $this->auth->authenticate();
    }
    
    /**
     * Imprimir ticket de venta
     */
    public function imprimirTicket($ventaId) {
        $this->auth->requirePermission($this->user, 'pos', 'vender');
        
        $tipo = $_GET['tipo'] ?? 'cliente';
        
        try {
            // Obtener venta
            $stmt = $this->db->prepare("
                SELECT v.*, u.nombre as vendedor_nombre, mp.nombre as metodo_pago
                FROM ventas v
                INNER JOIN usuarios u ON v.usuario_id = u.id
                INNER JOIN metodos_pago mp ON v.metodo_pago_id = mp.id
                WHERE v.id = ?
            ");
            $stmt->execute([$ventaId]);
            $venta = $stmt->fetch();
            
            if (!$venta) {
                Response::notFound('Venta no encontrada');
            }
            
            // Obtener productos
            $stmt = $this->db->prepare("SELECT * FROM detalle_ventas WHERE venta_id = ?");
            $stmt->execute([$ventaId]);
            $venta['productos'] = $stmt->fetchAll();
            
            // Generar HTML del ticket
            $html = Ticket::generarHTML($venta);
            
            // Generar PDF y guardarlo
            $pdfPath = Ticket::generarPDF($venta);
            
            // Actualizar venta con la ruta del PDF
            $stmt = $this->db->prepare("UPDATE ventas SET pdf_ticket = ? WHERE id = ?");
            $stmt->execute([$pdfPath, $ventaId]);
            
            // Guardar registro de impresión
            $stmt = $this->db->prepare("
                SELECT numero_reimpresion 
                FROM tickets_impresos 
                WHERE venta_id = ? AND tipo_ticket = ?
                ORDER BY numero_reimpresion DESC 
                LIMIT 1
            ");
            $stmt->execute([$ventaId, $tipo]);
            $ultimaImpresion = $stmt->fetch();
            $numeroReimpresion = $ultimaImpresion ? $ultimaImpresion['numero_reimpresion'] + 1 : 0;
            
            $stmt = $this->db->prepare("
                INSERT INTO tickets_impresos (venta_id, tipo_ticket, impreso_por, numero_reimpresion, contenido_html)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $ventaId,
                $tipo,
                $this->user['usuario_id'],
                $numeroReimpresion,
                $html
            ]);
            
            // Retornar JSON con el HTML y la ruta del PDF
            Response::success([
                'ticket_html' => $html,
                'pdf_path' => $pdfPath,
                'folio' => $venta['folio']
            ], 'Ticket generado correctamente');
            
        } catch (Exception $e) {
            Response::error('Error al imprimir ticket: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Reimprimir ticket
     */
    public function reimprimirTicket($ticketId) {
        $this->auth->requirePermission($this->user, 'pos', 'reimprimir');
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM tickets_impresos WHERE id = ?");
            $stmt->execute([$ticketId]);
            $ticket = $stmt->fetch();
            
            if (!$ticket) {
                Response::notFound('Ticket no encontrado');
            }
            
            // Registrar nueva reimpresión
            $stmt = $this->db->prepare("
                INSERT INTO tickets_impresos (venta_id, tipo_ticket, impreso_por, numero_reimpresion, contenido_html)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $ticket['venta_id'],
                $ticket['tipo_ticket'],
                $this->user['usuario_id'],
                $ticket['numero_reimpresion'] + 1,
                $ticket['contenido_html']
            ]);
            
            header('Content-Type: text/html; charset=utf-8');
            echo $ticket['contenido_html'];
            exit();
            
        } catch (Exception $e) {
            Response::error('Error al reimprimir ticket: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Imprimir corte de caja
     */
    public function imprimirCorte() {
        $this->auth->requirePermission($this->user, 'reportes', 'ver');
        
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d 00:00:00');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d 23:59:59');
        $usuario_id = $_GET['usuario_id'] ?? $this->user['usuario_id'];
        
        try {
            // Obtener datos del corte (reutilizar lógica de reportes)
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
            
            $stmt = $this->db->prepare("
                SELECT 
                    p.id,
                    p.nombre,
                    SUM(dv.cantidad) as total_kg_vendidos,
                    SUM(CASE WHEN dv.peso_gramos = 100 THEN 1 ELSE 0 END) as bolsas_100g,
                    SUM(CASE WHEN dv.peso_gramos = 250 THEN 1 ELSE 0 END) as bolsas_250g,
                    SUM(CASE WHEN dv.peso_gramos = 500 THEN 1 ELSE 0 END) as bolsas_500g,
                    SUM(CASE WHEN dv.peso_gramos = 1000 THEN 1 ELSE 0 END) as bolsas_1kg,
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
            
            $corte = [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'resumen' => $resumen,
                'productos_granel' => $productosGranel
            ];
            
            $html = Ticket::generarCorteHTML($corte);
            
            header('Content-Type: text/html; charset=utf-8');
            echo $html;
            exit();
            
        } catch (Exception $e) {
            Response::error('Error al imprimir corte: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Historial de tickets impresos
     */
    public function historialTickets() {
        $this->auth->requirePermission($this->user, 'ventas', 'ver');
        
        try {
            $limite = $_GET['limite'] ?? 50;
            
            $stmt = $this->db->prepare("
                SELECT t.*, v.folio, u.nombre as impreso_por_nombre
                FROM tickets_impresos t
                INNER JOIN ventas v ON t.venta_id = v.id
                INNER JOIN usuarios u ON t.impreso_por = u.id
                ORDER BY t.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([(int)$limite]);
            $tickets = $stmt->fetchAll();
            
            Response::success($tickets, 'Historial de tickets');
            
        } catch (Exception $e) {
            Response::error('Error al obtener historial: ' . $e->getMessage(), 500);
        }
    }
}

// Router
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$segments = explode('/', trim($path, '/'));

$api = new TicketsAPI();

if ($method === 'GET' && count($segments) === 1 && is_numeric($segments[0])) {
    $api->imprimirTicket($segments[0]);
} elseif ($method === 'GET' && count($segments) === 2 && $segments[0] === 'venta') {
    $api->imprimirTicket($segments[1]);
} elseif ($method === 'GET' && count($segments) === 2 && $segments[0] === 'reimprimir') {
    $api->reimprimirTicket($segments[1]);
} elseif ($method === 'GET' && $path === '/corte') {
    $api->imprimirCorte();
} elseif ($method === 'GET' && $path === '/historial') {
    $api->historialTickets();
} else {
    Response::notFound('Endpoint no encontrado');
}
