<?php
/**
 * HR4 Compensation & Intelligence System
 * Main Entry Point and Login
 */

require_once 'config/database.php';
require_once 'includes/auth.php';

// Initialize database and auth
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Handle logout
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    $auth->logout();
    header('Location: index.php');
    exit;
}

// Handle login
$error = '';
if ($_POST && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Debug: Log the login attempt
    error_log("Login attempt: username=$username");

    if ($auth->login($username, $password)) {
        $user = $auth->getCurrentUser();

        // Debug: Log successful login
        error_log("Login successful: user=" . $user['username'] . ", role=" . $user['role']);

        // Redirect to appropriate role dashboard
        $roleRedirects = [
            'HR Manager' => 'roles/hr_manager/dashboard.php',
            'Compensation Manager' => 'roles/compensation_manager/dashboard.php',
            'Benefits Coordinator' => 'roles/benefits_coordinator/dashboard.php',
            'Payroll Administrator' => 'roles/payroll_administrator/dashboard.php',
            'Department Head' => 'roles/department_head/dashboard.php',
            'Hospital Employee' => 'roles/hospital_employee/dashboard.php',
            'Hospital Management' => 'roles/hospital_management/dashboard.php'
        ];

        $redirectUrl = $roleRedirects[$user['role']] ?? 'roles/hr_manager/dashboard.php';
        header("Location: $redirectUrl");
        exit;
    } else {
        $error = 'Invalid username or password. Please check your credentials.';
        error_log("Login failed for username: $username");
    }
}

// If already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    $user = $auth->getCurrentUser();
    $roleRedirects = [
        'HR Manager' => 'roles/hr_manager/dashboard.php',
        'Compensation Manager' => 'roles/compensation_manager/dashboard.php',
        'Benefits Coordinator' => 'roles/benefits_coordinator/dashboard.php',
        'Payroll Administrator' => 'roles/payroll_administrator/dashboard.php',
        'Department Head' => 'roles/department_head/dashboard.php',
        'Hospital Employee' => 'roles/hospital_employee/dashboard.php',
        'Hospital Management' => 'roles/hospital_management/dashboard.php'
    ];

    $redirectUrl = $roleRedirects[$user['role']] ?? 'roles/hr_manager/dashboard.php';
    header("Location: $redirectUrl");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Compensation & Intelligence System</title>
    <link rel="stylesheet" href="shared/styles.css">
    <link rel="icon" type="image/svg+xml"
        href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E🏥%3C/text%3E%3C/svg%3E">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">HR4</div>
                <h1 class="login-title">HR Intelligence Portal</h1>
                <p class="login-subtitle">Compensation & HR Management System</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-input"
                        placeholder="Enter your username" required autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input"
                        placeholder="Enter your password" required autocomplete="current-password">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-500">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Sign In
                </button>
            </form>

            <div class="mt-8 text-center">
                <div class="text-sm text-gray-600 mb-4">
                    <p class="font-medium">Demo Credentials:</p>
                    <div class="mt-2 space-y-1 text-xs">
                        <p><strong>HR Manager:</strong> hr.manager / manager123</p>
                        <p><strong>Compensation Manager:</strong> comp.manager / comp123</p>
                        <p><strong>Benefits Coordinator:</strong> benefits.coord / benefits123</p>
                        <p><strong>Payroll Administrator:</strong> payroll.admin / payroll123</p>
                        <p><strong>Department Head:</strong> dept.head / dept123</p>
                        <p><strong>Hospital Employee:</strong> employee / emp123</p>
                        <p><strong>Hospital Management:</strong> executive / exec123</p>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <p class="text-xs text-gray-500">
                        &copy; 2024 Hospital System. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="shared/scripts.js"></script>
    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function () {
            const inputs = document.querySelectorAll('.form-input');

            inputs.forEach(input => {
                input.addEventListener('focus', function () {
                    this.parentElement.classList.add('focused');
                });

                input.addEventListener('blur', function () {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });

            // Add loading state to form submission
            const form = document.querySelector('form');
            form.addEventListener('submit', function () {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = `
                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Signing In...
                `;
                submitBtn.disabled = true;
            });
        });
    </script>
</body>

</html>