<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

// Check if user is logged in
$isLoggedIn = $auth->isLoggedIn();
$userInfo = $isLoggedIn ? $auth->getUserInfo() : null;
?>
<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>HR4 - Compensation & HR Intelligence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: "#f0f7ff",
                            100: "#e0efff",
                            200: "#b9dbff",
                            300: "#89c2ff",
                            400: "#61a6ff",
                            500: "#3b82f6",
                            600: "#2f6bd6",
                            700: "#2553ac",
                            800: "#1e4287",
                            900: "#1b376f",
                        },
                    },
                },
            },
        };
    </script>
    <style>
        :root {
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --card: 0 0% 100%;
            --card-foreground: 222.2 84% 4.9%;
            --popover: 0 0% 100%;
            --popover-foreground: 222.2 84% 4.9%;
            --primary: 240 5% 26%;
            --primary-foreground: 210 40% 98%;
            --secondary: 210 40% 96.1%;
            --secondary-foreground: 222.2 47.4% 11.2%;
            --muted: 210 40% 96.1%;
            --muted-foreground: 215.4 16.3% 46.9%;
            --accent: 210 40% 96.1%;
            --accent-foreground: 222.2 47.4% 11.2%;
            --destructive: 0 84.2% 60.2%;
            --destructive-foreground: 210 40% 98%;
            --border: 214.3 31.8% 91.4%;
            --input: 214.3 31.8% 91.4%;
            --ring: 240 5% 64.9%;
        }

        .dark:root {
            --background: 222.2 84% 4.9%;
            --foreground: 210 40% 98%;
            --card: 222.2 84% 4.9%;
            --card-foreground: 210 40% 98%;
            --popover: 222.2 84% 4.9%;
            --popover-foreground: 210 40% 98%;
            --primary: 240 5% 64.9%;
            --primary-foreground: 222.2 47.4% 11.2%;
            --secondary: 217.2 32.6% 17.5%;
            --secondary-foreground: 210 40% 98%;
            --muted: 217.2 32.6% 17.5%;
            --muted-foreground: 215 20.2% 65.1%;
            --accent: 217.2 32.6% 17.5%;
            --accent-foreground: 210 40% 98%;
            --destructive: 0 62.8% 30.6%;
            --destructive-foreground: 210 40% 98%;
            --border: 217.2 32.6% 17.5%;
            --input: 217.2 32.6% 17.5%;
            --ring: 240 4% 46%;
        }

        /* shadcn-like base */
        .ui-btn {
            @apply inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50;
        }

        .ui-btn--default {
            @apply bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95;
        }

        .ui-btn--secondary {
            @apply bg-[hsl(var(--secondary))] text-[hsl(var(--secondary-foreground))] hover:bg-[hsl(var(--secondary))]/80;
        }

        .ui-btn--ghost {
            @apply hover:bg-[hsl(var(--accent))] hover:text-[hsl(var(--accent-foreground))];
        }

        .ui-btn--outline {
            @apply border border-[hsl(var(--border))] bg-transparent hover:bg-[hsl(var(--accent))];
        }

        .ui-btn--sm {
            @apply h-9 px-3;
        }

        .ui-btn--md {
            @apply h-10 px-4;
        }

        .ui-btn--lg {
            @apply h-11 px-6;
        }

        .ui-input {
            @apply flex h-10 w-full rounded-md border border-[hsl(var(--input))] bg-transparent px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-[hsl(var(--muted-foreground))] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2;
        }

        .ui-card {
            @apply rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm;
        }

        .ui-card-header {
            @apply p-4 border-b border-[hsl(var(--border))] font-semibold;
        }

        .ui-card-content {
            @apply p-4 text-sm text-slate-600 dark:text-slate-300;
        }

        .ui-card-footer {
            @apply p-4 border-t border-[hsl(var(--border))];
        }

        .ui-sheet {
            @apply fixed inset-0 z-40 hidden;
        }

        .ui-sheet[data-open="true"] {
            @apply block;
        }

        .ui-sheet-overlay {
            @apply absolute inset-0 bg-black/40;
        }

        .ui-sheet-panel {
            @apply absolute left-0 top-0 h-full w-72 bg-[hsl(var(--background))] border-r border-[hsl(var(--border))] shadow-xl;
        }

        /* Login animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        .animate-slide-in {
            animation: slideIn 0.4s ease-out;
        }

        /* Enhanced focus states */
        .ui-input:focus {
            @apply ring-2 ring-brand-500/20 border-brand-500;
        }

        .ui-input:invalid {
            @apply border-red-500;
        }

        /* Loading spinner */
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        /* Button hover effects */
        .ui-btn:hover {
            @apply transform transition-transform duration-150;
        }

        .ui-btn:active {
            @apply scale-95;
        }

        /* Form validation styles */
        .form-error {
            @apply border-red-500 bg-red-50 dark:bg-red-900/20;
        }

        .form-success {
            @apply border-green-500 bg-green-50 dark:bg-green-900/20;
        }
    </style>
</head>

<body class="h-full bg-white text-slate-800 dark:bg-slate-950 dark:text-slate-100">
    <!-- App Root -->
    <div id="app" class="h-full"></div>

    <!-- PHP User Data -->
    <script>
        window.HR4_USER = {
            isLoggedIn: <?php echo $isLoggedIn ? 'true' : 'false'; ?>,
            userInfo: <?php echo $isLoggedIn ? json_encode($userInfo) : 'null'; ?>,
            csrfToken: '<?php echo bin2hex(random_bytes(32)); ?>'
        };
    </script>

    <!-- Templates rendered by JS -->
    <script src="js/rbac.js"></script>
    <script src="js/ui.js"></script>
    <script src="js/app.js"></script>
</body>

</html>