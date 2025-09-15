<?php
// HR4 Login System
session_start();
include_once 'rbac.php';
include_once '../config/auth.php';

// Rate limiting configuration
$max_attempts = 5;
$lockout_duration = 60; // 60 seconds
$attempts_key = 'login_attempts_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

// Check rate limiting
$attempts = $_SESSION[$attempts_key] ?? [];
$now = time();
$attempts = array_filter($attempts, function ($timestamp) use ($now, $lockout_duration) {
    return ($now - $timestamp) < $lockout_duration;
});

if (count($attempts) >= $max_attempts) {
    $error = 'Too many failed login attempts. Please try again shortly.';
    $show_form = false;
} else {
    $show_form = true;
}

// Compute remaining lockout time (in seconds) for countdown display
$lockout_remaining = 0;
if (!$show_form) {
    if (!empty($attempts)) {
        $oldest_attempt = min($attempts);
        $lockout_remaining = max(0, $lockout_duration - ($now - $oldest_attempt));
    } else {
        $lockout_remaining = $lockout_duration;
    }
}

// If lockout has expired, clear attempts and allow form immediately
if (!$show_form && $lockout_remaining <= 0) {
    unset($_SESSION[$attempts_key]);
    $show_form = true;
    if (isset($error)) {
        unset($error);
    }
}

