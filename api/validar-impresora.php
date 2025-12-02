<?php
/**
 * Script para validar conexión de impresora térmica
 * Detecta impresoras conectadas y valida configuración
 */

header('Content-Type: application/json');

function detectarImpresoras() {
    $os = strtolower(PHP_OS);
    $impresoras = [];
    
    try {
        // macOS
        if (strpos($os, 'darwin') !== false) {
            // Método 1: Usar lpstat -p para listar impresoras
            $comando = "lpstat -p 2>&1";
            exec($comando, $output, $returnCode);
            
            $salida = implode("\n", $output);
            
            if (!empty($salida)) {
                // Parsear salida de lpstat (soporta inglés y español)
                // Inglés: printer <nombre> is idle
                // Español: la impresora <nombre> está inactiva
                
                // Primero intentar patrón en español
                preg_match_all('/(?:la\s+)?impresora\s+([^\s]+)\s+está/i', $salida, $matchesES);
                
                if (isset($matchesES[1]) && !empty($matchesES[1])) {
                    foreach ($matchesES[1] as $nombre) {
                        if (!empty($nombre) && $nombre !== 'impresora') {
                            $impresoras[] = [
                                'nombre' => $nombre,
                                'estado' => 'disponible',
                                'tipo' => 'lpstat-p',
                                'so' => 'macOS',
                                'idioma' => 'español'
                            ];
                        }
                    }
                } else {
                    // Intentar patrón en inglés
                    preg_match_all('/printer\s+([^\s]+)\s+(?:is|disabled)/i', $salida, $matchesEN);
                    
                    if (isset($matchesEN[1]) && !empty($matchesEN[1])) {
                        foreach ($matchesEN[1] as $nombre) {
                            if (!empty($nombre) && $nombre !== 'printer') {
                                $impresoras[] = [
                                    'nombre' => $nombre,
                                    'estado' => 'disponible',
                                    'tipo' => 'lpstat-p',
                                    'so' => 'macOS',
                                    'idioma' => 'inglés'
                                ];
                            }
                        }
                    }
                }
            }
            
            // Método 2: Usar lpstat -a (impresoras que aceptan trabajos)
            if (empty($impresoras)) {
                $comando2 = "lpstat -a 2>&1";
                exec($comando2, $output2, $returnCode2);
                $salida2 = implode("\n", $output2);
                
                if (!empty($salida2)) {
                    // Español: <nombre> acepta peticiones desde
                    // Inglés: <nombre> accepting requests since
                    
                    // Intentar patrón en español
                    preg_match_all('/^([^\s]+)\s+acepta\s+peticiones/mi', $salida2, $matchesES2);
                    
                    if (isset($matchesES2[1]) && !empty($matchesES2[1])) {
                        foreach ($matchesES2[1] as $nombre) {
                            if (!empty($nombre)) {
                                $impresoras[] = [
                                    'nombre' => $nombre,
                                    'estado' => 'aceptando trabajos',
                                    'tipo' => 'lpstat-a',
                                    'so' => 'macOS',
                                    'idioma' => 'español'
                                ];
                            }
                        }
                    } else {
                        // Intentar patrón en inglés
                        preg_match_all('/^([^\s]+)\s+accepting\s+requests/mi', $salida2, $matchesEN2);
                        
                        if (isset($matchesEN2[1]) && !empty($matchesEN2[1])) {
                            foreach ($matchesEN2[1] as $nombre) {
                                if (!empty($nombre)) {
                                    $impresoras[] = [
                                        'nombre' => $nombre,
                                        'estado' => 'aceptando trabajos',
                                        'tipo' => 'lpstat-a',
                                        'so' => 'macOS',
                                        'idioma' => 'inglés'
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            
            // Método 3: Listar con lpstat -v (mostrar dispositivos)
            if (empty($impresoras)) {
                $comando3 = "lpstat -v 2>&1";
                exec($comando3, $output3, $returnCode3);
                $salida3 = implode("\n", $output3);
                
                if (!empty($salida3)) {
                    // Español: dispositivo para <nombre>:
                    // Inglés: device for <nombre>:
                    
                    // Intentar patrón en español
                    preg_match_all('/dispositivo\s+para\s+([^\s:]+)/i', $salida3, $matchesES3);
                    
                    if (isset($matchesES3[1]) && !empty($matchesES3[1])) {
                        foreach ($matchesES3[1] as $nombre) {
                            if (!empty($nombre)) {
                                $impresoras[] = [
                                    'nombre' => $nombre,
                                    'estado' => 'dispositivo detectado',
                                    'tipo' => 'lpstat-v',
                                    'so' => 'macOS',
                                    'idioma' => 'español'
                                ];
                            }
                        }
                    } else {
                        // Intentar patrón en inglés
                        preg_match_all('/device\s+for\s+([^\s:]+)/i', $salida3, $matchesEN3);
                        
                        if (isset($matchesEN3[1]) && !empty($matchesEN3[1])) {
                            foreach ($matchesEN3[1] as $nombre) {
                                if (!empty($nombre)) {
                                    $impresoras[] = [
                                        'nombre' => $nombre,
                                        'estado' => 'dispositivo detectado',
                                        'tipo' => 'lpstat-v',
                                        'so' => 'macOS',
                                        'idioma' => 'inglés'
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            
        } 
        // Windows
        elseif (strpos($os, 'win') !== false) {
            // Método 1: Usar wmic para listar impresoras
            $comando = 'wmic printer get name 2>&1';
            exec($comando, $output, $returnCode);
            
            if (!empty($output)) {
                foreach ($output as $linea) {
                    $linea = trim($linea);
                    // Ignorar línea de encabezado y líneas vacías
                    if ($linea && $linea !== 'Name' && strlen($linea) > 1) {
                        $impresoras[] = [
                            'nombre' => $linea,
                            'estado' => 'disponible',
                            'tipo' => 'wmic',
                            'so' => 'Windows'
                        ];
                    }
                }
            }
            
            // Método 2: PowerShell como alternativa
            if (empty($impresoras)) {
                $comandoPS = 'powershell -Command "Get-Printer | Select-Object -ExpandProperty Name"';
                exec($comandoPS, $outputPS, $returnCodePS);
                
                if (!empty($outputPS)) {
                    foreach ($outputPS as $linea) {
                        $linea = trim($linea);
                        if (!empty($linea) && $linea !== 'Name') {
                            $impresoras[] = [
                                'nombre' => $linea,
                                'estado' => 'disponible',
                                'tipo' => 'powershell',
                                'so' => 'Windows'
                            ];
                        }
                    }
                }
            }
        }
        // Linux
        elseif (strpos($os, 'linux') !== false) {
            // Método 1: Usar lpstat
            $comando = "lpstat -p 2>&1";
            exec($comando, $output, $returnCode);
            
            $salida = implode("\n", $output);
            
            if (!empty($salida)) {
                preg_match_all('/printer\s+([^\s]+)/i', $salida, $matches);
                
                if (isset($matches[1]) && !empty($matches[1])) {
                    foreach ($matches[1] as $nombre) {
                        if (!empty($nombre) && $nombre !== 'printer') {
                            $impresoras[] = [
                                'nombre' => $nombre,
                                'estado' => 'disponible',
                                'tipo' => 'lpstat',
                                'so' => 'Linux'
                            ];
                        }
                    }
                }
            }
            
            // Método 2: Listar con lpstat -a
            if (empty($impresoras)) {
                $comando2 = "lpstat -a 2>&1";
                exec($comando2, $output2, $returnCode2);
                $salida2 = implode("\n", $output2);
                
                if (!empty($salida2)) {
                    preg_match_all('/^([^\s]+)\s+accepting/mi', $salida2, $matches2);
                    
                    if (isset($matches2[1]) && !empty($matches2[1])) {
                        foreach ($matches2[1] as $nombre) {
                            if (!empty($nombre)) {
                                $impresoras[] = [
                                    'nombre' => $nombre,
                                    'estado' => 'aceptando trabajos',
                                    'tipo' => 'lpstat-a',
                                    'so' => 'Linux'
                                ];
                            }
                        }
                    }
                }
            }
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'impresoras' => []
        ];
    }
    
    return [
        'success' => true,
        'impresoras' => $impresoras,
        'sistema_operativo' => $os,
        'total' => count($impresoras)
    ];
}

function validarImpresora($nombreImpresora) {
    $resultado = detectarImpresoras();
    
    if (!$resultado['success']) {
        return [
            'success' => false,
            'error' => 'No se pudo detectar impresoras',
            'conectada' => false
        ];
    }
    
    // Buscar impresora específica
    $encontrada = false;
    foreach ($resultado['impresoras'] as $impresora) {
        if ($impresora['nombre'] === $nombreImpresora) {
            $encontrada = true;
            break;
        }
    }
    
    return [
        'success' => true,
        'conectada' => $encontrada,
        'nombre_buscado' => $nombreImpresora,
        'impresoras_disponibles' => $resultado['impresoras'],
        'mensaje' => $encontrada 
            ? "Impresora '$nombreImpresora' está conectada y disponible" 
            : "Impresora '$nombreImpresora' NO fue encontrada",
        'sugerencia' => !$encontrada && count($resultado['impresoras']) > 0
            ? "Impresoras disponibles: " . implode(', ', array_column($resultado['impresoras'], 'nombre'))
            : null
    ];
}

// Procesar petición
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    if (isset($_GET['accion'])) {
        
        switch ($_GET['accion']) {
            case 'listar':
                // Listar todas las impresoras
                $resultado = detectarImpresoras();
                echo json_encode($resultado);
                break;
                
            case 'validar':
                // Validar impresora específica
                if (!isset($_GET['nombre'])) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Nombre de impresora no especificado'
                    ]);
                    exit;
                }
                
                $resultado = validarImpresora($_GET['nombre']);
                echo json_encode($resultado);
                break;
                
            default:
                echo json_encode([
                    'success' => false,
                    'error' => 'Acción no válida'
                ]);
        }
        
    } else {
        // Por defecto, listar impresoras
        $resultado = detectarImpresoras();
        echo json_encode($resultado);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Método HTTP no permitido. Use GET'
    ]);
}
?>
