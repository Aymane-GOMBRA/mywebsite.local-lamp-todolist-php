<?php
class DBConnection {
    private $host = 'localhost';
    private $database = 'tdl';
    private $username = 'root';
    private $password = '123456';
    private $connection;

    public function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database}";
            $this->connection = new PDO($dsn, $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
            die();
        }
    }

    public function getConnection() {
        return $this->connection;
    }
    public function disconnect() {
        $this->connection = null;
    }
}
?>
