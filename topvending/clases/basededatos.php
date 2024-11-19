<?php
declare(strict_types=1);
class database{
private static $dbh = null;
private $host = "localhost";
private $password = "root";
private $username = "root";
private $port = 3306;
private $dbname = 'maquinas_expendedoras';


private function __construct() {
    try {
        // Creamos una nueva conexión PDO
        self::$dbh = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->password);
        self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Configura el modo de errores
        
    } catch (PDOException $e) {
        // Si hay un error, lo manejamos aquí
        echo "Error de conexión: " . $e->getMessage();
    }
}

    // Método estático para obtener la única instancia de la conexión
  

    public static function getInstance(): PDO{
        if(!isset(self::$dbh)){
            new self();
        }
        return self::$dbh;
        
    }

    public static function CloseConn(): bool{
        self::$dbh = null;
        return true;
    }
}
?>