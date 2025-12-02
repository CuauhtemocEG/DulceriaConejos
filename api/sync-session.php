<?php
/**
 * Endpoint para sincronizar el token JWT con la sesión PHP
 * Esto permite proteger las páginas PHP directamente
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Establecer sesión autenticada
    try {
        // Obtener el token del header
        $headers = getallheaders();
        $token = null;
        
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }
        
        if (!$token) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Token no proporcionado'
            ]);
            exit;
        }
        
        // Validar el token usando AuthMiddleware
        $auth = new AuthMiddleware();
        $user = $auth->authenticate();
        
        if ($user) {
            // Token válido, establecer sesión PHP
            $_SESSION['authenticated'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_rol'] = $user['rol'];
            $_SESSION['user_token'] = $token;
            
            echo json_encode([
                'success' => true,
                'message' => 'Sesión PHP sincronizada correctamente',
                'data' => [
                    'session_id' => session_id(),
                    'user_id' => $user['id']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Token inválido'
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al sincronizar sesión: ' . $e->getMessage()
        ]);
    }
    
} elseif ($method === 'DELETE') {
    // Destruir sesión (logout)
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Sesión PHP cerrada correctamente'
    ]);
    
} elseif ($method === 'GET') {
    // Verificar estado de la sesión
    if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
        echo json_encode([
            'success' => true,
            'message' => 'Sesión activa',
            'data' => [
                'authenticated' => true,
                'user_id' => $_SESSION['user_id'] ?? null,
                'session_id' => session_id()
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No hay sesión activa',
            'data' => [
                'authenticated' => false
            ]
        ]);
    }
    
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
