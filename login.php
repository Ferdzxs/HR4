<?php
// HR4 Login System
session_start();
include_once 'rbac.php';
include_once 'config/auth.php';

// Handle login form submission
if ($_POST['login'] ?? false) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $auth = new Auth();
        $user = $auth->authenticate($username, $password);
        
        if ($user) {
            // Create session token
            $session_token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            
            // Store session in database
            $auth->createSession($user['id'], $session_token, $expires_at, $ip_address);
            
            // Store user data in session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'employee_id' => $user['employee_id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'employee_number' => $user['employee_number'],
                'session_token' => $session_token
            ];
            
            // Redirect to dashboard
            header('Location: app.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Please enter both username and password';
    }
}

// If already logged in, redirect to app
if (isset($_SESSION['user'])) {
    header('Location: app.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php include 'shared/styles.php'; ?>
</head>
<body>
    <div class="h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-950">
        <div class="h-full flex items-center justify-center p-6">
            <div class="w-full max-w-md space-y-6 animate-fade-in">
                <div class="text-center">
                    <div class="inline-flex items-center gap-3 text-brand-600 dark:text-brand-400 mb-2">
                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white font-bold text-lg shadow-lg">H4</span>
                        <div class="text-2xl font-bold">HR4</div>
                    </div>
                    <div class="text-sm text-slate-500">Compensation & HR Intelligence</div>
                    <div class="text-lg font-semibold mt-4">Sign In</div>
                </div>
                
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                    <div class="p-6">
                        <?php if (isset($error)): ?>
                            <div class="mb-4 p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="space-y-4">
                            <div>
                                <label for="username" class="block text-sm font-medium mb-2">Username</label>
                                <input type="text" id="username" name="username" required
                                       class="w-full px-3 py-2 border border-[hsl(var(--input))] rounded-md bg-transparent focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))] focus:ring-offset-2"
                                       placeholder="Enter your username">
                            </div>
                            
                            <div>
                                <label for="password" class="block text-sm font-medium mb-2">Password</label>
                                <input type="password" id="password" name="password" required
                                       class="w-full px-3 py-2 border border-[hsl(var(--input))] rounded-md bg-transparent focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))] focus:ring-offset-2"
                                       placeholder="Enter your password">
                            </div>
                            
                            <button type="submit" name="login" value="1"
                                    class="w-full bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] py-2 px-4 rounded-md hover:opacity-95 transition-colors focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))] focus:ring-offset-2">
                                Sign In
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="text-center text-xs text-slate-500">
                    <div class="mb-2">Demo Credentials:</div>
                    <div class="space-y-1 text-left bg-slate-50 dark:bg-slate-800 p-3 rounded-md">
                        <div><strong>HR Manager:</strong> hr.manager / hr123</div>
                        <div><strong>Employee:</strong> employee / emp123</div>
                        <div><strong>Payroll Admin:</strong> payroll.admin / payroll123</div>
                        <div><strong>Benefits Coord:</strong> benefits.coord / benefits123</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'shared/scripts.php'; ?>
</body>
</html>
