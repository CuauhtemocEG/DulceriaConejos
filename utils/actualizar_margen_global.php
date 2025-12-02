<?php
// Script para actualizar el margen_ganancia y precio_venta de todos los productos de un tipo
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Permitir ejecución por CLI o HTTP
if (php_sapi_name() === 'cli') {
    // Modo CLI
    if ($argc < 3) {
        echo "Uso: php actualizar_margen_global.php <tipo_producto> <nuevo_margen>\n";
        echo "Ejemplo: php actualizar_margen_global.php anaquel 35\n";
        exit(1);
    }
    $tipo = $argv[1];
    $nuevoMargen = floatval($argv[2]);
} else {
    // Modo HTTP
    header('Content-Type: text/plain; charset=utf-8');
    
    if (!isset($_GET['tipo']) || !isset($_GET['margen'])) {
        http_response_code(400);
        echo 'Error: parámetros tipo y margen requeridos';
        exit;
    }
    
    $tipo = $_GET['tipo'];
    $nuevoMargen = floatval($_GET['margen']);
}

$db = Database::getInstance()->getConnection();

try {
    if ($tipo === 'granel') {
        // Para granel, actualiza el margen_ganancia principal (1kg)
        // Y también actualiza los precios en la tabla precios_granel
        $stmt = $db->prepare("SELECT id, precio_compra FROM productos WHERE tipo_producto = 'granel' AND activo = 1");
        $stmt->execute();
        $productos = $stmt->fetchAll();
        
        foreach ($productos as $p) {
            // Actualizar margen_ganancia y precio_venta principal (1kg)
            $precioVenta = $p['precio_compra'] * (1 + ($nuevoMargen / 100));
            $update = $db->prepare("UPDATE productos SET margen_ganancia = ?, precio_venta = ? WHERE id = ?");
            $update->execute([$nuevoMargen, $precioVenta, $p['id']]);
            
            // Actualizar precio de 1kg en precios_granel
            $update1kg = $db->prepare("UPDATE precios_granel SET margen_adicional = ?, precio_calculado = ? WHERE producto_id = ? AND peso_gramos = 1000");
            $update1kg->execute([$nuevoMargen, $precioVenta, $p['id']]);
        }
        
        echo "✓ Actualizados " . count($productos) . " productos granel con margen de " . $nuevoMargen . "%\n";
    } else {
        // anaquel o pieza
        $stmt = $db->prepare("SELECT id, precio_compra FROM productos WHERE tipo_producto = ? AND activo = 1");
        $stmt->execute([$tipo]);
        $productos = $stmt->fetchAll();
        
        foreach ($productos as $p) {
            $precioVenta = $p['precio_compra'] * (1 + ($nuevoMargen / 100));
            $update = $db->prepare("UPDATE productos SET margen_ganancia = ?, precio_venta = ? WHERE id = ?");
            $update->execute([$nuevoMargen, $precioVenta, $p['id']]);
        }
        
        echo "✓ Actualizados " . count($productos) . " productos tipo $tipo con margen de " . $nuevoMargen . "%\n";
    }
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
