<?php
// HR4 Password Reset Request
session_start();
include_once 'config/auth.php';

$message = '';
$error = '';

// Handle password reset request
if ($_POST['reset_request'] ?? false) {
    $email = trim($_POST['email'] ?? '');

    if (!empty($email)) {
        // In a real application, you would:
        // 1. Validate email format
        // 2. Check if email exists in database
        // 3. Generate secure reset token
        // 4. Store token in database with expiration
        // 5. Send email with reset link

        // For now, just show a message
        $message = 'If an account with that email exists, a password reset link has been sent.';
    } else {
        $error = 'Please enter your email address.';
    }
}

// If already logged in, redirect to app
if (isset($_SESSION['user'])) {
    header('Location: routing/app.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Password Reset</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="shared/styles.css">
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
                        Reset Password
                    </div>
                    <p class="text-sm text-[hsl(var(--muted-foreground))] mt-2">Enter your email address and we'll send
                        you a link to reset your password.</p>
                </div>

                <div
                    class="glass-effect rounded-2xl border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-2xl backdrop-blur-xl p-8">
                    <?php if ($error): ?>
                        <div
                            class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($message): ?>
                        <div
                            class="mb-6 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 text-sm">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-6">
                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-semibold text-[hsl(var(--foreground))] mb-2">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[hsl(var(--primary))]" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    Email Address
                                </div>
                            </label>
                            <input type="email" id="email" name="email" required
                                class="w-full px-4 py-3 border border-[hsl(var(--border))] rounded-xl bg-[hsl(var(--input))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))] focus:border-transparent transition-all duration-200 placeholder-[hsl(var(--muted-foreground))]"
                                placeholder="Enter your email address"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" autocomplete="email">
                        </div>

                        <button type="submit" name="reset_request" value="1"
                            class="w-full btn-primary text-white py-3 px-6 rounded-xl font-semibold transform hover:scale-[1.02] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))] focus:ring-offset-2 shadow-lg hover:shadow-xl">
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                                Send Reset Link
                            </span>
                        </button>
                    </form>

                    <div class="mt-6 text-center">
                        <a href="/HR4_COMPEN&INTELLI/routing/login.php"
                            class="inline-flex items-center gap-2 text-sm font-medium text-[hsl(var(--primary))] hover:text-[hsl(var(--primary))]/80 hover:underline transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Login
                        </a>
                    </div>
                </div>

                <div class="text-center text-sm text-[hsl(var(--muted-foreground))]">
                    <div class="mb-3 font-medium">Need help?</div>
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-[hsl(var(--primary))]" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5z">
                            </path>
                        </svg>
                        Contact your system administrator for assistance
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Page scripts can go here; theme handled globally via shared/scripts.js
        });
    </script>
</body>

</html>