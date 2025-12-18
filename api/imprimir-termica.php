<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Clase para generar comandos ESC/POS para impresoras térmicas
 * Ajustado para papel de 58mm (32 caracteres de ancho)
 */
class ImpresorTermica {

    // Comandos ESC/POS básicos
    const ESC = "\x1B";
    const GS = "\x1D";
    const NUL = "\x00";
    const LF = "\x0A";
    const CR = "\x0D";
    const INIT = "\x1B\x40";
    const RESET = "\x1B\x40";
    const FEED_LINE = "\x0A";
    const CUT_PAPER = "\x1D\x56\x00";
    const PARTIAL_CUT = "\x1D\x56\x01";
    
    const ALIGN_LEFT = "\x1B\x61\x00";
    const ALIGN_CENTER = "\x1B\x61\x01";
    const ALIGN_RIGHT = "\x1B\x61\x02";
    
    const TEXT_NORMAL = "\x1B\x21\x00";
    const TEXT_BOLD = "\x1B\x45\x01";
    const TEXT_BOLD_OFF = "\x1B\x45\x00";
    const TEXT_UNDERLINE = "\x1B\x2D\x01";
    const TEXT_UNDERLINE_OFF = "\x1B\x2D\x00";
    
    const TEXT_SIZE_NORMAL = "\x1B\x21\x00";
    const TEXT_SIZE_WIDE = "\x1B\x21\x20";
    const TEXT_SIZE_TALL = "\x1B\x21\x10";
    const TEXT_SIZE_LARGE = "\x1B\x21\x30";
    
    private $contenido = "";
    private $db;
    
    public function __construct() {
        $this->contenido = self::INIT;
        $this->db = Database::getInstance()->getConnection();
    }
    public function texto($texto, $alineacion = 'left', $bold = false, $size = 'normal') {
        switch ($alineacion) {
            case 'center':
                $this->contenido .= self::ALIGN_CENTER;
                break;
            case 'right':
                $this->contenido .= self::ALIGN_RIGHT;
                break;
            default:
                $this->contenido .= self::ALIGN_LEFT;
        }
        
        switch ($size) {
            case 'large':
                $this->contenido .= self::TEXT_SIZE_LARGE;
                break;
            case 'wide':
                $this->contenido .= self::TEXT_SIZE_WIDE;
                break;
            case 'tall':
                $this->contenido .= self::TEXT_SIZE_TALL;
                break;
            default:
                $this->contenido .= self::TEXT_SIZE_NORMAL;
        }
        
        if ($bold) {
            $this->contenido .= self::TEXT_BOLD;
        }
        
        $texto = $this->limpiarTexto($texto);
        $this->contenido .= $texto;
        
        if ($bold) {
            $this->contenido .= self::TEXT_BOLD_OFF;
        }
        
        $this->contenido .= self::LF;
    }
    
    public function linea($caracter = '-', $longitud = 32) {
        $this->contenido .= self::ALIGN_LEFT;
        $this->contenido .= str_repeat($caracter, $longitud) . self::LF;
    }
    
