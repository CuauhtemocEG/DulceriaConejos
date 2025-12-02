<?php
// Script para actualizar TODOS los márgenes de granel (100g, 250g, 500g, 1kg) para todos los productos
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Solo permitir ejecución HTTP
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['margenes']) || !is_array($data['margenes'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parámetro margenes requerido']);
    exit;
}

$margenes = $data['margenes'];

// Validar que tenemos los 4 pesos
$pesosRequeridos = [100, 250, 500, 1000];
foreach ($pesosRequeridos as $peso) {
    if (!isset($margenes[$peso])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Faltan márgenes para peso ' . $peso . 'g']);
        exit;
    }
}

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();
    
    // Obtener todos los productos granel activos
    $stmt = $db->prepare("SELECT id, precio_compra FROM productos WHERE tipo_producto = 'granel' AND activo = 1");
    $stmt->execute();
    $productos = $stmt->fetchAll();
    
    $actualizados = 0;
    
    foreach ($productos as $producto) {
        $productoId = $producto['id'];
        $precioCompra = floatval($producto['precio_compra']);
        
        // Primero calcular el precio de 1kg (necesario para otros cálculos)
        $margen1kg = floatval($margenes[1000]);
        $precioVenta1kg = $precioCompra * (1 + ($margen1kg / 100));
        
        // Calcular y actualizar precio para cada peso
        foreach ($margenes as $peso => $margen) {
            $peso = intval($peso);
            $margen = floatval($margen);
            
            // Calcular precio según el peso usando la lógica correcta
            if ($peso == 1000) {
                // 1kg: aplicar margen directamente al precio de compra
                $precioCalculado = $precioVenta1kg;
            } else {
                // Otros pesos: precio proporcional de 1kg + margen adicional
                $precioBase = ($precioVenta1kg / 1000) * $peso;
                $precioCalculado = $precioBase * (1 + ($margen / 100));
            }
            
            $precioCalculado = round($precioCalculado, 2);
            
            // Actualizar o insertar en precios_granel
            $updateStmt = $db->prepare("
                UPDATE precios_granel 
                SET margen_adicional = ?, precio_calculado = ? 
                WHERE producto_id = ? AND peso_gramos = ?
            ");
            $updateStmt->execute([$margen, $precioCalculado, $productoId, $peso]);
            
            // Si es 1kg, también actualizar la tabla productos
            if ($peso == 1000) {
                $updateProducto = $db->prepare("
                    UPDATE productos 
                    SET margen_ganancia = ?, precio_venta = ? 
                    WHERE id = ?
                ");
                $updateProducto->execute([$margen, $precioCalculado, $productoId]);
            }
        }
        
        $actualizados++;
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Actualizados $actualizados productos granel con los nuevos márgenes",
        'productos_actualizados' => $actualizados
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
