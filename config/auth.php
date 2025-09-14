<?php
// User authentication class
require_once 'database.php';

class Auth {
    private $conn;
    private $table_name = "users";
    private $is_pdo = false;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->is_pdo = ($this->conn instanceof PDO);
    }

    public function authenticate($username, $password) {
        $query = "SELECT u.id, u.username, u.password_hash, r.role_name, e.id as employee_id, 
                         e.first_name, e.last_name, e.employee_number
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.role_id = r.id
                  LEFT JOIN employees e ON u.employee_id = e.id
                  WHERE u.username = ?";

        if ($this->is_pdo) {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $username);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if(password_verify($password, $row['password_hash'])) {
                    return [
                        'id' => $row['id'],
                        'username' => $row['username'],
                        'role' => $row['role_name'],
                        'employee_id' => $row['employee_id'],
                        'first_name' => $row['first_name'],
                        'last_name' => $row['last_name'],
                        'employee_number' => $row['employee_number']
                    ];
                }
            }
        } else {
            // MySQLi
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                // Verify password
                if(password_verify($password, $row['password_hash'])) {
                    return [
                        'id' => $row['id'],
                        'username' => $row['username'],
                        'role' => $row['role_name'],
                        'employee_id' => $row['employee_id'],
                        'first_name' => $row['first_name'],
                        'last_name' => $row['last_name'],
                        'employee_number' => $row['employee_number']
                    ];
                }
            }
        }
        
        return false;
    }

    public function getUserById($user_id) {
        $query = "SELECT u.id, u.username, r.role_name, e.id as employee_id, 
                         e.first_name, e.last_name, e.employee_number
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.role_id = r.id
                  LEFT JOIN employees e ON u.employee_id = e.id
                  WHERE u.id = ?";

        if ($this->is_pdo) {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user_id);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } else {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
        }
        
        return false;
    }

    public function createSession($user_id, $session_token, $expires_at, $ip_address) {
        $query = "INSERT INTO user_sessions (user_id, session_token, expires_at, ip_address) 
                  VALUES (?, ?, ?, ?)";

        if ($this->is_pdo) {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $user_id);
            $stmt->bindParam(2, $session_token);
            $stmt->bindParam(3, $expires_at);
            $stmt->bindParam(4, $ip_address);
            return $stmt->execute();
        } else {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("isss", $user_id, $session_token, $expires_at, $ip_address);
            return $stmt->execute();
        }
    }

    public function validateSession($session_token) {
        $query = "SELECT u.id, u.username, r.role_name, e.id as employee_id, 
                         e.first_name, e.last_name, e.employee_number
                  FROM user_sessions s
                  JOIN " . $this->table_name . " u ON s.user_id = u.id
                  LEFT JOIN roles r ON u.role_id = r.id
                  LEFT JOIN employees e ON u.employee_id = e.id
                  WHERE s.session_token = ? 
                  AND s.expires_at > NOW()";

        if ($this->is_pdo) {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $session_token);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } else {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $session_token);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
        }
        
        return false;
    }

    public function destroySession($session_token) {
        $query = "DELETE FROM user_sessions WHERE session_token = ?";
        
        if ($this->is_pdo) {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $session_token);
            return $stmt->execute();
        } else {
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $session_token);
            return $stmt->execute();
        }
    }
}
?>
