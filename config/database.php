<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'courses';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Log error but don't echo it (would break JSON response)
            error_log("Database Connection Error: " . $exception->getMessage());
            $this->conn = null;
        }
        return $this->conn;
    }
}
?>