// If explicitly redirected with unlocked flag, clear local lock state and show form
if (isset($_GET['unlocked'])) {
    unset($_SESSION[$attempts_key]);
    $show_form = true;
    if (isset($error)) {
        unset($error);
    }
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle login form submission
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $show_form) {
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
                // Ask server for exact remaining lock time to sync countdown
                if (!headers_sent()) {
                    $remaining_from_db = method_exists($auth, 'getLockRemainingSeconds') ? (int) $auth->getLockRemainingSeconds($username) : 60;
                    // Fallback to 60 if invalid
                    if ($remaining_from_db <= 0) {
                        $remaining_from_db = 60;
                    }
                    // Seed session-based countdown with DB-derived remaining seconds
                    $now = time();
                    $_SESSION[$attempts_key] = [$now - ($lockout_duration - min($remaining_from_db, $lockout_duration))];
                }
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
    <link rel="stylesheet" href="/HR4_COMPEN&INTELLI/shared/styles.css">
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('hr4.theme');
                var theme = stored ? JSON.parse(stored) : 'system';
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var isDark = theme === 'dark' || (theme === 'system' && prefersDark);
                if (isDark) { document.documentElement.classList.add('dark'); }
                else { document.documentElement.classList.remove('dark'); }
            } catch (e) { /* noop */ }
        })();
    </script>
    <style>
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
            }

            50% {
                box-shadow: 0 0 40px rgba(59, 130, 246, 0.6);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .floating {
            animation: float 6s ease-in-out infinite;
        }

        .glowing {
            animation: glow 2s ease-in-out infinite;
        }

        .slide-in {
            animation: slideInUp 0.8s ease-out;
        }

        .pulse-animation {
            animation: pulse 2s ease-in-out infinite;
        }

        .glass-effect {
            background: hsl(var(--card) / 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid hsl(var(--border) / 0.5);
        }

        /* Theme-aware background and components */
        .login-bg {
            position: relative;
            background:
                radial-gradient(1200px 800px at 90% -10%, hsl(var(--secondary)) 0%, transparent 60%),
                radial-gradient(1000px 700px at -10% 110%, hsl(var(--accent)) 0%, transparent 60%),
                linear-gradient(to bottom right, hsl(var(--background)), hsl(var(--secondary)));
        }

        .orb {
            filter: blur(0.2px);
            opacity: 0.22;
        }

        .orb-1 {
            background: linear-gradient(to bottom right, hsl(var(--primary)), hsl(var(--accent)));
        }

        .orb-2 {
            background: linear-gradient(to bottom right, hsl(var(--accent)), hsl(var(--primary)));
        }

        .orb-3 {
            background: linear-gradient(to bottom right, hsl(var(--primary)), hsl(var(--muted)));
            opacity: 0.14;
        }

        .btn-primary {
            background-image: linear-gradient(90deg, hsl(var(--primary)), hsl(var(--primary)));
            color: hsl(var(--primary-foreground));
        }

        .btn-primary:hover {
            filter: brightness(0.95);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
    </style>
</head>

<body>
    <div class="h-screen login-bg relative overflow-hidden">
        <!-- Theme Toggle Button -->
        <div class="absolute top-6 right-6 z-20">
            <button id="themeToggle" type="button" data-cycle="binary"
                class="p-3 rounded-xl bg-white/20 dark:bg-slate-800/20 backdrop-blur-sm border border-white/30 dark:border-slate-700/30 hover:bg-white/30 dark:hover:bg-slate-700/30 transition-all duration-200 shadow-lg hover:shadow-xl">
                <span class="sr-only">Theme</span>
            </button>
        </div>

        <!-- Animated background elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full floating orb orb-1"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full floating orb orb-2"
                style="animation-delay: -3s;"></div>
            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 rounded-full floating orb orb-3"
                style="animation-delay: -1.5s;"></div>
        </div>

        <div class="h-full flex items-center justify-center p-6 relative z-10">
            <div class="w-full max-w-md space-y-8 slide-in">
                <!-- Enhanced logo section -->
                <div class="text-center">
                    <div class="inline-flex items-center  text-[hsl(var(--primary))] mb-4">
                        <div class="relative">

                            <div
                                class="absolute inset-0 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 opacity-20 blur-xl">
                            </div>
                        </div>
                        <div
                            class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            HR4
                        </div>
                    </div>
                    <div class="text-sm text-[hsl(var(--muted-foreground))] font-medium">Compensation & HR Intelligence
                    </div>
                    <div class="text-xl font-semibold mt-6 text-[hsl(var(--foreground))]">
                        Welcome Back
                    </div>
                    <div class="text-sm text-[hsl(var(--muted-foreground))] mt-2">Sign in to access your dashboard</div>
                </div>

                <div
                    class="glass-effect rounded-2xl border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-2xl backdrop-blur-xl p-8">
                    <?php if (isset($error)): ?>
                        <div
                            class="mb-4 p-3 rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-light-300 text-sm">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['logged_out'])): ?>
                        <div
                            class="mb-4 p-3 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-black-300 text-sm">
                            You have been successfully logged out.
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['unlocked']) && (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') && !isset($error)): ?>
                        <div
                            class="mb-4 p-3 rounded-md bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-black-300 text-sm">
                            Your account lock has expired. You can try logging in now.
                        </div>
                    <?php endif; ?>

                    <?php if ($show_form): ?>
                        <form method="POST" class="space-y-4" id="loginForm">
                            <input type="hidden" name="csrf_token"
                                value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                            <div class="space-y-2">
                                <label for="username"
                                    class="block text-sm font-semibold text-[hsl(var(--foreground))] mb-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-[hsl(var(--primary))]" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                        Username
                                    </div>
                                </label>
                                <input type="text" id="username" name="username" required
                                    class="w-full px-4 py-3 border border-[hsl(var(--border))] rounded-xl bg-[hsl(var(--input))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))] focus:border-transparent transition-all duration-200 placeholder-[hsl(var(--muted-foreground))]"
                                    placeholder="Enter your username"
                                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                    autocomplete="username">
                            </div>

                            <div class="space-y-2">
                                <label for="password"
                                    class="block text-sm font-semibold text-[hsl(var(--foreground))] mb-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-[hsl(var(--primary))]" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                            </path>
                                        </svg>
                                        Password
                                    </div>
                                </label>
                                <div class="relative">
                                    <input type="password" id="password" name="password" required
                                        class="w-full px-4 py-3 pr-12 border border-[hsl(var(--border))] rounded-xl bg-[hsl(var(--input))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))] focus:border-transparent transition-all duration-200 placeholder-[hsl(var(--muted-foreground))]"
                                        placeholder="Enter your password" autocomplete="current-password">
                                    <button type="button" id="togglePassword"
                                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-[hsl(var(--muted-foreground))] hover:text-[hsl(var(--primary))] transition-colors">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center group">
                                    <input type="checkbox" id="remember_me" name="remember_me"
                                        class="h-4 w-4 text-[hsl(var(--primary))] focus:ring-[hsl(var(--ring))] border-[hsl(var(--border))] rounded transition-colors">
                                    <label for="remember_me"
                                        class="ml-3 block text-sm font-medium text-[hsl(var(--foreground))] group-hover:text-[hsl(var(--primary))] transition-colors cursor-pointer">
                                        Remember me
                                    </label>
                                </div>
                                <a href="../password_reset.php"
                                    class="text-sm font-medium text-[hsl(var(--primary))] hover:text-[hsl(var(--primary))]/80 hover:underline transition-colors">
                                    Forgot password?
                                </a>
                            </div>

                            <button type="submit" name="login" value="1"
                                class="w-full btn-primary py-3 px-6 rounded-xl font-semibold transform hover:scale-[1.02] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))] focus:ring-offset-2 shadow-lg hover:shadow-xl">
                                <span class="flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" id="loadingSpinner"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    <span class="flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                                            </path>
                                        </svg>
                                        Sign In
                                    </span>
                                </span>
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-8" id="lockoutContainer"
                            data-remaining="<?php echo (int) ($lockout_remaining ?: 60); ?>">
                            <div class="text-red-600 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-[hsl(var(--foreground))] mb-2">Account Temporarily
                                Locked</h3>
                            <p class="text-sm text-[hsl(var(--muted-foreground))]">Please wait before attempting to log
                                in again.</p>
                            <p class="mt-2 text-sm text-[hsl(var(--muted-foreground))]">
                                Try again in <span class="font-semibold text-[hsl(var(--primary))]"
                                    id="countdownText">60</span> seconds.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="text-center text-sm text-[hsl(var(--muted-foreground))]">
                    <div class="mb-3 font-medium">Need help accessing your account?</div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-[hsl(var(--primary))]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5z">
                                </path>
                            </svg>
                            Contact your system administrator
                        </div>
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-[hsl(var(--primary))]" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                </path>
                            </svg>
                            or use the password reset feature
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Password toggle functionality
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function () {
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
                loginForm.addEventListener('submit', function () {
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

            // Lockout countdown handling
            const lockoutContainer = document.getElementById('lockoutContainer');
            if (lockoutContainer) {
                let remaining = parseInt(lockoutContainer.getAttribute('data-remaining') || '60', 10);
                const countdownText = document.getElementById('countdownText');
                if (Number.isNaN(remaining) || remaining < 0) remaining = 60;

                function tick() {
                    if (countdownText) countdownText.textContent = String(remaining);
                    if (remaining <= 0) {
                        // Redirect to show the unlocked state and refocus the login form
                        const url = new URL(window.location.href);
                        url.searchParams.set('unlocked', '1');
                        window.location.replace(url.toString());
                        return;
                    }
                    remaining -= 1;
                }

                tick();
                setInterval(tick, 1000);
            }
            // If unlocked flag is present, focus the username field for convenience
            const params = new URLSearchParams(window.location.search);
            if (params.has('unlocked')) {
                const usernameEl = document.getElementById('username');
                if (usernameEl) usernameEl.focus();
            }
        });
    </script>
</body>

</html>