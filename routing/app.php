<?php
// HR4 Unified App Router
session_start();
include_once 'rbac.php';
include_once '../config/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    // Check for remember me token
    $remember_token = $_COOKIE['hr4_remember_token'] ?? '';
    if ($remember_token) {
        $auth = new Auth();
        $user = $auth->validateRememberToken($remember_token);

        if ($user) {
            // Create new session
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
        } else {
            // Invalid remember token, clear cookie and redirect
            setcookie('hr4_remember_token', '', time() - 3600, '/', '', true, true);
            header('Location: login.php');
            exit;
        }
    } else {
        header('Location: login.php');
        exit;
    }
}

// Validate session with database
$auth = new Auth();
$session_token = $_SESSION['user']['session_token'] ?? '';
$validated_user = $auth->validateSession($session_token);

if (!$validated_user) {
    // Session expired or invalid, destroy session and redirect to login
    error_log("HR4 Debug: Session validation failed for token: " . substr($session_token, 0, 10) . "...");
    session_destroy();
    header('Location: login.php');
    exit;
}


// Update session with fresh user data
$user = [
    'id' => $validated_user['id'] ?? 0,
    'username' => $validated_user['username'] ?? '',
    'role' => $validated_user['role'] ?? 'Hospital Employee',
    'employee_id' => $validated_user['employee_id'] ?? 0,
    'first_name' => $validated_user['first_name'] ?? '',
    'last_name' => $validated_user['last_name'] ?? '',
    'employee_number' => $validated_user['employee_number'] ?? '',
    'session_token' => $session_token
];

$_SESSION['user'] = $user;
$sidebarCollapsed = false;

// Get the page parameter from URL
$page = $_GET['page'] ?? 'dashboard';

// Define available pages for each role
$rolePages = [
    'HR Manager' => [
        'dashboard' => '../roles/HR_MANAGER/dashboard.php',
        'employees' => '../roles/HR_MANAGER/employees.php',
        'payroll' => '../roles/HR_MANAGER/payroll.php',
        'compensation' => '../roles/HR_MANAGER/compensation.php',
        'benefits' => '../roles/HR_MANAGER/benefits.php',
        'leave' => '../roles/HR_MANAGER/leave.php',
        'analytics' => '../roles/HR_MANAGER/analytics.php',
        'documents' => '../roles/HR_MANAGER/documents.php',
        'delegations' => '../roles/HR_MANAGER/delegations.php',
        'settings' => '../roles/HR_MANAGER/settings.php'
    ],
    'Compensation Manager' => [
        'dashboard' => '../roles/COMPENSATION_MANAGER/dashboard.php',
        'structures' => '../roles/COMPENSATION_MANAGER/structures.php',
        'merit' => '../roles/COMPENSATION_MANAGER/merit.php',
        'budget' => '../roles/COMPENSATION_MANAGER/budget.php',
        'equity' => '../roles/COMPENSATION_MANAGER/equity.php',
        'benchmarking' => '../roles/COMPENSATION_MANAGER/benchmarking.php',
        'analytics' => '../roles/COMPENSATION_MANAGER/analytics.php'
    ],
    'Benefits Coordinator' => [
        'dashboard' => '../roles/BENEFITS_COORDINATOR/dashboard.php',
        'benefits' => '../roles/BENEFITS_COORDINATOR/benefits.php',
        'claims' => '../roles/BENEFITS_COORDINATOR/claims.php',
        'providers' => '../roles/BENEFITS_COORDINATOR/providers.php',
        'enrollment' => '../roles/BENEFITS_COORDINATOR/enrollment.php',
        'benefits-analytics' => '../roles/BENEFITS_COORDINATOR/benefits-analytics.php',
        'documents' => '../roles/BENEFITS_COORDINATOR/documents.php',
        'member' => '../roles/BENEFITS_COORDINATOR/member.php'
    ],
    'Payroll Administrator' => [
        'dashboard' => '../roles/PAYROLL_ADMIN/dashboard.php',
        'payroll' => '../roles/PAYROLL_ADMIN/payroll.php',
        'bank' => '../roles/PAYROLL_ADMIN/bank.php',
        'payslips' => '../roles/PAYROLL_ADMIN/payslips.php',
        'compliance' => '../roles/PAYROLL_ADMIN/compliance.php',
        'tax' => '../roles/PAYROLL_ADMIN/tax.php',
        'reports' => '../roles/PAYROLL_ADMIN/reports.php',
        'deductions' => '../roles/PAYROLL_ADMIN/deductions.php'
    ],
    'Department Head' => [
        'dashboard' => '../roles/DEPT_HEAD/dashboard.php',
        'team' => '../roles/DEPT_HEAD/team.php',
        'budget' => '../roles/DEPT_HEAD/budget.php',
        'leave' => '../roles/DEPT_HEAD/leave.php',
        'performance' => '../roles/DEPT_HEAD/performance.php',
        'reports' => '../roles/DEPT_HEAD/reports.php',
        'documents' => '../roles/DEPT_HEAD/documents.php'
    ],
    'Hospital Employee' => [
        'dashboard' => '../roles/EMPLOYEE/dashboard.php',
        'profile' => '../roles/EMPLOYEE/profile.php',
        'payslips' => '../roles/EMPLOYEE/payslips.php',
        'leave' => '../roles/EMPLOYEE/leave.php',
        'benefits-center' => '../roles/EMPLOYEE/benefits-center.php',
        'documents' => '../roles/EMPLOYEE/documents.php',
        'help' => '../roles/EMPLOYEE/help.php'
    ],
    'Hospital Management' => [
        'dashboard' => '../roles/EXECUTIVE/dashboard.php',
        'workforce' => '../roles/EXECUTIVE/workforce.php',
        'cost' => '../roles/EXECUTIVE/cost.php',
        'benefits' => '../roles/EXECUTIVE/benefits.php',
        'compliance' => '../roles/EXECUTIVE/compliance.php',
        'reports' => '../roles/EXECUTIVE/reports.php'
    ]
];

// Get available pages for current user's role
$availablePages = $rolePages[$user['role']] ?? [];

// Log role information for debugging (remove in production)
if (empty($availablePages)) {
    error_log("HR4 Debug: User role '{$user['role']}' not found in rolePages. Available roles: " . implode(', ', array_keys($rolePages)));
}

// Check if the requested page exists and is allowed for this role
if (isset($availablePages[$page])) {
    $pageFile = $availablePages[$page];
    if (file_exists($pageFile)) {
        // Set active page for sidebar
        $activeId = $page;

        // Include the page file
        include $pageFile;
        exit;
    }
}

// Default to dashboard if page not found
$activeId = 'dashboard';

// Check if dashboard exists for this role, otherwise use a fallback
if (isset($availablePages['dashboard']) && file_exists($availablePages['dashboard'])) {
    include $availablePages['dashboard'];
} else {
    // Fallback to employee dashboard if role not found
    $fallbackDashboard = '../roles/EMPLOYEE/dashboard.php';
    if (file_exists($fallbackDashboard)) {
        include $fallbackDashboard;
    } else {
        // Last resort - show error page
        echo '<div style="padding: 20px; text-align: center;">';
        echo '<h2>Error: Dashboard not found</h2>';
        echo '<p>Unable to load dashboard for role: ' . htmlspecialchars($user['role']) . '</p>';
        echo '<p><a href="logout.php">Logout</a></p>';
        echo '</div>';
    }
}
?>