<?php
// Database configuration for HR4 system
class Database
{
    private $host = 'localhost';
    private $db_name = 'hr4_compensation_intelli';
    private $username = 'root';
    private $password = '54321';
    private $conn;

    public function getConnection()
    {
        $this->conn = null;

        // Try PDO first, fallback to MySQLi
        if (extension_loaded('pdo_mysql')) {
            try {
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $this->conn;
            } catch (PDOException $exception) {
                echo "PDO Connection error: " . $exception->getMessage();
            }
        }

        // Fallback to MySQLi
        if (extension_loaded('mysqli')) {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);

            if ($this->conn->connect_error) {
                echo "MySQLi Connection error: " . $this->conn->connect_error;
                return null;
            }

            $this->conn->set_charset("utf8");
            return $this->conn;
        }

        echo "No MySQL driver available. Please enable PDO_MySQL or MySQLi extension.";
        return null;
    }
}

// Create global PDO connection
try {
    $database = new Database();
    $pdo = $database->getConnection();

    if (!$pdo) {
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?>