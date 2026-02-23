<?php
// config/db.php

class Conexion
{
    // Credenciales exactas proporcionadas por IONOS
    private $host = 'db5019859242.hosting-data.io';
    private $db_name = 'dbs15352652';
    private $username = 'dbu3761605';
    private $password = 'Espinoza12@@'; // <-- ¡Cambia esto por la contraseña que le pusiste a la BD!
    private $conn;

    // Método para establecer la conexión
    public function conectar()
    {
        $this->conn = null;

        try {
            // DSN - Mantenemos el charset utf8mb4 para evitar problemas con acentos y la "ñ"
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";

            // Opciones de blindaje y rendimiento
            $opciones = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            // Instanciamos la conexión usando las variables de la clase
            $this->conn = new PDO($dsn, $this->username, $this->password, $opciones);

        } catch (PDOException $e) {
            // Guardamos el error real en el log y mostramos un mensaje genérico
            error_log("Error de conexión a BD IONOS: " . $e->getMessage());
            die("Error crítico de sistema: No se pudo conectar a la base de datos.");
        }

        return $this->conn;
    }
}
?>