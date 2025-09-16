<?php
// HR Manager Analytics Hub Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../shared/database_helper.php';
include_once __DIR__ . '/../../routing/rbac.php';

$activeId = 'analytics';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Initialize database helper
$dbHelper = new DatabaseHelper();

// Handle CRUD operations
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_metric') {
        try {
            $metricName = $_POST['metric_name'] ?? '';
            $metricType = $_POST['metric_type'] ?? '';
            $description = $_POST['description'] ?? '';
            $formula = $_POST['formula'] ?? '';
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            $dbHelper->query("
                INSERT INTO analytics_metrics (metric_name, metric_type, description, formula, is_active) 
                VALUES (?, ?, ?, ?, ?)
            ", [$metricName, $metricType, $description, $formula, $isActive]);

            $message = 'Analytics metric created successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error creating metric: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'update_metric') {
        try {
            $metricId = intval($_POST['metric_id'] ?? 0);
            $metricName = $_POST['metric_name'] ?? '';
            $metricType = $_POST['metric_type'] ?? '';
            $description = $_POST['description'] ?? '';
            $formula = $_POST['formula'] ?? '';
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            $dbHelper->query("
                UPDATE analytics_metrics 
                SET metric_name = ?, metric_type = ?, description = ?, formula = ?, is_active = ?
                WHERE id = ?
            ", [$metricName, $metricType, $description, $formula, $isActive, $metricId]);

            $message = 'Analytics metric updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error updating metric: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete_metric') {
        try {
            $metricId = intval($_POST['metric_id'] ?? 0);
            $dbHelper->query("DELETE FROM analytics_metrics WHERE id = ?", [$metricId]);
            $message = 'Analytics metric deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error deleting metric: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'create_report_template') {
        try {
            $templateName = $_POST['template_name'] ?? '';
            $description = $_POST['description'] ?? '';
            $templateType = $_POST['template_type'] ?? '';
            $query = $_POST['query'] ?? '';
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            $dbHelper->query("
                INSERT INTO report_templates (template_name, description, template_type, query, is_active) 
                VALUES (?, ?, ?, ?, ?)
            ", [$templateName, $description, $templateType, $query, $isActive]);

            $message = 'Report template created successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error creating report template: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'update_report_template') {
        try {
            $templateId = intval($_POST['template_id'] ?? 0);
            $templateName = $_POST['template_name'] ?? '';
            $description = $_POST['description'] ?? '';
            $templateType = $_POST['template_type'] ?? '';
            $query = $_POST['query'] ?? '';
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            $dbHelper->query("
                UPDATE report_templates 
                SET template_name = ?, description = ?, template_type = ?, query = ?, is_active = ?
                WHERE id = ?
            ", [$templateName, $description, $templateType, $query, $isActive, $templateId]);

            $message = 'Report template updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error updating report template: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete_report_template') {
        try {
            $templateId = intval($_POST['template_id'] ?? 0);
            $dbHelper->query("DELETE FROM report_templates WHERE id = ?", [$templateId]);
            $message = 'Report template deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error deleting report template: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get analytics data
$analyticsData = $dbHelper->getAnalyticsData();
$departments = $dbHelper->getDepartments();
$employees = $dbHelper->getEmployees(100); // Get more employees for analytics
$compensationData = $dbHelper->getCompensationData();

// Get analytics metrics and report templates for CRUD operations
$analyticsMetrics = $dbHelper->fetchAll("SELECT * FROM analytics_metrics ORDER BY metric_name");
$reportTemplates = $dbHelper->fetchAll("SELECT * FROM report_templates ORDER BY template_name");

// Calculate key metrics
$totalEmployees = count($employees);
$totalDepartments = count($departments);
$avgSalary = $dbHelper->fetchOne("SELECT AVG(pe.basic_salary) as avg_salary FROM payroll_entries pe JOIN payroll_periods pp ON pe.period_id = pp.id WHERE pp.status = 'Processed' ORDER BY pp.period_end DESC LIMIT 1")['avg_salary'] ?? 0;

// Department headcount
$deptHeadcount = [];
foreach ($departments as $dept) {
    $deptHeadcount[$dept['department_name']] = $dept['employee_count'];
}

// Salary distribution based on actual salary data
$salaryRanges = [
    'Under 30k' => 0,
    '30k-50k' => 0,
    '50k-75k' => 0,
    '75k-100k' => 0,
    'Over 100k' => 0
];

foreach ($compensationData as $emp) {
    $salary = $emp['basic_salary'] ?? 0;
    if ($salary < 30000)
        $salaryRanges['Under 30k']++;
    elseif ($salary < 50000)
        $salaryRanges['30k-50k']++;
    elseif ($salary < 75000)
        $salaryRanges['50k-75k']++;
    elseif ($salary < 100000)
        $salaryRanges['75k-100k']++;
    else
        $salaryRanges['Over 100k']++;
}

// Get turnover rate from analytics data
$turnoverRate = 0;
foreach ($analyticsData as $metric) {
    if ($metric['metric_type'] === 'Turnover Rate') {
        $turnoverRate = $metric['metric_value'];
        break;
    }
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

                        <!-- Message Display -->
                        <?php if ($message): ?>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] p-4 <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'; ?>">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 <?php echo $messageType === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?>"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?php if ($messageType === 'success'): ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        <?php else: ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        <?php endif; ?>
                                    </svg>
                                    <span
                                        class="text-sm font-medium <?php echo $messageType === 'success' ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'; ?>">
                                        <?php echo htmlspecialchars($message); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Key Metrics -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Total Employees</div>
                                        <div class="text-2xl font-semibold">
                                            <?php echo number_format($totalEmployees); ?>
                                        </div>
                                        <div class="text-xs text-green-600">+5% from last month</div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Departments</div>
                                        <div class="text-2xl font-semibold"><?php echo $totalDepartments; ?></div>
                                        <div class="text-xs text-slate-500">Active departments</div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Average Salary</div>
                                        <div class="text-2xl font-semibold">₱<?php echo number_format($avgSalary, 0); ?>
                                        </div>
                                        <div class="text-xs text-green-600">+3% from last period</div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Turnover Rate</div>
                                        <div class="text-2xl font-semibold">
                                            <?php echo number_format($turnoverRate, 1); ?>%
                                        </div>
                                        <div class="text-xs text-red-600">+0.8% from last quarter</div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts Section -->
                        <div class="grid lg:grid-cols-2 gap-4">
                            <!-- Department Headcount Chart -->
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <h3 class="font-semibold mb-4">Department Headcount</h3>
                                <div class="h-64">
                                    <canvas id="headcountChart"></canvas>
                                </div>
                            </div>

                            <!-- Salary Distribution Chart -->
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <h3 class="font-semibold mb-4">Salary Distribution</h3>
                                <div class="h-64">
                                    <canvas id="salaryChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Department Analytics Table -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <h3 class="font-semibold">Department Analytics</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Department</th>
                                            <th class="text-left px-3 py-2 font-semibold">Headcount</th>
                                            <th class="text-left px-3 py-2 font-semibold">Budget</th>
                                            <th class="text-left px-3 py-2 font-semibold">Budget per Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Head</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($departments as $dept): ?>
                                            <tr
                                                class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                <td class="px-3 py-3 font-medium text-slate-900 dark:text-slate-100">
                                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex items-center gap-2">
                                                        <span
                                                            class="font-semibold"><?php echo $dept['employee_count']; ?></span>
                                                        <div class="w-16 bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                                                            <div class="bg-blue-600 h-2 rounded-full"
                                                                style="width: <?php echo min(100, ($dept['employee_count'] / max(array_column($departments, 'employee_count'))) * 100); ?>%">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    ₱<?php echo number_format($dept['budget_allocation'], 0); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    ₱<?php echo number_format($dept['budget_allocation'] / max(1, $dept['employee_count']), 0); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    <?php echo htmlspecialchars($dept['head_name'] ?? 'Not assigned'); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                                        Active
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Recent Analytics Data -->
                        <div
                            class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                            <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Recent Analytics Updates
                            </div>
                            <div class="p-4">
                                <?php if (empty($analyticsData)): ?>
                                    <div class="text-sm text-slate-500">No analytics data available</div>
                                <?php else: ?>
                                    <div class="space-y-3">
                                        <?php foreach (array_slice($analyticsData, 0, 5) as $metric): ?>
                                            <div
                                                class="flex items-center justify-between p-3 rounded-lg border border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                <div>
                                                    <div class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                                        <?php echo htmlspecialchars($metric['metric_type']); ?>
                                                    </div>
                                                    <div class="text-xs text-slate-500">
                                                        <?php echo $metric['department_name'] ? 'Department: ' . htmlspecialchars($metric['department_name']) : 'Organization-wide'; ?>
                                                        •
                                                        <?php echo date('M j, Y', strtotime($metric['calculation_date'])); ?>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                                        <?php echo number_format($metric['metric_value'], 2); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Analytics Metrics Management -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <h3 class="font-semibold">Analytics Metrics Management</h3>
                                        <span
                                            class="px-2 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400 text-xs rounded-full">
                                            <?php echo count($analyticsMetrics); ?> metrics
                                        </span>
                                    </div>
                                    <button onclick="openCreateMetricModal()"
                                        class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-3 py-1 rounded-md text-sm hover:opacity-95 transition-opacity">
                                        Add Metric
                                    </button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Metric Name</th>
                                            <th class="text-left px-3 py-2 font-semibold">Type</th>
                                            <th class="text-left px-3 py-2 font-semibold">Description</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($analyticsMetrics as $metric): ?>
                                            <tr
                                                class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                <td class="px-3 py-3 font-medium text-slate-900 dark:text-slate-100">
                                                    <?php echo htmlspecialchars($metric['metric_name']); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    <?php echo htmlspecialchars($metric['metric_type']); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    <?php echo htmlspecialchars(substr($metric['description'], 0, 50)) . (strlen($metric['description']) > 50 ? '...' : ''); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span
                                                        class="px-2 py-1 rounded-full text-xs font-medium <?php echo $metric['is_active'] ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'; ?>">
                                                        <?php echo $metric['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex items-center gap-1">
                                                        <button
                                                            onclick="openEditMetricModal(<?php echo $metric['id']; ?>, '<?php echo htmlspecialchars($metric['metric_name']); ?>', '<?php echo $metric['metric_type']; ?>', '<?php echo htmlspecialchars($metric['description']); ?>', '<?php echo htmlspecialchars($metric['formula']); ?>', <?php echo $metric['is_active']; ?>)"
                                                            class="p-1 text-slate-400 hover:text-green-600 transition-colors"
                                                            title="Edit">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                        <button
                                                            onclick="openDeleteMetricModal(<?php echo $metric['id']; ?>, '<?php echo htmlspecialchars($metric['metric_name']); ?>')"
                                                            class="p-1 text-slate-400 hover:text-red-600 transition-colors"
                                                            title="Delete">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Report Templates Management -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <h3 class="font-semibold">Report Templates Management</h3>
                                        <span
                                            class="px-2 py-1 bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400 text-xs rounded-full">
                                            <?php echo count($reportTemplates); ?> templates
                                        </span>
                                    </div>
                                    <button onclick="openCreateTemplateModal()"
                                        class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-3 py-1 rounded-md text-sm hover:opacity-95 transition-opacity">
                                        Add Template
                                    </button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Template Name</th>
                                            <th class="text-left px-3 py-2 font-semibold">Type</th>
                                            <th class="text-left px-3 py-2 font-semibold">Description</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportTemplates as $template): ?>
                                            <tr
                                                class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                <td class="px-3 py-3 font-medium text-slate-900 dark:text-slate-100">
                                                    <?php echo htmlspecialchars($template['template_name']); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    <?php echo htmlspecialchars($template['template_type']); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    <?php echo htmlspecialchars(substr($template['description'], 0, 50)) . (strlen($template['description']) > 50 ? '...' : ''); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span
                                                        class="px-2 py-1 rounded-full text-xs font-medium <?php echo $template['is_active'] ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'; ?>">
                                                        <?php echo $template['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex items-center gap-1">
                                                        <button
                                                            onclick="openEditTemplateModal(<?php echo $template['id']; ?>, '<?php echo htmlspecialchars($template['template_name']); ?>', '<?php echo htmlspecialchars($template['description']); ?>', '<?php echo $template['template_type']; ?>', '<?php echo htmlspecialchars($template['query']); ?>', <?php echo $template['is_active']; ?>)"
                                                            class="p-1 text-slate-400 hover:text-green-600 transition-colors"
                                                            title="Edit">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                        <button
                                                            onclick="openDeleteTemplateModal(<?php echo $template['id']; ?>, '<?php echo htmlspecialchars($template['template_name']); ?>')"
                                                            class="p-1 text-slate-400 hover:text-red-600 transition-colors"
                                                            title="Delete">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>

    <!-- Create Analytics Metric Modal -->
    <div id="createMetricModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Add Analytics Metric</h3>
                    <button onclick="closeCreateMetricModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create_metric">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Metric Name
                        *</label>
                    <input type="text" name="metric_name" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Metric Type
                        *</label>
                    <select name="metric_type" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Type</option>
                        <option value="Turnover Rate">Turnover Rate</option>
                        <option value="Employee Satisfaction">Employee Satisfaction</option>
                        <option value="Productivity">Productivity</option>
                        <option value="Cost per Hire">Cost per Hire</option>
                        <option value="Time to Fill">Time to Fill</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Formula</label>
                    <input type="text" name="formula" placeholder="e.g., (terminations / average_employees) * 100"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" checked class="rounded border-[hsl(var(--border))]">
                    <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCreateMetricModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Create Metric
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Analytics Metric Modal -->
    <div id="editMetricModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Edit Analytics Metric</h3>
                    <button onclick="closeEditMetricModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="update_metric">
                <input type="hidden" name="metric_id" id="edit_metric_id">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Metric Name
                        *</label>
                    <input type="text" name="metric_name" id="edit_metric_name" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Metric Type
                        *</label>
                    <select name="metric_type" id="edit_metric_type" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Type</option>
                        <option value="Turnover Rate">Turnover Rate</option>
                        <option value="Employee Satisfaction">Employee Satisfaction</option>
                        <option value="Productivity">Productivity</option>
                        <option value="Cost per Hire">Cost per Hire</option>
                        <option value="Time to Fill">Time to Fill</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" id="edit_description" rows="3"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Formula</label>
                    <input type="text" name="formula" id="edit_formula"
                        placeholder="e.g., (terminations / average_employees) * 100"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="edit_is_active"
                        class="rounded border-[hsl(var(--border))]">
                    <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditMetricModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Update Metric
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Metric Confirmation Modal -->
    <div id="deleteMetricModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Confirm Deletion</h3>
                        <p class="text-sm text-slate-500">This action cannot be undone.</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300 mb-6">
                    Are you sure you want to delete this analytics metric? This will remove it from all reports and
                    dashboards.
                </p>
                <form method="POST" id="deleteMetricForm">
                    <input type="hidden" name="action" value="delete_metric">
                    <input type="hidden" name="metric_id" id="delete_metric_id">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeDeleteMetricModal()"
                            class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            Delete Metric
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Report Template Modal -->
    <div id="createTemplateModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-lg w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Add Report Template</h3>
                    <button onclick="closeCreateTemplateModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create_report_template">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Template Name
                        *</label>
                    <input type="text" name="template_name" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Template Type
                        *</label>
                    <select name="template_type" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Type</option>
                        <option value="Employee Report">Employee Report</option>
                        <option value="Payroll Report">Payroll Report</option>
                        <option value="Performance Report">Performance Report</option>
                        <option value="Benefits Report">Benefits Report</option>
                        <option value="Analytics Report">Analytics Report</option>
                        <option value="Custom Report">Custom Report</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">SQL Query</label>
                    <textarea name="query" rows="6" placeholder="SELECT * FROM employees WHERE..."
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))] font-mono text-sm"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" checked class="rounded border-[hsl(var(--border))]">
                    <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCreateTemplateModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Create Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Report Template Modal -->
    <div id="editTemplateModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-lg w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Edit Report Template</h3>
                    <button onclick="closeEditTemplateModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="update_report_template">
                <input type="hidden" name="template_id" id="edit_template_id">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Template Name
                        *</label>
                    <input type="text" name="template_name" id="edit_template_name" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Template Type
                        *</label>
                    <select name="template_type" id="edit_template_type" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Type</option>
                        <option value="Employee Report">Employee Report</option>
                        <option value="Payroll Report">Payroll Report</option>
                        <option value="Performance Report">Performance Report</option>
                        <option value="Benefits Report">Benefits Report</option>
                        <option value="Analytics Report">Analytics Report</option>
                        <option value="Custom Report">Custom Report</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" id="edit_description" rows="3"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">SQL Query</label>
                    <textarea name="query" id="edit_query" rows="6" placeholder="SELECT * FROM employees WHERE..."
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))] font-mono text-sm"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="edit_is_active"
                        class="rounded border-[hsl(var(--border))]">
                    <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditTemplateModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Update Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Template Confirmation Modal -->
    <div id="deleteTemplateModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Confirm Deletion</h3>
                        <p class="text-sm text-slate-500">This action cannot be undone.</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300 mb-6">
                    Are you sure you want to delete this report template? This will remove it from the system
                    permanently.
                </p>
                <form method="POST" id="deleteTemplateForm">
                    <input type="hidden" name="action" value="delete_report_template">
                    <input type="hidden" name="template_id" id="delete_template_id">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeDeleteTemplateModal()"
                            class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            Delete Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        // Department Headcount Chart
        const headcountCtx = document.getElementById('headcountChart').getContext('2d');
        new Chart(headcountCtx, {
            type: 'bar',
            data: {
                labels: [<?php echo "'" . implode("','", array_column($departments, 'department_name')) . "'"; ?>],
                datasets: [{
                    label: 'Headcount',
                    data: [<?php echo implode(',', array_column($departments, 'employee_count')); ?>],
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Salary Distribution Chart
        const salaryCtx = document.getElementById('salaryChart').getContext('2d');
        new Chart(salaryCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php echo "'" . implode("','", array_keys($salaryRanges)) . "'"; ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_values($salaryRanges)); ?>],
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.5)',
                        'rgba(245, 158, 11, 0.5)',
                        'rgba(34, 197, 94, 0.5)',
                        'rgba(59, 130, 246, 0.5)',
                        'rgba(147, 51, 234, 0.5)'
                    ],
                    borderColor: [
                        'rgba(239, 68, 68, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(147, 51, 234, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Analytics Metrics Management
        function openCreateMetricModal() {
            document.getElementById('createMetricModal').classList.remove('hidden');
        }

        function closeCreateMetricModal() {
            document.getElementById('createMetricModal').classList.add('hidden');
        }

        function openEditMetricModal(metricId, metricName, metricType, description, formula, isActive) {
            document.getElementById('edit_metric_id').value = metricId;
            document.getElementById('edit_metric_name').value = metricName;
            document.getElementById('edit_metric_type').value = metricType;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_formula').value = formula;
            document.getElementById('edit_is_active').checked = isActive == 1;
            document.getElementById('editMetricModal').classList.remove('hidden');
        }

        function closeEditMetricModal() {
            document.getElementById('editMetricModal').classList.add('hidden');
        }

        function openDeleteMetricModal(metricId, metricName) {
            document.getElementById('delete_metric_id').value = metricId;
            document.getElementById('deleteMetricModal').classList.remove('hidden');
        }

        function closeDeleteMetricModal() {
            document.getElementById('deleteMetricModal').classList.add('hidden');
        }

        // Report Templates Management
        function openCreateTemplateModal() {
            document.getElementById('createTemplateModal').classList.remove('hidden');
        }

        function closeCreateTemplateModal() {
            document.getElementById('createTemplateModal').classList.add('hidden');
        }

        function openEditTemplateModal(templateId, templateName, description, templateType, query, isActive) {
            document.getElementById('edit_template_id').value = templateId;
            document.getElementById('edit_template_name').value = templateName;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_template_type').value = templateType;
            document.getElementById('edit_query').value = query;
            document.getElementById('edit_is_active').checked = isActive == 1;
            document.getElementById('editTemplateModal').classList.remove('hidden');
        }

        function closeEditTemplateModal() {
            document.getElementById('editTemplateModal').classList.add('hidden');
        }

        function openDeleteTemplateModal(templateId, templateName) {
            document.getElementById('delete_template_id').value = templateId;
            document.getElementById('deleteTemplateModal').classList.remove('hidden');
        }

        function closeDeleteTemplateModal() {
            document.getElementById('deleteTemplateModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('fixed')) {
                closeCreateMetricModal();
                closeEditMetricModal();
                closeDeleteMetricModal();
                closeCreateTemplateModal();
                closeEditTemplateModal();
                closeDeleteTemplateModal();
            }
        });
    </script>
</body>

</html>