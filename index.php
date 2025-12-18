<?php
/**
 * Router principal del sistema
 */

require_once __DIR__ . '/config/config.php';

// Definir rutas
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/DulceriaConejos', '', $uri);

// Remover query string si existe
$uri = strtok($uri, '?');

// Rutas públicas (sin autenticación)
$publicRoutes = [
    '/' => 'pages/login.php',
    '/login' => 'pages/login.php'
];

// Rutas protegidas (requieren autenticación)
$protectedRoutes = [
    '/dashboard' => 'pages/dashboard.php',
    '/productos' => 'pages/productos.php',
    '/usuarios' => 'pages/usuarios.php',
    '/pos' => 'pages/pos.php',
    '/ventas' => 'pages/ventas.php',
    '/reportes' => 'pages/reportes.php',
    '/temporadas' => 'pages/temporadas.php',
    '/inventario' => 'pages/inventario.php'
];

// Verificar si es una ruta pública
if (array_key_exists($uri, $publicRoutes)) {
    require $publicRoutes[$uri];
    exit();
}

// Verificar si es una ruta protegida
if (array_key_exists($uri, $protectedRoutes)) {
    // Verificar autenticación mediante sesión
    session_start();
    
    if (!isset($_SESSION['user_token'])) {
        header('Location: /DulceriaConejos/login');
        exit();
    }
    
    require $protectedRoutes[$uri];
    exit();
}

// Ruta no encontrada
http_response_code(404);
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>404 - Página no encontrada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f5f5f5;
        }
        h1 { color: #333; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>404 - Página no encontrada</h1>
    <p>La página que buscas no existe.</p>
    <a href='/DulceriaConejos/'>Volver al inicio</a>
</body>
</html>";
