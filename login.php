<?php
// HR4 Login System
session_start();
include_once 'rbac.php';
include_once 'config/auth.php';

// Rate limiting configuration
$max_attempts = 5;
$lockout_duration = 300; // 5 minutes
$attempts_key = 'login_attempts_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

// Check rate limiting
$attempts = $_SESSION[$attempts_key] ?? [];
$now = time();
$attempts = array_filter($attempts, function($timestamp) use ($now, $lockout_duration) {
    return ($now - $timestamp) < $lockout_duration;
});

if (count($attempts) >= $max_attempts) {
    $error = 'Too many failed login attempts. Please try again in ' . ceil($lockout_duration / 60) . ' minutes.';
    $show_form = false;
} else {
    $show_form = true;
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle login form submission
if ($_POST['login'] ?? false && $show_form) {
    // Validate CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember_me = isset($_POST['remember_me']);
        
        if (!empty($username) && !empty($password)) {
            $auth = new Auth();
            
            // Check if account is locked
            if ($auth->isAccountLocked($username)) {
                $error = 'Account is temporarily locked due to too many failed attempts. Please try again later.';
                $show_form = false;
            } else {
                $user = $auth->authenticate($username, $password);
                
                if ($user) {
                    // Clear failed attempts on successful login
                    unset($_SESSION[$attempts_key]);
                    $auth->clearFailedAttempts($username);
                    
                    // Create session token
                    $session_token = bin2hex(random_bytes(32));
                    $expires_at = $remember_me ? 
                        date('Y-m-d H:i:s', strtotime('+30 days')) : 
                        date('Y-m-d H:i:s', strtotime('+24 hours'));
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
                    
                    // Set remember me cookie if requested
                    if ($remember_me) {
                        setcookie('hr4_remember_token', $session_token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
                    }
                    
                    // Redirect to dashboard
                    header('Location: app.php');
                    exit;
                } else {
                    // Record failed attempt in database
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                    $auth->recordFailedAttempt($username, $ip_address);
                    
                    // Record failed attempt in session
                    $attempts[] = $now;
                    $_SESSION[$attempts_key] = $attempts;
                    
                    $remaining_attempts = $max_attempts - count($attempts);
                    if ($remaining_attempts > 0) {
                        $error = "Invalid username or password. {$remaining_attempts} attempts remaining.";
                    } else {
                        $error = 'Account temporarily locked due to too many failed attempts.';
                        $show_form = false;
                    }
                }
            }
        } else {
            $error = 'Please enter both username and password';
        }
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
                        
                        <?php if (isset($_GET['logged_out'])): ?>
                            <div class="mb-4 p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">
                                You have been successfully logged out.
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($show_form): ?>
                        <form method="POST" class="space-y-4" id="loginForm">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            
                            <div>
                                <label for="username" class="block text-sm font-medium mb-2">Username</label>
                                <input type="text" id="username" name="username" required
                                       class="w-full px-3 py-2 border border-[hsl(var(--input))] rounded-md bg-transparent focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))] focus:ring-offset-2 transition-colors"
                                       placeholder="Enter your username"
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                       autocomplete="username">
                            </div>
                            
                            <div>
                                <label for="password" class="block text-sm font-medium mb-2">Password</label>
                                <div class="relative">
                                    <input type="password" id="password" name="password" required
                                           class="w-full px-3 py-2 pr-10 border border-[hsl(var(--input))] rounded-md bg-transparent focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))] focus:ring-offset-2 transition-colors"
                                           placeholder="Enter your password"
                                           autocomplete="current-password">
                                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input type="checkbox" id="remember_me" name="remember_me" 
                                           class="h-4 w-4 text-[hsl(var(--primary))] focus:ring-[hsl(var(--ring))] border-[hsl(var(--input))] rounded">
                                    <label for="remember_me" class="ml-2 block text-sm text-[hsl(var(--muted-foreground))]">
                                        Remember me
                                    </label>
                                </div>
                                <a href="password_reset.php" class="text-sm text-[hsl(var(--primary))] hover:underline">
                                    Forgot password?
                                </a>
                            </div>
                            
                            <button type="submit" name="login" value="1"
                                    class="w-full bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] py-2 px-4 rounded-md hover:opacity-95 transition-colors focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))] focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" id="loadingSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Sign In
                                </span>
                            </button>
                        </form>
                        <?php else: ?>
                        <div class="text-center py-8">
                            <div class="text-red-600 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-[hsl(var(--foreground))] mb-2">Account Temporarily Locked</h3>
                            <p class="text-sm text-[hsl(var(--muted-foreground))]">Please wait before attempting to log in again.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="text-center text-xs text-slate-500">
                    <div class="mb-2">Need help accessing your account?</div>
                    <div class="space-y-1">
                        <div>Contact your system administrator for assistance</div>
                        <div>or use the password reset feature if available</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'shared/scripts.php'; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password toggle functionality
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle icon
                const icon = this.querySelector('svg');
                if (type === 'text') {
                    icon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                    `;
                } else {
                    icon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    `;
                }
            });
        }
        
        // Form submission with loading state
        const loginForm = document.getElementById('loginForm');
        const submitButton = loginForm?.querySelector('button[type="submit"]');
        const loadingSpinner = document.getElementById('loadingSpinner');
        
        if (loginForm && submitButton) {
            loginForm.addEventListener('submit', function() {
                // Show loading state
                submitButton.disabled = true;
                if (loadingSpinner) {
                    loadingSpinner.classList.remove('hidden');
                }
                submitButton.querySelector('span').textContent = 'Signing in...';
            });
        }
        
        // Real-time validation
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        
        function validateForm() {
            const username = usernameInput?.value.trim();
            const password = passwordInput?.value;
            
            if (submitButton) {
                if (username && password) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        }
        
        if (usernameInput) {
            usernameInput.addEventListener('input', validateForm);
        }
        if (passwordInput) {
            passwordInput.addEventListener('input', validateForm);
        }
        
        // Initial validation
        validateForm();
    });
    </script>
</body>
</html>
