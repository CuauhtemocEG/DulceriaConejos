<?php
/**
 * Configuración general del sistema
 */

// Configuración de la base de datos
// MAMP usa puerto 8889 para MySQL por defecto
define('DB_HOST', 'localhost:3306');
define('DB_NAME', 'dulceria_pos');
define('DB_USER', 'root');
define('DB_PASS', 'root'); // Cambia esto según tu configuración de MAMP

// Configuración de JWT
define('JWT_SECRET_KEY', 'tu_clave_secreta_muy_segura_cambiar_en_produccion');
define('JWT_EXPIRATION_TIME', 604800); // 7 días en segundos (7 * 24 * 60 * 60)

// Configuración del negocio para tickets
define('NEGOCIO_NOMBRE', 'Dulcería Conejos');
define('NEGOCIO_DIRECCION', 'Plaza 87 Local 2, Camino Real a Momoxpan 1109, Santiago Momoxpan,72776 Cholula de Rivadavia, Pue.');
define('NEGOCIO_SUCURSAL', 'Momoxpan');
define('NEGOCIO_LOGO', '/DulceriaConejos/public/img/DulceriaConejos.png');

// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de errores
if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] === 'localhost') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Configuración de headers para API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS

// Rutas del sistema
define('BASE_PATH', dirname(__DIR__));
define('API_PATH', BASE_PATH . '/api');
define('PAGES_PATH', BASE_PATH . '/pages');
define('MODELS_PATH', BASE_PATH . '/models');
define('UTILS_PATH', BASE_PATH . '/utils');
define('MIDDLEWARE_PATH', BASE_PATH . '/middleware');

// URL base del sistema
define('BASE_URL', 'http://localhost/DulceriaConejos');
define('API_URL', BASE_URL . '/api');

// Configuración de ticket
define('NOMBRE_NEGOCIO', 'Dulcería El Sabor');
define('DIRECCION_NEGOCIO', 'Calle Principal #123, Col. Centro');
define('TELEFONO_NEGOCIO', '(555) 123-4567');
define('RFC_NEGOCIO', 'ABC123456XYZ');

// Autoload de clases
spl_autoload_register(function ($class) {
    $paths = [
        MODELS_PATH . '/' . $class . '.php',
        UTILS_PATH . '/' . $class . '.php',
        MIDDLEWARE_PATH . '/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});
