<?php
/**
 * Authentication System
 * HR4 Compensation & Intelligence System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function login($username, $password)
    {
        try {
            // First, get the user with their stored password hash
            $stmt = $this->db->prepare("
                SELECT u.*, r.role_name, e.first_name, e.last_name, e.employee_number
                FROM users u
                JOIN roles r ON u.role_id = r.id
                LEFT JOIN employees e ON u.employee_id = e.id
                WHERE u.username = ?
            ");
            $stmt->execute([$username]);

            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role_name'];
                $_SESSION['employee_id'] = $user['employee_id'];
                $_SESSION['employee_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['employee_number'] = $user['employee_number'];

                return true;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function logout()
    {
        session_destroy();
        return true;
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser()
    {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role'],
                'employee_id' => $_SESSION['employee_id'],
                'employee_name' => $_SESSION['employee_name'],
                'employee_number' => $_SESSION['employee_number']
            ];
        }
        return null;
    }

    public function hasRole($role)
    {
        return $this->isLoggedIn() && $_SESSION['role'] === $role;
    }

    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            header('Location: ../index.php');
            exit;
        }
    }

    public function requireRole($role)
    {
        $this->requireLogin();
        if (!$this->hasRole($role)) {
            header('Location: ../index.php?error=access_denied');
            exit;
        }
    }
}
?>