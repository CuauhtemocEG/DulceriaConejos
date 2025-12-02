<?php
/**
 * Clase para manejo de JSON Web Tokens
 */
class JWT {
    
    /**
     * Codificar datos en Base64URL
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Decodificar datos de Base64URL
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
    
    /**
     * Crear un nuevo token JWT
     */
    public static function encode($payload, $secret = JWT_SECRET_KEY) {
        // Header
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);
        
        // Agregar tiempo de expiración al payload
        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_EXPIRATION_TIME;
        
        // Codificar header y payload
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));
        
        // Crear firma
        $signature = hash_hmac(
            'sha256',
            $base64UrlHeader . "." . $base64UrlPayload,
            $secret,
            true
        );
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        // Crear token
        $token = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        
        return $token;
    }
    
    /**
     * Decodificar y validar un token JWT
     */
    public static function decode($token, $secret = JWT_SECRET_KEY) {
        // Dividir el token
        $tokenParts = explode('.', $token);
        
        if (count($tokenParts) !== 3) {
            throw new Exception('Token inválido');
        }
        
        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $tokenParts;
        
        // Verificar firma
        $signature = self::base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac(
            'sha256',
            $base64UrlHeader . "." . $base64UrlPayload,
            $secret,
            true
        );
        
        if (!hash_equals($signature, $expectedSignature)) {
            throw new Exception('Firma del token inválida');
        }
        
        // Decodificar payload
        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);
        
        // Verificar expiración
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token expirado');
        }
        
        return $payload;
    }
    
    /**
     * Verificar si un token es válido
     */
    public static function verify($token, $secret = JWT_SECRET_KEY) {
        try {
            self::decode($token, $secret);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtener token del header Authorization
     */
    public static function getBearerToken() {
        $headers = null;
        
        // Intentar obtener de diferentes fuentes (Apache puede ponerlo en diferentes lugares)
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            // Apache con mod_rewrite a veces pone el header aquí
            $headers = trim($_SERVER["REDIRECT_HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        // Si aún no lo encontramos, intentar getallheaders()
        if (empty($headers) && function_exists('getallheaders')) {
            $allHeaders = getallheaders();
            if (isset($allHeaders['Authorization'])) {
                $headers = trim($allHeaders['Authorization']);
            } elseif (isset($allHeaders['authorization'])) {
                $headers = trim($allHeaders['authorization']);
            }
        }
        
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
}
