<?php
// HR Manager Analytics Hub Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../routing/rbac.php';
include_once __DIR__ . '/../../config/database.php';

$activeId = 'analytics';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Fetch analytics data
try {
    // Workforce analytics
    $totalEmployees = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'Active'")->fetchColumn();
    $totalDepartments = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
    $averageAge = $pdo->query("SELECT AVG(YEAR(CURDATE()) - YEAR(birth_date)) FROM employee_details ed JOIN employees e ON ed.employee_id = e.id WHERE e.status = 'Active'")->fetchColumn();

    // Department distribution
    $departmentStats = $pdo->query("SELECT d.department_name, COUNT(e.id) as employee_count, 
                                   AVG(sg.min_salary + sg.max_salary) / 2 as avg_salary
                                   FROM departments d
                                   LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'Active'
                                   LEFT JOIN positions p ON e.position_id = p.id
                                   LEFT JOIN salary_grades sg ON p.salary_grade_id = sg.id
                                   GROUP BY d.id, d.department_name
                                   ORDER BY employee_count DESC")->fetchAll();

    // Payroll analytics
    $totalPayroll = $pdo->query("SELECT SUM(net_pay) FROM payroll_entries pe 
                                JOIN payroll_periods pp ON pe.period_id = pp.id 
                                WHERE pp.status = 'Processed' AND pp.period_start >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)")->fetchColumn();

    $averageSalary = $pdo->query("SELECT AVG(net_pay) FROM payroll_entries pe 
                                 JOIN payroll_periods pp ON pe.period_id = pp.id 
                                 WHERE pp.status = 'Processed' AND pp.period_start >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)")->fetchColumn();

    // Benefits analytics
    $totalBenefitsCost = $pdo->query("SELECT SUM(hp.premium_amount * COUNT(be.id)) FROM hmo_plans hp 
                                     LEFT JOIN benefit_enrollments be ON hp.id = be.plan_id AND be.status = 'Active'
                                     GROUP BY hp.id")->fetchColumn();

    $activeEnrollments = $pdo->query("SELECT COUNT(*) FROM benefit_enrollments WHERE status = 'Active'")->fetchColumn();

    // Recent trends (last 6 months)
    $monthlyTrends = $pdo->query("SELECT 
                                 DATE_FORMAT(pp.period_start, '%Y-%m') as month,
                                 COUNT(DISTINCT pe.employee_id) as employees,
                                 SUM(pe.net_pay) as total_payroll,
                                 AVG(pe.net_pay) as avg_payroll
                                 FROM payroll_periods pp
                                 LEFT JOIN payroll_entries pe ON pp.id = pe.period_id
                                 WHERE pp.period_start >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                                 GROUP BY DATE_FORMAT(pp.period_start, '%Y-%m')
                                 ORDER BY month DESC")->fetchAll();

    // Gender distribution
    $genderStats = $pdo->query("SELECT gender, COUNT(*) as count FROM employee_details ed 
                               JOIN employees e ON ed.employee_id = e.id 
                               WHERE e.status = 'Active' GROUP BY gender")->fetchAll();

    // Employment type distribution
    $employmentStats = $pdo->query("SELECT employment_type, COUNT(*) as count FROM employee_details ed 
                                   JOIN employees e ON ed.employee_id = e.id 
                                   WHERE e.status = 'Active' GROUP BY employment_type")->fetchAll();

    // Top earners
    $topEarners = $pdo->query("SELECT e.first_name, e.last_name, e.employee_number, d.department_name, 
                              AVG(pe.net_pay) as avg_salary
                              FROM payroll_entries pe
                              JOIN employees e ON pe.employee_id = e.id
                              LEFT JOIN departments d ON e.department_id = d.id
                              JOIN payroll_periods pp ON pe.period_id = pp.id
                              WHERE pp.status = 'Processed' AND pp.period_start >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                              GROUP BY e.id
                              ORDER BY avg_salary DESC
                              LIMIT 10")->fetchAll();

    // Real-time metrics
    $newHiresThisMonth = $pdo->query("SELECT COUNT(*) FROM employees WHERE hire_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)")->fetchColumn();
    $resignationsThisMonth = $pdo->query("SELECT COUNT(*) FROM employees WHERE resignation_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)")->fetchColumn();
    $pendingApprovals = $pdo->query("SELECT COUNT(*) FROM payroll_entries pe JOIN payroll_periods pp ON pe.period_id = pp.id WHERE pp.status = 'Open'")->fetchColumn();
    $activeClaims = $pdo->query("SELECT COUNT(*) FROM benefit_claims WHERE status = 'Pending'")->fetchColumn();

    // Performance metrics
    $avgProcessingTime = $pdo->query("SELECT AVG(DATEDIFF(processed_date, claim_date)) FROM benefit_claims WHERE status = 'Approved' AND processed_date IS NOT NULL")->fetchColumn();
    $budgetUtilization = $pdo->query("SELECT (SUM(sg.min_salary + sg.max_salary) / 2 * COUNT(e.id)) / SUM(d.budget_allocation) * 100 
                                     FROM departments d
                                     LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'Active'
                                     LEFT JOIN positions p ON e.position_id = p.id
                                     LEFT JOIN salary_grades sg ON p.salary_grade_id = sg.id
                                     WHERE d.budget_allocation > 0")->fetchColumn();

    // Turnover analysis
    $turnoverRate = $pdo->query("SELECT (COUNT(CASE WHEN resignation_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) THEN 1 END) / COUNT(*)) * 100 
                                FROM employees")->fetchColumn();

    // Cost per employee
    $costPerEmployee = $pdo->query("SELECT (SUM(pe.net_pay) + COALESCE(SUM(hp.premium_amount * COUNT(be.id)), 0)) / COUNT(DISTINCT e.id)
                                   FROM employees e
                                   LEFT JOIN payroll_entries pe ON e.id = pe.employee_id
                                   LEFT JOIN payroll_periods pp ON pe.period_id = pp.id AND pp.status = 'Processed'
                                   LEFT JOIN benefit_enrollments be ON e.id = be.employee_id AND be.status = 'Active'
                                   LEFT JOIN hmo_plans hp ON be.plan_id = hp.id
                                   WHERE e.status = 'Active'")->fetchColumn();

    // Department performance metrics
    $departmentPerformance = $pdo->query("SELECT d.department_name, 
                                         COUNT(e.id) as employee_count,
                                         AVG(sg.min_salary + sg.max_salary) / 2 as avg_salary,
                                         COUNT(CASE WHEN e.hire_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) THEN 1 END) as new_hires,
                                         COUNT(CASE WHEN e.resignation_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) THEN 1 END) as resignations,
                                         d.budget_allocation
                                         FROM departments d
                                         LEFT JOIN employees e ON d.id = e.department_id
                                         LEFT JOIN positions p ON e.position_id = p.id
                                         LEFT JOIN salary_grades sg ON p.salary_grade_id = sg.id
                                         GROUP BY d.id
                                         ORDER BY employee_count DESC")->fetchAll();

} catch (PDOException $e) {
    $totalEmployees = $totalDepartments = $averageAge = 0;
    $departmentStats = [];
    $totalPayroll = $averageSalary = $totalBenefitsCost = $activeEnrollments = 0;
    $monthlyTrends = [];
    $genderStats = [];
    $employmentStats = [];
    $topEarners = [];
    $newHiresThisMonth = $resignationsThisMonth = $pendingApprovals = $activeClaims = 0;
    $avgProcessingTime = $budgetUtilization = $turnoverRate = $costPerEmployee = 0;
    $departmentPerformance = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Analytics Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/HR4_COMPEN&INTELLI/shared/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            <h1 class="text-lg font-semibold">Analytics Hub</h1>
                            <p class="text-xs text-slate-500 mt-1">Real-time workforce and payroll analytics</p>
                        </div>

                        <!-- Quick Actions -->
                        <div class="flex flex-wrap gap-2">
                            <button onclick="exportAnalytics()"
                                class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Export Report
                            </button>
                            <button onclick="openAdvancedAnalytics()"
                                class="bg-purple-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Advanced Analytics
                            </button>
                            <button onclick="openPredictiveAnalytics()"
                                class="bg-indigo-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Predictive Analytics
                            </button>
                            <button onclick="openRealTimeDashboard()"
                                class="bg-blue-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Real-time Dashboard
                            </button>
                            <button onclick="generateCustomReport()"
                                class="bg-orange-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Custom Report
                            </button>
                            <button onclick="refreshData()"
                                class="bg-slate-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Refresh Data
                            </button>
                            <button id="autoRefreshBtn" onclick="toggleAutoRefresh()"
                                class="bg-gray-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Enable Auto Refresh
                            </button>
                        </div>

                        <!-- Key Metrics -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Total Employees</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($totalEmployees); ?></div>
                                <div class="text-xs text-blue-600 mt-1">Active workforce</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Departments</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($totalDepartments); ?>
                                </div>
                                <div class="text-xs text-green-600 mt-1">Organizational units</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Average Age</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($averageAge, 1); ?></div>
                                <div class="text-xs text-purple-600 mt-1">Years</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Total Payroll</div>
                                <div class="text-2xl font-semibold">₱<?php echo number_format($totalPayroll, 0); ?>
                                </div>
                                <div class="text-xs text-orange-600 mt-1">Last 12 months</div>
                            </div>
                        </div>

                        <!-- Real-time Metrics -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">New Hires (30 days)</div>
                                <div class="text-2xl font-semibold text-green-600">
                                    <?php echo number_format($newHiresThisMonth); ?></div>
                                <div class="text-xs text-green-600 mt-1">Recent additions</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Resignations (30 days)</div>
                                <div class="text-2xl font-semibold text-red-600">
                                    <?php echo number_format($resignationsThisMonth); ?></div>
                                <div class="text-xs text-red-600 mt-1">Recent departures</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Pending Approvals</div>
                                <div class="text-2xl font-semibold text-orange-600">
                                    <?php echo number_format($pendingApprovals); ?></div>
                                <div class="text-xs text-orange-600 mt-1">Awaiting review</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Active Claims</div>
                                <div class="text-2xl font-semibold text-blue-600">
                                    <?php echo number_format($activeClaims); ?></div>
                                <div class="text-xs text-blue-600 mt-1">In processing</div>
                            </div>
                        </div>

                        <!-- Performance Metrics -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Avg Processing Time</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($avgProcessingTime, 1); ?>
                                    days</div>
                                <div class="text-xs text-purple-600 mt-1">Claims processing</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Budget Utilization</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($budgetUtilization, 1); ?>%
                                </div>
                                <div class="text-xs text-indigo-600 mt-1">Department budgets</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Turnover Rate</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($turnoverRate, 1); ?>%
                                </div>
                                <div class="text-xs text-pink-600 mt-1">Annual rate</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Cost per Employee</div>
                                <div class="text-2xl font-semibold">₱<?php echo number_format($costPerEmployee, 0); ?>
                                </div>
                                <div class="text-xs text-teal-600 mt-1">Monthly average</div>
                            </div>
                        </div>

                        <!-- Department Performance Dashboard -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Department Performance Dashboard</div>
                                    <div class="text-sm text-slate-500">Comprehensive department metrics</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Department</th>
                                            <th class="text-left px-3 py-2 font-semibold">Employees</th>
                                            <th class="text-left px-3 py-2 font-semibold">Avg Salary</th>
                                            <th class="text-left px-3 py-2 font-semibold">New Hires (12m)</th>
                                            <th class="text-left px-3 py-2 font-semibold">Resignations (12m)</th>
                                            <th class="text-left px-3 py-2 font-semibold">Budget</th>
                                            <th class="text-left px-3 py-2 font-semibold">Utilization</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($departmentPerformance as $dept): ?>
                                            <?php
                                            $utilization = $dept['budget_allocation'] > 0 ? (($dept['avg_salary'] * $dept['employee_count']) / $dept['budget_allocation']) * 100 : 0;
                                            $turnoverRate = $dept['employee_count'] > 0 ? ($dept['resignations'] / $dept['employee_count']) * 100 : 0;
                                            ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($dept['department_name']); ?></div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo number_format($dept['employee_count']); ?></div>
                                                </td>
                                                <td class="px-3 py-3">₱<?php echo number_format($dept['avg_salary'], 0); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span
                                                        class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                                        <?php echo number_format($dept['new_hires']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">
                                                        <?php echo number_format($dept['resignations']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    ₱<?php echo number_format($dept['budget_allocation'], 0); ?></td>
                                                <td class="px-3 py-3">
                                                    <div class="flex items-center space-x-2">
                                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                                            <div class="bg-blue-600 h-2 rounded-full"
                                                                style="width: <?php echo min($utilization, 100); ?>%"></div>
                                                        </div>
                                                        <span
                                                            class="text-xs"><?php echo number_format($utilization, 1); ?>%</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Charts Row -->
                        <div class="grid lg:grid-cols-2 gap-4">
                            <!-- Department Distribution -->
                            <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                                <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                    <div class="font-semibold">Department Distribution</div>
                                </div>
                                <div class="p-4">
                                    <canvas id="departmentChart" width="400" height="200"></canvas>
                                </div>
                            </div>

                            <!-- Gender Distribution -->
                            <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                                <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                    <div class="font-semibold">Gender Distribution</div>
                                </div>
                                <div class="p-4">
                                    <canvas id="genderChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Trends -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="font-semibold">Payroll Trends (Last 6 Months)</div>
                            </div>
                            <div class="p-4">
                                <canvas id="trendsChart" width="800" height="300"></canvas>
                            </div>
                        </div>

                        <!-- Department Statistics -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Department Statistics</div>
                                    <div class="text-sm text-slate-500">Employee count and average salary</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Department</th>
                                            <th class="text-left px-3 py-2 font-semibold">Employees</th>
                                            <th class="text-left px-3 py-2 font-semibold">Avg Salary</th>
                                            <th class="text-left px-3 py-2 font-semibold">Distribution</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($departmentStats as $dept): ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                        <?php echo $dept['employee_count']; ?> employees
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    ₱<?php echo number_format($dept['avg_salary'] ?? 0, 0); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="bg-blue-600 h-2 rounded-full"
                                                            style="width: <?php echo $totalEmployees > 0 ? ($dept['employee_count'] / $totalEmployees) * 100 : 0; ?>%">
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Top Earners -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Top Earners (Last 3 Months)</div>
                                    <div class="text-sm text-slate-500">Average salary</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Rank</th>
                                            <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Department</th>
                                            <th class="text-left px-3 py-2 font-semibold">Avg Salary</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topEarners as $index => $earner): ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <span
                                                        class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">
                                                        #<?php echo $index + 1; ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div>
                                                        <div class="font-medium">
                                                            <?php echo htmlspecialchars($earner['first_name'] . ' ' . $earner['last_name']); ?>
                                                        </div>
                                                        <div class="text-xs text-slate-500">
                                                            <?php echo htmlspecialchars($earner['employee_number']); ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <?php echo htmlspecialchars($earner['department_name'] ?? 'N/A'); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="font-semibold">
                                                        ₱<?php echo number_format($earner['avg_salary'], 2); ?></div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Benefits Analytics -->
                        <div class="grid lg:grid-cols-2 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Benefits Cost</div>
                                <div class="text-2xl font-semibold">₱<?php echo number_format($totalBenefitsCost, 0); ?>
                                </div>
                                <div class="text-xs text-green-600 mt-1">Monthly premium</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Active Enrollments</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($activeEnrollments); ?>
                                </div>
                                <div class="text-xs text-blue-600 mt-1">Benefit participants</div>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        // Department Chart
        const departmentCtx = document.getElementById('departmentChart').getContext('2d');
        new Chart(departmentCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php echo implode(',', array_map(function ($dept) {
                    return '"' . htmlspecialchars($dept['department_name']) . '"';
                }, $departmentStats)); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column($departmentStats, 'employee_count')); ?>],
                    backgroundColor: [
                        '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                        '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6B7280'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Gender Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: [<?php echo implode(',', array_map(function ($gender) {
                    return '"' . htmlspecialchars($gender['gender']) . '"';
                }, $genderStats)); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_column($genderStats, 'count')); ?>],
                    backgroundColor: ['#3B82F6', '#EC4899', '#10B981']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Trends Chart
        const trendsCtx = document.getElementById('trendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: [<?php echo implode(',', array_map(function ($trend) {
                    return '"' . $trend['month'] . '"';
                }, array_reverse($monthlyTrends))); ?>],
                datasets: [{
                    label: 'Total Payroll',
                    data: [<?php echo implode(',', array_map(function ($trend) {
                        return $trend['total_payroll'] ?? 0;
                    }, array_reverse($monthlyTrends))); ?>],
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Average Salary',
                    data: [<?php echo implode(',', array_map(function ($trend) {
                        return $trend['avg_payroll'] ?? 0;
                    }, array_reverse($monthlyTrends))); ?>],
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });

        function exportAnalytics() {
            alert('Export analytics functionality coming soon');
        }

        function generateCustomReport() {
            alert('Custom report functionality coming soon');
        }

        function refreshData() {
            location.reload();
        }

        // Advanced analytics functions
        function openAdvancedAnalytics() {
            alert('Advanced analytics - Access predictive analytics, forecasting, and advanced reporting features');
        }

        function openRealTimeDashboard() {
            alert('Real-time dashboard - View live updates and real-time metrics');
        }

        function openPredictiveAnalytics() {
            alert('Predictive analytics - Forecast trends, predict turnover, and analyze future workforce needs');
        }

        function openCustomReports() {
            alert('Custom reports - Create custom analytics reports with specific metrics and visualizations');
        }

        // Chart interaction functions
        function filterByDepartment(department) {
            alert(`Filtering analytics by department: ${department}`);
        }

        function filterByTimeRange(range) {
            alert(`Filtering analytics by time range: ${range}`);
        }

        function exportChart(chartId) {
            alert(`Exporting chart: ${chartId} - This would save the chart as an image`);
        }

        // Auto-refresh functionality
        let autoRefreshInterval;

        function toggleAutoRefresh() {
            const button = document.getElementById('autoRefreshBtn');
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
                button.textContent = 'Enable Auto Refresh';
                button.classList.remove('bg-green-600');
                button.classList.add('bg-gray-600');
            } else {
                autoRefreshInterval = setInterval(refreshData, 30000); // Refresh every 30 seconds
                button.textContent = 'Disable Auto Refresh';
                button.classList.remove('bg-gray-600');
                button.classList.add('bg-green-600');
            }
        }

        // Initialize tooltips and interactions
        document.addEventListener('DOMContentLoaded', function () {
            // Add hover effects to metric cards
            const metricCards = document.querySelectorAll('.rounded-lg.border.border-\\[hsl\\(var\\(--border\\)\\)\\]');
            metricCards.forEach(card => {
                card.addEventListener('mouseenter', function () {
                    this.classList.add('shadow-lg', 'transform', 'scale-105');
                });
                card.addEventListener('mouseleave', function () {
                    this.classList.remove('shadow-lg', 'transform', 'scale-105');
                });
            });
        });
    </script>
</body>

</html>