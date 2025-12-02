<?php
/**
 * Clase Database - Singleton para manejo de conexión a la base de datos
 */

class Database {
    private static $instance = null;
    private $connection = null;
    
    /**
     * Constructor privado para patrón Singleton
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            throw new Exception("Error al conectar con la base de datos");
        }
    }
    
    /**
     * Prevenir clonación del objeto
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialización
     */
    public function __wakeup() {
        throw new Exception("No se puede deserializar un singleton");
    }
    
    /**
     * Obtener instancia única de la clase
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Obtener la conexión PDO
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Cerrar la conexión
     */
    public function closeConnection() {
        $this->connection = null;
    }
    
    /**
     * Comenzar una transacción
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Confirmar una transacción
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Revertir una transacción
     */
    public function rollBack() {
        return $this->connection->rollBack();
    }
    
    /**
     * Verificar si hay una transacción activa
     * 
     * @return bool
     */
    public function inTransaction() {
        return $this->connection->inTransaction();
    }
    
    /**
     * Obtener el último ID insertado
     * 
     * @return string
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}
