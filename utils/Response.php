<?php
/**
 * Utilidades de respuesta HTTP
 */
class Response {
    
    /**
     * Enviar respuesta JSON exitosa
     */
    public static function success($data = null, $message = 'Operación exitosa', $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Enviar respuesta JSON de error
     */
    public static function error($message = 'Error en la operación', $statusCode = 400, $errors = null) {
        http_response_code($statusCode);
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Enviar respuesta no autorizado
     */
    public static function unauthorized($message = 'No autorizado') {
        self::error($message, 401);
    }
    
    /**
     * Enviar respuesta prohibido
     */
    public static function forbidden($message = 'No tienes permisos para realizar esta acción') {
        self::error($message, 403);
    }
    
    /**
     * Enviar respuesta no encontrado
     */
    public static function notFound($message = 'Recurso no encontrado') {
        self::error($message, 404);
    }
    
    /**
     * Enviar respuesta de validación
     */
    public static function validationError($errors, $message = 'Errores de validación') {
        self::error($message, 422, $errors);
    }
}
