<?php
// HR Manager Dashboard Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../routing/rbac.php';
include_once __DIR__ . '/../../config/database.php';

$activeId = 'dashboard';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Fetch dashboard statistics
try {
    // Total employees
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM employees WHERE status = 'Active'");
    $totalEmployees = $stmt->fetch()['total'];

    // Total departments
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM departments");
    $totalDepartments = $stmt->fetch()['total'];

    // Payroll entries for current period
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM payroll_entries pe 
                        JOIN payroll_periods pp ON pe.period_id = pp.id 
                        WHERE pp.status = 'Processed'");
    $payrollEntries = $stmt->fetch()['total'];

    // Active benefits
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM benefit_enrollments WHERE status = 'Active'");
    $activeBenefits = $stmt->fetch()['total'];

    // Recent activities from audit logs
    $stmt = $pdo->query("SELECT al.*, u.username, e.first_name, e.last_name 
                        FROM audit_logs al 
                        LEFT JOIN users u ON al.user_id = u.id 
                        LEFT JOIN employees e ON u.employee_id = e.id 
                        ORDER BY al.timestamp DESC LIMIT 5");
    $recentActivities = $stmt->fetchAll();

} catch (PDOException $e) {
    $totalEmployees = $totalDepartments = $payrollEntries = $activeBenefits = 0;
    $recentActivities = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - HR Manager Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/HR4_COMPEN&INTELLI/shared/styles.css">
</head>

<body>
    <div id="app" class="h-screen">
        <div class="h-full flex flex-col">
            <?php echo renderHeader($user, $sidebarCollapsed); ?>
            <div
                class="flex-1 grid <?php echo $sidebarCollapsed ? 'lg:grid-cols-[72px_1fr]' : 'lg:grid-cols-[260px_1fr]'; ?>">
                <?php echo renderSidebar($sidebarItems, $activeId, $sidebarCollapsed); ?>
                <main class="overflow-y-auto">
                    <section class="p-4 lg:p-6 space-y-4">
                        <div>
                            <h1 class="text-lg font-semibold">HR Manager</h1>
                            <p class="text-xs text-slate-500 mt-1">Role-based overview with quick insights</p>
                        </div>
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Total Employees</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($totalEmployees); ?></div>
                                <div class="text-xs text-green-600 mt-1">+2 this month</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Departments</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($totalDepartments); ?>
                                </div>
                                <div class="text-xs text-blue-600 mt-1">All active</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Payroll Entries</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($payrollEntries); ?></div>
                                <div class="text-xs text-orange-600 mt-1">Current period</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Active Benefits</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($activeBenefits); ?></div>
                                <div class="text-xs text-purple-600 mt-1">Enrolled</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Recent Activities
                                </div>
                                <div class="p-4 text-sm text-slate-600 dark:text-slate-300">
                                    <?php if (empty($recentActivities)): ?>
                                        <div class="text-sm text-slate-500">No recent activities</div>
                                    <?php else: ?>
                                        <div class="space-y-2">
                                            <?php foreach ($recentActivities as $activity): ?>
                                                <div
                                                    class="flex items-center justify-between py-2 border-b border-slate-100 last:border-b-0">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                                        <div>
                                                            <div class="text-sm font-medium">
                                                                <?php echo htmlspecialchars($activity['username'] ?? 'System'); ?>
                                                            </div>
                                                            <div class="text-xs text-slate-500">
                                                                <?php echo ucfirst($activity['action_type']); ?> on
                                                                <?php echo htmlspecialchars($activity['table_affected']); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-xs text-slate-400">
                                                        <?php echo date('M j, H:i', strtotime($activity['timestamp'])); ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>
    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
</body>

</html>