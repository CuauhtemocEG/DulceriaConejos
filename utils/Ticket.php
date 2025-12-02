<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Utilidad para generación de tickets
 */
class Ticket {
    
    /**
     * Generar HTML del ticket para navegador (con botones)
     */
    public static function generarHTML($venta) {
        $fecha = date('d/m/Y H:i:s', strtotime($venta['created_at']));
        
        $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket ' . $venta['folio'] . '</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        body {
            font-family: "Courier New", monospace;
            width: 300px;
            margin: 0 auto;
            padding: 10px;
            font-size: 12px;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; }
        .right { text-align: right; }
        .producto { font-size: 11px; }
        .total { font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="center bold">
        ' . NOMBRE_NEGOCIO . '
    </div>
    <div class="center">
        ' . DIRECCION_NEGOCIO . '<br>
        Tel: ' . TELEFONO_NEGOCIO . '<br>
        RFC: ' . RFC_NEGOCIO . '
    </div>
    <div class="line"></div>
    <div>
        <strong>Folio:</strong> ' . $venta['folio'] . '<br>
        <strong>Fecha:</strong> ' . $fecha . '<br>
        <strong>Cajero:</strong> ' . $venta['vendedor_nombre'] . '
    </div>
    <div class="line"></div>
    <table class="producto">
        <thead>
            <tr>
                <td><strong>Producto</strong></td>
                <td class="right"><strong>Cant.</strong></td>
                <td class="right"><strong>Precio</strong></td>
                <td class="right"><strong>Total</strong></td>
            </tr>
        </thead>
        <tbody>';
        
        foreach ($venta['productos'] as $producto) {
            $cantidad = number_format($producto['cantidad'], 2);
            $precio = '$' . number_format($producto['precio_unitario'], 2);
            $subtotal = '$' . number_format($producto['subtotal'], 2);
            
            $descripcion = $producto['nombre_producto'];
            if ($producto['tipo_venta'] === 'granel' && $producto['peso_gramos']) {
                $descripcion .= ' (' . $producto['peso_gramos'] . 'g)';
            }
            
            $html .= '
            <tr>
                <td colspan="4">' . $descripcion . '</td>
            </tr>
            <tr>
                <td></td>
                <td class="right">' . $cantidad . '</td>
                <td class="right">' . $precio . '</td>
                <td class="right">' . $subtotal . '</td>
            </tr>';
        }
        
        $html .= '
        </tbody>
    </table>
    <div class="line"></div>
    <table>
        <tr>
            <td><strong>Subtotal:</strong></td>
            <td class="right">$' . number_format($venta['subtotal'], 2) . '</td>
        </tr>
        <tr class="total">
            <td>TOTAL:</td>
            <td class="right">$' . number_format($venta['total'], 2) . '</td>
        </tr>
        <tr>
            <td><strong>Método de pago:</strong></td>
            <td class="right">' . $venta['metodo_pago'] . '</td>
        </tr>
    </table>
    <div class="line"></div>
    <div class="center">
        ¡Gracias por su compra!<br>
        <small>Conserve su ticket</small>
    </div>
    <br>
    <div class="center no-print">
        <button onclick="window.print()">Imprimir</button>
        <button onclick="window.close()">Cerrar</button>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Generar PDF del ticket
     */
    public static function generarPDF($venta) {
        // Crear directorio si no existe
        $dirTickets = __DIR__ . '/../documents/tickets';
        if (!file_exists($dirTickets)) {
            mkdir($dirTickets, 0755, true);
        }
        
        // Generar HTML sin botones (versión para PDF)
        $html = self::generarHTMLParaPDF($venta);
        
        // Configurar Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Courier');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        
        // Tamaño de papel para ticket térmico (80mm x auto)
        $dompdf->setPaper([0, 0, 226.77, 841.89], 'portrait'); // 80mm de ancho
        
        // Renderizar PDF
        $dompdf->render();
        
        // Generar nombre de archivo
        $nombreArchivo = 'ticket_' . $venta['folio'] . '.pdf';
        $rutaCompleta = $dirTickets . '/' . $nombreArchivo;
        
        // Guardar PDF
        file_put_contents($rutaCompleta, $dompdf->output());
        
        // Retornar ruta relativa
        return 'documents/tickets/' . $nombreArchivo;
    }
    
    /**
     * Generar HTML del ticket sin botones (para PDF)
     */
    private static function generarHTMLParaPDF($venta) {
        $fecha = date('d/m/Y H:i:s', strtotime($venta['created_at']));
        
        // Logo
        $logoHtml = '';
        if (defined('NEGOCIO_LOGO') && file_exists(__DIR__ . '/../' . NEGOCIO_LOGO)) {
            $logoPath = __DIR__ . '/../' . NEGOCIO_LOGO;
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoMime = mime_content_type($logoPath);
            $logoHtml = '<img src="data:' . $logoMime . ';base64,' . $logoData . '" style="max-width: 150px; height: auto; margin: 10px auto; display: block;">';
        }
        
        $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ticket ' . $venta['folio'] . '</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: "Courier New", monospace;
            width: 280px;
            margin: 0 auto;
            padding: 8px;
            font-size: 10px;
            line-height: 1.3;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; }
        .right { text-align: right; }
        .producto { font-size: 9px; }
        .total { font-size: 12px; font-weight: bold; }
        h1 { font-size: 13px; margin: 4px 0; }
        .info-negocio { font-size: 9px; line-height: 1.2; }
        .info-venta { font-size: 9px; }
    </style>
</head>
<body>
    ' . $logoHtml . '
    <h1 class="center">
        ' . (defined('NEGOCIO_NOMBRE') ? NEGOCIO_NOMBRE : NOMBRE_NEGOCIO) . '
    </h1>
    <div class="center info-negocio">
        ' . (defined('NEGOCIO_DIRECCION') ? NEGOCIO_DIRECCION : DIRECCION_NEGOCIO) . '<br>';
        
        if (defined('NEGOCIO_SUCURSAL')) {
            $html .= 'Sucursal: ' . NEGOCIO_SUCURSAL . '<br>';
        }
        
        $html .= '
    </div>
    <div class="line"></div>
    <div class="info-venta">
        <strong>Atendió:</strong> ' . $venta['vendedor_nombre'] . '<br>
        <strong>Folio:</strong> #' . $venta['folio'] . '<br>
        <strong>Fecha:</strong> ' . $fecha . '
    </div>
    <div class="line"></div>
    <table class="producto">
        <thead>
            <tr style="border-bottom: 1px solid #000;">
                <td style="width: 65%;"><strong>Producto</strong></td>
                <td class="right" style="width: 35%;"><strong>Subtotal</strong></td>
            </tr>
        </thead>
        <tbody>';
        
        foreach ($venta['productos'] as $producto) {
            $subtotal = '$' . number_format($producto['subtotal'], 2);
            
            // Nombre del producto (truncado si es muy largo)
            $nombre = $producto['nombre_producto'];
            if (strlen($nombre) > 25) {
                $nombre = substr($nombre, 0, 22) . '...';
            }
            
            $descripcion = $nombre;
            if ($producto['tipo_venta'] === 'granel' && $producto['peso_gramos']) {
                $descripcion .= ' ' . $producto['peso_gramos'] . 'g';
            }
            $descripcion .= ' x' . number_format($producto['cantidad'], 0);
            
            $html .= '
            <tr>
                <td>' . $descripcion . '</td>
                <td class="right">' . $subtotal . '</td>
            </tr>';
        }
        
        $html .= '
        </tbody>
    </table>
    <div class="line"></div>
    <table class="total">
        <tr>
            <td style="width: 50%;">TOTAL:</td>
            <td class="right" style="width: 50%;">$' . number_format($venta['total'], 2) . '</td>
        </tr>
    </table>
    <div class="line"></div>
    <table class="info-venta">
        <tr>
            <td style="width: 60%;">Método de pago:</td>
            <td class="right" style="width: 40%;">' . $venta['metodo_pago'] . '</td>
        </tr>';
        
        // Si es efectivo, mostrar recibido y cambio
        if (strtolower($venta['metodo_pago']) === 'efectivo' && isset($venta['efectivo_recibido'])) {
            $cambio = $venta['efectivo_recibido'] - $venta['total'];
            $html .= '
        <tr>
            <td>Recibido:</td>
            <td class="right">$' . number_format($venta['efectivo_recibido'], 2) . '</td>
        </tr>
        <tr>
            <td>Cambio:</td>
            <td class="right">$' . number_format($cambio, 2) . '</td>
        </tr>';
        }
        
        $html .= '
    </table>
    <div class="line"></div>
    <div class="center" style="margin-top: 8px; font-size: 10px;">
        <strong>¡Gracias por su compra!</strong>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Generar HTML del corte de caja
     */
    public static function generarCorteHTML($corte) {
        $fecha_inicio = date('d/m/Y H:i', strtotime($corte['fecha_inicio']));
        $fecha_fin = date('d/m/Y H:i', strtotime($corte['fecha_fin']));
        
        $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Corte de Caja</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        body {
            font-family: "Courier New", monospace;
            width: 300px;
            margin: 0 auto;
            padding: 10px;
            font-size: 12px;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 5px 0; }
        .double-line { border-top: 3px double #000; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; }
        .right { text-align: right; }
        .total { font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="center bold">
        CORTE DE CAJA
    </div>
    <div class="center">
        ' . NOMBRE_NEGOCIO . '
    </div>
    <div class="line"></div>
    <div>
        <strong>Periodo:</strong><br>
        ' . $fecha_inicio . '<br>
        ' . $fecha_fin . '
    </div>
    <div class="line"></div>
    <table>
        <tr>
            <td><strong>Total Transacciones:</strong></td>
            <td class="right">' . $corte['resumen']['num_transacciones'] . '</td>
        </tr>
    </table>
    <div class="line"></div>
    <table>
        <tr>
            <td><strong>Efectivo:</strong></td>
            <td class="right">$' . number_format($corte['resumen']['total_efectivo'], 2) . '</td>
        </tr>
        <tr>
            <td><strong>Tarjeta:</strong></td>
            <td class="right">$' . number_format($corte['resumen']['total_tarjeta'], 2) . '</td>
        </tr>
        <tr>
            <td><strong>Otros:</strong></td>
            <td class="right">$' . number_format($corte['resumen']['total_otros'], 2) . '</td>
        </tr>
    </table>
    <div class="double-line"></div>
    <table>
        <tr class="total">
            <td>TOTAL:</td>
            <td class="right">$' . number_format($corte['resumen']['total_ventas'], 2) . '</td>
        </tr>
    </table>';
        
        if (!empty($corte['productos_granel'])) {
            $html .= '
    <div class="double-line"></div>
    <div class="center bold">PRODUCTOS A GRANEL</div>
    <div class="line"></div>
    <table style="font-size: 10px;">';
            
            foreach ($corte['productos_granel'] as $producto) {
                $html .= '
        <tr>
            <td colspan="2"><strong>' . $producto['nombre'] . '</strong></td>
        </tr>
        <tr>
            <td>Total vendido:</td>
            <td class="right">' . number_format($producto['total_kg_vendidos'], 3) . ' kg</td>
        </tr>';
                
                if ($producto['bolsas_100g'] > 0) {
                    $html .= '
        <tr>
            <td>Bolsas 100g:</td>
            <td class="right">' . $producto['bolsas_100g'] . '</td>
        </tr>';
                }
                if ($producto['bolsas_250g'] > 0) {
                    $html .= '
        <tr>
            <td>Bolsas 1/4 kg:</td>
            <td class="right">' . $producto['bolsas_250g'] . '</td>
        </tr>';
                }
                if ($producto['bolsas_500g'] > 0) {
                    $html .= '
        <tr>
            <td>Bolsas 1/2 kg:</td>
            <td class="right">' . $producto['bolsas_500g'] . '</td>
        </tr>';
                }
                if ($producto['bolsas_1kg'] > 0) {
                    $html .= '
        <tr>
            <td>Bolsas 1 kg:</td>
            <td class="right">' . $producto['bolsas_1kg'] . '</td>
        </tr>';
                }
                
                $html .= '
        <tr>
            <td><strong>Total:</strong></td>
            <td class="right"><strong>$' . number_format($producto['total_venta'], 2) . '</strong></td>
        </tr>
        <tr><td colspan="2">&nbsp;</td></tr>';
            }
            
            $html .= '
    </table>';
        }
        
        $html .= '
    <div class="double-line"></div>
    <div class="center">
        <small>Documento generado el ' . date('d/m/Y H:i:s') . '</small>
    </div>
    <br>
    <div class="center no-print">
        <button onclick="window.print()">Imprimir</button>
        <button onclick="window.close()">Cerrar</button>
    </div>
</body>
</html>';
        
        return $html;
    }
}
