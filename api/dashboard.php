<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $userInfo = $auth->getUserInfo();
    $role = $userInfo['role_name'];

    $dashboardData = [];

    // Get basic metrics
    $stmt = $pdo->query("SELECT COUNT(*) as total_employees FROM employees WHERE status = 'Active'");
    $dashboardData['total_employees'] = $stmt->fetch()['total_employees'];

    $stmt = $pdo->query("SELECT COUNT(*) as total_departments FROM departments");
    $dashboardData['total_departments'] = $stmt->fetch()['total_departments'];

    // Get payroll summary (using actual schema)
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_payroll_entries,
            SUM(amount) as total_amount
        FROM payroll_entries 
        WHERE is_processed = 1
    ");
    $payrollData = $stmt->fetch();
    $dashboardData['payroll'] = $payrollData;

    // Get benefits summary (using actual schema)
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_enrollments,
            COUNT(DISTINCT hmo_plan_id) as active_plans
        FROM benefit_enrollments 
        WHERE status = 'Active'
    ");
    $benefitsData = $stmt->fetch();
    $dashboardData['benefits'] = $benefitsData;

    // Get recent activities (using actual schema)
    $stmt = $pdo->query("
        SELECT 
            al.action_type,
            al.action_description,
            al.action_timestamp,
            u.username
        FROM audit_logs al
        JOIN users u ON al.user_id = u.id
        ORDER BY al.action_timestamp DESC
        LIMIT 10
    ");
    $dashboardData['recent_activities'] = $stmt->fetchAll();

    // Role-specific data
    if ($role === 'HR Manager') {
        $stmt = $pdo->query("
            SELECT 
                d.department_name,
                COUNT(e.id) as employee_count
            FROM departments d
            LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'Active'
            GROUP BY d.id, d.department_name
            ORDER BY employee_count DESC
        ");
        $dashboardData['department_stats'] = $stmt->fetchAll();
    }

    if ($role === 'Payroll Administrator') {
        $stmt = $pdo->query("
            SELECT 
                pp.start_date,
                pp.end_date,
                pp.status,
                COUNT(pe.id) as entries_count,
                SUM(pe.amount) as total_amount
            FROM payroll_periods pp
            LEFT JOIN payroll_entries pe ON pp.id = pe.payroll_period_id
            GROUP BY pp.id
            ORDER BY pp.start_date DESC
            LIMIT 5
        ");
        $dashboardData['payroll_periods'] = $stmt->fetchAll();
    }

    if ($role === 'Benefits Coordinator') {
        $stmt = $pdo->query("
            SELECT 
                hp.plan_name,
                COUNT(be.id) as enrollment_count,
                hp.monthly_premium
            FROM hmo_plans hp
            LEFT JOIN benefit_enrollments be ON hp.id = be.hmo_plan_id AND be.status = 'Active'
            GROUP BY hp.id
            ORDER BY enrollment_count DESC
        ");
        $dashboardData['benefit_plans'] = $stmt->fetchAll();
    }

    echo json_encode([
        'ok' => true,
        'data' => $dashboardData,
        'user_role' => $role
    ]);

} catch (Exception $e) {
    error_log("Dashboard API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Internal server error']);
}
?>