    private function cargarImagen($rutaImagen) {
        $info = getimagesize($rutaImagen);
        if (!$info) return false;
        
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($rutaImagen);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($rutaImagen);
            case IMAGETYPE_GIF:
                return imagecreatefromgif($rutaImagen);
            default:
                return false;
        }
    }
    
    public function imagenLogo() {
        try {
            $rutaLogo = __DIR__ . '/../public/img/DulceriaConejos.png';
            if (file_exists($rutaLogo)) {
                return $this->imagenGigante($rutaLogo);
            }
            // Si no existe el logo, continuar sin error
            return false;
        } catch (Exception $e) {
            // Si hay error al cargar la imagen, continuar sin el logo
            error_log('Error al cargar logo: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * IMAGEN GIGANTE - Método optimizado usando GS v 0
     * Este es el método que SÍ funciona correctamente en térmicas
     */
    public function imagenGigante($rutaImagen) {
        try {
            if (!file_exists($rutaImagen)) {
                error_log('Imagen no encontrada: ' . $rutaImagen);
                return false;
            }
            
            // Cargar imagen
            $imagenOriginal = $this->cargarImagen($rutaImagen);
            if (!$imagenOriginal) {
                error_log('No se pudo cargar imagen: ' . $rutaImagen);
                return false;
            }
            
            // Obtener dimensiones originales
            $info = @getimagesize($rutaImagen);
            if (!$info) {
                imagedestroy($imagenOriginal);
                return false;
            }
            
            $anchoOriginal = $info[0];
            $altoOriginal = $info[1];
            
            // Tamaño optimizado para 58mm (360 píxeles máximo)
            $anchoFinal = 360;
            $altoFinal = intval(($altoOriginal * $anchoFinal) / $anchoOriginal);
            
            // Limitar altura
            if ($altoFinal > 180) {
                $altoFinal = 180;
                $anchoFinal = intval(($anchoOriginal * $altoFinal) / $altoOriginal);
            }
            
            // Crear imagen redimensionada con fondo blanco
            $imagenGigante = imagecreatetruecolor($anchoFinal, $altoFinal);
            if (!$imagenGigante) {
                imagedestroy($imagenOriginal);
                return false;
            }
            
            $blanco = imagecolorallocate($imagenGigante, 255, 255, 255);
            imagefill($imagenGigante, 0, 0, $blanco);
            
            imagecopyresampled(
                $imagenGigante, $imagenOriginal,
                0, 0, 0, 0,
                $anchoFinal, $altoFinal, $anchoOriginal, $altoOriginal
            );
            
            // Convertir a comandos ESC/POS usando GS v 0
            $comandoGigante = $this->crearComandoImagenGigante($imagenGigante, $anchoFinal, $altoFinal);
            
            if ($comandoGigante) {
                $this->contenido .= self::ALIGN_CENTER;
                $this->contenido .= $comandoGigante;
                $this->contenido .= self::LF;
            }
            
            // Limpiar memoria
            imagedestroy($imagenOriginal);
            imagedestroy($imagenGigante);
            
            return true;
        } catch (Exception $e) {
            error_log('Error en imagenGigante: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear comando de imagen usando GS v 0 (método que funciona correctamente)
     */
    private function crearComandoImagenGigante($imagen, $ancho, $alto) {
        $umbral = 80; // Umbral para convertir a blanco/negro
        
        // Comando GS v 0 - Formato correcto para impresoras térmicas
        $comando = "\x1D\x76\x30" . chr(0);
        
        // Ancho en bytes (little endian)
        $anchoBytes = ceil($ancho / 8);
        $comando .= chr($anchoBytes & 0xFF);
        $comando .= chr(($anchoBytes >> 8) & 0xFF);
        
        // Alto (little endian)
        $comando .= chr($alto & 0xFF);
        $comando .= chr(($alto >> 8) & 0xFF);
        
        // Convertir imagen a datos bitmap
        for ($y = 0; $y < $alto; $y++) {
            for ($x = 0; $x < $anchoBytes * 8; $x += 8) {
                $byte = 0;
                
                for ($bit = 0; $bit < 8; $bit++) {
                    $px = $x + $bit;
                    if ($px < $ancho) {
                        $color = imagecolorat($imagen, $px, $y);
                        $r = ($color >> 16) & 0xFF;
                        $g = ($color >> 8) & 0xFF;
                        $b = $color & 0xFF;
                        $gris = intval(0.299 * $r + 0.587 * $g + 0.114 * $b);
                        
                        // Si es oscuro, marcar el bit
                        if ($gris < $umbral) {
                            $byte |= (1 << (7 - $bit));
                        }
                    }
                }
                
                $comando .= chr($byte);
            }
        }
        
        return $comando;
    }
        
        public function saltoLinea($cantidad = 1) {
            for ($i = 0; $i < $cantidad; $i++) {
                $this->contenido .= self::LF;
            }
        }
        
        public function tablaProductos($productos) {
            $this->texto("PRODUCTO        CANT   PRECIO", 'center', true);
            $this->linea('-', 32);
            
            foreach ($productos as $producto) {
                $nombre = substr($this->limpiarTexto($producto['nombre']), 0, 14);
                // Cantidad sin decimales - mostrar solo el número entero
                $cantidadNum = intval($producto['cantidad']);
                $cantidad = str_pad($cantidadNum, 3, ' ', STR_PAD_LEFT);
                // Precio con formato 0000.00 (7 caracteres: $0000.00)
                $precio = str_pad('$' . number_format($producto['subtotal'], 2, '.', ''), 8, ' ', STR_PAD_LEFT);

                // 14 chars nombre + 3 espacios + 3 cant + 3 espacios + 8 precio = 31 chars
                $linea = str_pad($nombre, 14) . '   ' . $cantidad . '   ' . $precio;
                $this->contenido .= self::ALIGN_LEFT . $linea . self::LF;
                
                if (isset($producto['peso_gramos']) && $producto['peso_gramos'] > 0) {
                    $peso = " (" . $producto['peso_gramos'] . "g)";
                    $this->contenido .= self::ALIGN_LEFT . $peso . self::LF;
                }
            }
            
            $this->linea('-', 32);
        }
        
        public function cortar($parcial = false) {
            $this->saltoLinea(3);
            if ($parcial) {
                $this->contenido .= self::PARTIAL_CUT;
            } else {
                $this->contenido .= self::CUT_PAPER;
            }
        }
        
        private function limpiarTexto($texto) {
            $caracteres = array(
                'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
                'ñ' => 'n', 'Ñ' => 'N', 'ü' => 'u', 'Ü' => 'U',
                'ç' => 'c', 'Ç' => 'C'
            );
            
            $texto = strtr($texto, $caracteres);
            $texto = preg_replace('/[^\x20-\x7E]/', '', $texto);
            
            return $texto;
        }
        
        public function obtenerComandos() {
            return $this->contenido;
        }
    
    public function imprimir($nombreImpresora) {
        $archivoTemp = tempnam(sys_get_temp_dir(), 'ticket_') . '.prn';
        file_put_contents($archivoTemp, $this->contenido);
        
        $os = strtolower(PHP_OS);
        $resultado = '';
        $success = false;
        $metodo = '';
        
        try {
            if (strpos($os, 'darwin') !== false) {
                $comando = "lpr -P " . escapeshellarg($nombreImpresora) . " " . escapeshellarg($archivoTemp) . " 2>&1";
                exec($comando, $output, $returnCode);
                $resultado = implode("\n", $output);
                $metodo = 'lpr';
                
                if ($returnCode !== 0) {
                    $output = [];
                    $comando = "lpr -P " . escapeshellarg($nombreImpresora) . " -o raw " . escapeshellarg($archivoTemp) . " 2>&1";
                    exec($comando, $output, $returnCode);
                    $resultado = implode("\n", $output);
                    $metodo = 'lpr -o raw';
                }
                
                if ($returnCode !== 0) {
                    $output = [];
                    $comando = "lpr -P " . escapeshellarg($nombreImpresora) . " -l " . escapeshellarg($archivoTemp) . " 2>&1";
                    exec($comando, $output, $returnCode);
                    $resultado = implode("\n", $output);
                    $metodo = 'lpr -l';
                }
                
                $success = ($returnCode === 0);
            }
            elseif (strpos($os, 'win') !== false) {
                // Para Windows: Crear un script VBS temporal que envíe a la impresora
                $scriptVBS = tempnam(sys_get_temp_dir(), 'print_') . '.vbs';
                
                $vbsContent = "Set objFSO = CreateObject(\"Scripting.FileSystemObject\")\n";
                $vbsContent .= "Set objFile = objFSO.OpenTextFile(\"" . str_replace('\\', '\\\\', $archivoTemp) . "\", 1, False, 0)\n";
                $vbsContent .= "strContent = objFile.ReadAll()\n";
                $vbsContent .= "objFile.Close\n\n";
                $vbsContent .= "Set objPrinter = CreateObject(\"WScript.Network\")\n";
                $vbsContent .= "objPrinter.SetDefaultPrinter \"" . $nombreImpresora . "\"\n\n";
                $vbsContent .= "Set objShell = CreateObject(\"WScript.Shell\")\n";
                $vbsContent .= "objShell.Run \"cmd /c copy /b \\\"\" & \"" . str_replace('\\', '\\\\', $archivoTemp) . "\" & \"\\\" \\\"\\\\\\\\localhost\\\\\" & \"" . $nombreImpresora . "\" & \"\\\"\", 0, True\n";
                
                file_put_contents($scriptVBS, $vbsContent);
                
                // Ejecutar el script VBS
                $comando = "cscript //NoLogo \"$scriptVBS\" 2>&1";
                exec($comando, $output, $returnCode);
                $resultado = implode("\n", $output);
                $metodo = 'vbscript-copy';
                
                // Limpiar script temporal
                @unlink($scriptVBS);
                
                // Si falla con VBS, intentar método directo con copy
                if ($returnCode !== 0) {
                    $output = [];
                    $comando = "copy /B \"$archivoTemp\" \"\\\\localhost\\$nombreImpresora\" 2>&1";
                    exec($comando, $output, $returnCode);
                    $resultado = implode("\n", $output);
                    $metodo = 'copy-localhost';
                }
                
                // Si también falla, intentar método net use
                if ($returnCode !== 0) {
                    $output = [];
                    $comando = "net use LPT1: \\\\localhost\\\"$nombreImpresora\" 2>&1 && copy /B \"$archivoTemp\" LPT1: 2>&1";
                    exec($comando, $output, $returnCode);
                    $resultado = implode("\n", $output);
                    $metodo = 'net-use-lpt1';
                }
                
                $success = ($returnCode === 0);
            }
            elseif (strpos($os, 'linux') !== false) {
                $comando = "lpr -P " . escapeshellarg($nombreImpresora) . " -o raw " . escapeshellarg($archivoTemp) . " 2>&1";
                exec($comando, $output, $returnCode);
                $resultado = implode("\n", $output);
                $metodo = 'lpr -o raw';
                
                if ($returnCode !== 0) {
                    $comando = "lpr -P " . escapeshellarg($nombreImpresora) . " " . escapeshellarg($archivoTemp) . " 2>&1";
                    exec($comando, $output2, $returnCode2);
                    $resultado = implode("\n", $output2);
                    $returnCode = $returnCode2;
                    $metodo = 'lpr';
                }
                
                $success = ($returnCode === 0);
            }
            
        } catch (Exception $e) {
            $resultado = 'Excepción: ' . $e->getMessage();
            $success = false;
            $metodo = 'error';
        } finally {
            sleep(1);
            if (file_exists($archivoTemp)) {
                @unlink($archivoTemp);
            }
        }
        
        return [
            'success' => $success,
            'mensaje' => $success ? 'Ticket enviado a la impresora correctamente' : 'Error al imprimir: ' . $resultado,
            'salida' => $resultado,
            'sistema' => $os,
            'impresora' => $nombreImpresora,
            'metodo' => $metodo,
            'archivo_temporal' => $archivoTemp,
            'tamano_bytes' => strlen($this->contenido)
        ];
    }
}

// Procesar peticiones POST
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    header('Content-Type: application/json');
    
    try {
        $rawInput = file_get_contents('php://input');
        error_log('=== IMPRIMIR-TERMICA.PHP ===');
        error_log('Raw input recibido: ' . substr($rawInput, 0, 500));
        
        $input = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
        }
        
        error_log('Input decodificado: ' . print_r($input, true));
        
        if (!isset($input['tipo'])) {
            throw new Exception('Tipo de impresión no especificado');
        }
        
        error_log('Tipo de impresión: ' . $input['tipo']);
        
        $impresora = new ImpresorTermica();
        
        switch ($input['tipo']) {
            case 'prueba':
                $impresora->imagenLogo();
                $impresora->saltoLinea();
                $impresora->texto(NEGOCIO_DIRECCION, 'center');
                $impresora->texto('Sucursal: ' . NEGOCIO_SUCURSAL, 'center');
                $impresora->saltoLinea();
                $impresora->texto('PRUEBA TERMICA', 'center', true, 'large');
                $impresora->saltoLinea();
                $impresora->texto('Fecha: ' . date('d/m/Y H:i:s'), 'left');
                $impresora->texto('Sistema POS', 'left');
                $impresora->saltoLinea();
                $impresora->linea('=', 32);
                $impresora->saltoLinea();
                $impresora->texto('Si puede leer', 'center');
                $impresora->texto('este mensaje,', 'center');
                $impresora->texto('la impresora', 'center');
                $impresora->texto('esta OK.', 'center');
                $impresora->saltoLinea();
                $impresora->linea('=', 32);
                $impresora->texto('Fin del test', 'center');
                $impresora->cortar();
                
                if (isset($input['impresora'])) {
                    $resultadoImpresion = $impresora->imprimir($input['impresora']);
                    echo json_encode($resultadoImpresion);
                } else {
                    echo json_encode([
                        'success' => true,
                        'comandos' => base64_encode($impresora->obtenerComandos()),
                        'message' => 'Comandos ESC/POS generados'
                    ]);
                }
                break;
                
            case 'preview':
                error_log('=== CASO PREVIEW ===');
                
                if (!isset($input['datos'])) {
                    throw new Exception('Datos del preview no especificados');
                }
                
                $datos = $input['datos'];
                error_log('Datos del preview: ' . print_r($datos, true));
                error_log('Productos: ' . count($datos['productos']));
                
                $impresora->imagenLogo();
                $impresora->saltoLinea();
                $impresora->texto(NEGOCIO_DIRECCION, 'center');
                $impresora->texto('Sucursal: ' . NEGOCIO_SUCURSAL, 'center');
                $impresora->saltoLinea();
                $impresora->linea('=', 32);
                $impresora->texto('*** PREVIEW ***', 'center', true, 'large');
                $impresora->texto('(No es ticket oficial)', 'center');
                $impresora->linea('=', 32);
                $impresora->saltoLinea();
                $impresora->texto('Fecha: ' . date('d/m/Y H:i:s'), 'left');
                $impresora->saltoLinea();
                $impresora->linea('-', 32);
                
                error_log('Llamando tablaProductos...');
                $impresora->tablaProductos($datos['productos']);
                error_log('tablaProductos completado');
                
                $impresora->saltoLinea();
                $impresora->texto('TOTAL:', 'right', true, 'wide');
                $impresora->texto('$' . number_format($datos['total'], 2), 'right', true, 'large');
                $impresora->saltoLinea();
                $impresora->linea('-', 32);
                $impresora->texto('PAGO:', 'left', true);
                $impresora->texto($datos['metodo_pago'], 'left');
                $impresora->saltoLinea();
                $impresora->linea('=', 32);
                $impresora->texto('*** PREVIEW ***', 'center', true);
                $impresora->texto('Procesa la venta para', 'center');
                $impresora->texto('generar ticket oficial', 'center');
                $impresora->saltoLinea();
                $impresora->cortar();
                
                if (isset($input['impresora'])) {
                    error_log('Imprimiendo en: ' . $input['impresora']);
                    $resultadoImpresion = $impresora->imprimir($input['impresora']);
                    error_log('Resultado impresión: ' . print_r($resultadoImpresion, true));
                    echo json_encode($resultadoImpresion);
                } else {
                    error_log('Generando comandos sin imprimir');
                    echo json_encode([
                        'success' => true,
                        'comandos' => base64_encode($impresora->obtenerComandos()),
                        'message' => 'Comandos ESC/POS generados para preview'
                    ]);
                }
                break;
                
            case 'ticket':
                if (!isset($input['venta_id'])) {
                    throw new Exception('ID de venta no especificado');
                }
                
                $db = Database::getInstance()->getConnection();
                
                $stmt = $db->prepare("
                    SELECT v.*, u.nombre as vendedor_nombre, mp.nombre as metodo_pago
                    FROM ventas v
                    INNER JOIN usuarios u ON v.usuario_id = u.id
                    INNER JOIN metodos_pago mp ON v.metodo_pago_id = mp.id
                    WHERE v.id = ?
                ");
                $stmt->execute([$input['venta_id']]);
                $venta = $stmt->fetch();
                
                if (!$venta) {
                    throw new Exception('Venta no encontrada');
                }
                
                $stmt = $db->prepare("
                    SELECT dv.*, p.nombre as nombre_producto
                    FROM detalle_ventas dv
                    INNER JOIN productos p ON dv.producto_id = p.id
                    WHERE dv.venta_id = ?
                ");
                $stmt->execute([$input['venta_id']]);
                $productos = $stmt->fetchAll();
                
                $impresora->imagenLogo();
                $impresora->saltoLinea();
                $impresora->texto(NEGOCIO_DIRECCION, 'center');
                $impresora->texto('Sucursal: ' . NEGOCIO_SUCURSAL, 'center');
                $impresora->saltoLinea();
                $impresora->linea('=', 32);
                $impresora->texto('Atendio:'. $venta['vendedor_nombre'], 'left');
                $impresora->texto('Folio: #' . $venta['folio'], 'left');
                $impresora->texto('Fecha: ' . date('d/m/Y H:i:s', strtotime($venta['created_at'])), 'left');
                $impresora->linea('=', 32);
                $impresora->saltoLinea();
                
                $productos_formateados = array_map(function($p) {
                    return [
                        'nombre' => $p['nombre_producto'],
                        'cantidad' => $p['cantidad'],
                        'subtotal' => $p['subtotal'],
                        'peso_gramos' => $p['peso_gramos'] ?? null
                    ];
                }, $productos);
                
                $impresora->tablaProductos($productos_formateados);
            
                $impresora->saltoLinea();
                $impresora->texto('TOTAL: $' . number_format($venta['total'], 2), 'right', true, 'wide');
                $impresora->saltoLinea();
                $impresora->linea('-', 16);
                $impresora->texto('METODO DE PAGO:', 'left', true);
                $impresora->texto($venta['metodo_pago'], 'left');
                
                // Mostrar efectivo recibido y cambio solo si es pago en efectivo
                if (isset($venta['pago_recibido']) && $venta['pago_recibido'] > 0) {
                    $impresora->saltoLinea();
                    $impresora->texto('Efectivo: $' . number_format($venta['pago_recibido'], 2), 'left');
                    
                    if (isset($venta['cambio']) && $venta['cambio'] > 0) {
                        $impresora->texto('Cambio:   $' . number_format($venta['cambio'], 2), 'left', true);
                    } else {
                        $impresora->texto('Pago exacto', 'left');
                    }
                }
                
                $impresora->saltoLinea();
                $impresora->linea('=', 32);
                $impresora->texto('Gracias!', 'center', true);
                $impresora->saltoLinea();
                $impresora->cortar();
                
                if (isset($input['impresora'])) {
                    $resultadoImpresion = $impresora->imprimir($input['impresora']);
                    echo json_encode($resultadoImpresion);
                } else {
                    echo json_encode([
                        'success' => true,
                        'comandos' => base64_encode($impresora->obtenerComandos()),
                        'message' => 'Comandos ESC/POS generados'
                    ]);
                }
                break;
            default:
                throw new Exception('Tipo de impresión no válido');
        }
        
    } catch (Exception $e) {
        error_log('❌ ERROR en imprimir-termica.php: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
}
?>