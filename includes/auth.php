<?php
require_once __DIR__ . '/../config/database.php';

class Auth
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function login($username, $password, $role, $rememberMe = false)
    {
        try {
            // Validate input
            if (empty($username) || empty($password) || empty($role)) {
                return ['ok' => false, 'message' => 'All fields are required'];
            }

            if (strlen($username) < 3) {
                return ['ok' => false, 'message' => 'Username must be at least 3 characters'];
            }

            if (strlen($password) < 6) {
                return ['ok' => false, 'message' => 'Password must be at least 6 characters'];
            }

            // Get user from database
            $stmt = $this->pdo->prepare("
                SELECT u.*, r.role_name, e.first_name, e.last_name, e.employee_number
                FROM users u
                JOIN roles r ON u.role_id = r.id
                LEFT JOIN employees e ON u.employee_id = e.id
                WHERE u.username = ? AND r.role_name = ?
            ");
            $stmt->execute([$username, $role]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['ok' => false, 'message' => 'Invalid username or password'];
            }

            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                return ['ok' => false, 'message' => 'Invalid username or password'];
            }

            // Create session
            $sessionToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+8 hours')); // Default 8 hours

            // Store session in database
            $stmt = $this->pdo->prepare("
                INSERT INTO user_sessions (user_id, session_token, expires_at, ip_address)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $user['id'],
                $sessionToken,
                $expiresAt,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role_name'];
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['session_token'] = $sessionToken;
            $_SESSION['remember_me'] = $rememberMe;

            // Set cookie if remember me is checked
            if ($rememberMe) {
                setcookie('hr4_remember_token', $sessionToken, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            }

            // Log login activity
            $this->logActivity($user['id'], 'LOGIN', 'users', null, ['login_time' => date('Y-m-d H:i:s')]);

            return [
                'ok' => true,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role_name'],
                    'employee_id' => $user['employee_id'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'employee_number' => $user['employee_number']
                ]
            ];

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['ok' => false, 'message' => 'An error occurred during login'];
        }
    }

    public function logout()
    {
        try {
            if (isset($_SESSION['user_id']) && isset($_SESSION['session_token'])) {
                // Remove session from database
                $stmt = $this->pdo->prepare("
                    DELETE FROM user_sessions 
                    WHERE user_id = ? AND session_token = ?
                ");
                $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);

                // Log logout activity
                $this->logActivity($_SESSION['user_id'], 'LOGOUT', 'users', null, ['logout_time' => date('Y-m-d H:i:s')]);
            }

            // Clear session
            session_unset();
            session_destroy();

            // Clear remember me cookie
            if (isset($_COOKIE['hr4_remember_token'])) {
                setcookie('hr4_remember_token', '', time() - 3600, '/', '', false, true);
            }

            return ['ok' => true];

        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return ['ok' => false, 'message' => 'An error occurred during logout'];
        }
    }

    public function isLoggedIn()
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
            return false;
        }

        try {
            // Check if session exists and is valid
            $stmt = $this->pdo->prepare("
                SELECT us.*, u.username, r.role_name
                FROM user_sessions us
                JOIN users u ON us.user_id = u.id
                JOIN roles r ON u.role_id = r.id
                WHERE us.user_id = ? AND us.session_token = ? AND us.expires_at > NOW()
            ");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);
            $session = $stmt->fetch();

            if (!$session) {
                $this->logout();
                return false;
            }

            // Update last activity
            $stmt = $this->pdo->prepare("
                UPDATE user_sessions 
                SET expires_at = DATE_ADD(NOW(), INTERVAL 8 HOUR)
                WHERE user_id = ? AND session_token = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);

            return true;

        } catch (Exception $e) {
            error_log("Session check error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserInfo()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT u.*, r.role_name, e.first_name, e.last_name, e.employee_number, d.department_name
                FROM users u
                JOIN roles r ON u.role_id = r.id
                LEFT JOIN employees e ON u.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE u.id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();

        } catch (Exception $e) {
            error_log("Get user info error: " . $e->getMessage());
            return null;
        }
    }

    public function hasPermission($module)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT permissions FROM roles r
                JOIN users u ON r.id = u.role_id
                WHERE u.id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $role = $stmt->fetch();

            if (!$role) {
                return false;
            }

            $permissions = json_decode($role['permissions'], true);
            return isset($permissions['modules']) && in_array($module, $permissions['modules']);

        } catch (Exception $e) {
            error_log("Permission check error: " . $e->getMessage());
            return false;
        }
    }

    private function logActivity($userId, $actionType, $tableAffected, $oldValues = null, $newValues = null)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO audit_logs (user_id, action_type, table_affected, old_values, new_values)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $actionType,
                $tableAffected,
                $oldValues ? json_encode($oldValues) : null,
                $newValues ? json_encode($newValues) : null
            ]);
        } catch (Exception $e) {
            error_log("Activity logging error: " . $e->getMessage());
        }
    }

    public function getLoginHistory($limit = 10)
    {
        if (!$this->isLoggedIn()) {
            return [];
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT al.*, u.username, r.role_name
                FROM audit_logs al
                JOIN users u ON al.user_id = u.id
                JOIN roles r ON u.role_id = r.id
                WHERE al.action_type = 'LOGIN' AND al.user_id = ?
                ORDER BY al.timestamp DESC
                LIMIT ?
            ");
            $stmt->execute([$_SESSION['user_id'], $limit]);
            return $stmt->fetchAll();

        } catch (Exception $e) {
            error_log("Get login history error: " . $e->getMessage());
            return [];
        }
    }
}

// Initialize auth instance
$auth = new Auth($pdo);
?>
