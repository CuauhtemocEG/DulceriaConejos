<?php
/**
 * API para subir archivos (imágenes de productos)
 */

require_once __DIR__ . '/../config/config.php';

try {
    // Verificar autenticación
    $auth = new AuthMiddleware();
    $user = $auth->authenticate();
    
    // Verificar permisos (crear/editar productos)
    $auth->requirePermission($user, 'productos', 'crear');
    
    // Solo permitir POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Response::error('Método no permitido', 405);
    }
    
    // Verificar que se haya subido un archivo
    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        Response::error('No se recibió ninguna imagen o hubo un error en la subida', 400);
    }
    
    $file = $_FILES['imagen'];
    
    // Validar tipo de archivo
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        Response::error('Tipo de archivo no permitido. Solo JPG, PNG, GIF, WEBP', 400);
    }
    
    // Validar tamaño (máximo 2MB)
    $maxSize = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $maxSize) {
        Response::error('La imagen no debe superar 2MB', 400);
    }
    
    // Generar nombre único para el archivo
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('producto_') . '_' . time() . '.' . $extension;
    
    // Ruta de destino
    $uploadDir = __DIR__ . '/../public/img/productos/';
    $uploadPath = $uploadDir . $fileName;
    
    // Crear directorio si no existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Mover archivo
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        Response::error('Error al guardar la imagen', 500);
    }
    
    // Redimensionar imagen si es muy grande (opcional)
    redimensionarImagen($uploadPath, 800, 800);
    
    // Retornar URL relativa
    $imageUrl = '/DulceriaConejos/public/img/productos/' . $fileName;
    
    Response::success([
        'imagen_url' => $imageUrl,
        'nombre_archivo' => $fileName
    ], 'Imagen subida correctamente');
    
} catch (Exception $e) {
    Response::error('Error al procesar la imagen: ' . $e->getMessage(), 500);
}

/**
 * Redimensionar imagen manteniendo aspect ratio
 */
function redimensionarImagen($filePath, $maxWidth, $maxHeight) {
    try {
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            return false;
        }
        
        list($width, $height, $type) = $imageInfo;
        
        // Si la imagen ya es pequeña, no hacer nada
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return true;
        }
        
        // Calcular nuevas dimensiones
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);
        
        // Crear imagen desde archivo
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filePath);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($filePath);
                break;
            default:
                return false;
        }
        
        // Crear imagen de destino
        $destination = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preservar transparencia para PNG y GIF
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Redimensionar
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Guardar imagen redimensionada
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($destination, $filePath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($destination, $filePath, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($destination, $filePath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($destination, $filePath, 85);
                break;
        }
        
        // Liberar memoria
        imagedestroy($source);
        imagedestroy($destination);
        
        return true;
        
    } catch (Exception $e) {
        return false;
    }
}
