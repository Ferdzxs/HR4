<?php
// HR Manager Compensation Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../shared/database_helper.php';
include_once __DIR__ . '/../../routing/rbac.php';

$activeId = 'compensation';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Initialize database helper
$dbHelper = new DatabaseHelper();

// Handle CRUD operations
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_salary_grade') {
        try {
            $gradeLevel = $_POST['grade_level'] ?? '';
            $minSalary = floatval($_POST['min_salary'] ?? 0);
            $maxSalary = floatval($_POST['max_salary'] ?? 0);

            $dbHelper->query("
                INSERT INTO salary_grades (grade_level, min_salary, max_salary) 
                VALUES (?, ?, ?)
            ", [$gradeLevel, $minSalary, $maxSalary]);

            $message = 'Salary grade created successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error creating salary grade: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'update_salary_grade') {
        try {
            $gradeId = intval($_POST['grade_id'] ?? 0);
            $gradeLevel = $_POST['grade_level'] ?? '';
            $minSalary = floatval($_POST['min_salary'] ?? 0);
            $maxSalary = floatval($_POST['max_salary'] ?? 0);

            $dbHelper->query("
                UPDATE salary_grades 
                SET grade_level = ?, min_salary = ?, max_salary = ?
                WHERE id = ?
            ", [$gradeLevel, $minSalary, $maxSalary, $gradeId]);

            $message = 'Salary grade updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error updating salary grade: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete_salary_grade') {
        try {
            $gradeId = intval($_POST['grade_id'] ?? 0);
            $dbHelper->query("DELETE FROM salary_grades WHERE id = ?", [$gradeId]);
            $message = 'Salary grade deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error deleting salary grade: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'create_position') {
        try {
            $positionTitle = $_POST['position_title'] ?? '';
            $salaryGradeId = intval($_POST['salary_grade_id'] ?? 0);
            $jobDescription = $_POST['job_description'] ?? '';
            $reportsToPositionId = intval($_POST['reports_to_position_id'] ?? 0);

            $dbHelper->query("
                INSERT INTO positions (position_title, salary_grade_id, job_description, reports_to_position_id) 
                VALUES (?, ?, ?, ?)
            ", [$positionTitle, $salaryGradeId, $jobDescription, $reportsToPositionId ?: null]);

            $message = 'Position created successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error creating position: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'update_position') {
        try {
            $positionId = intval($_POST['position_id'] ?? 0);
            $positionTitle = $_POST['position_title'] ?? '';
            $salaryGradeId = intval($_POST['salary_grade_id'] ?? 0);
            $jobDescription = $_POST['job_description'] ?? '';
            $reportsToPositionId = intval($_POST['reports_to_position_id'] ?? 0);

            $dbHelper->query("
                UPDATE positions 
                SET position_title = ?, salary_grade_id = ?, job_description = ?, reports_to_position_id = ?
                WHERE id = ?
            ", [$positionTitle, $salaryGradeId, $jobDescription, $reportsToPositionId ?: null, $positionId]);

            $message = 'Position updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error updating position: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete_position') {
        try {
            $positionId = intval($_POST['position_id'] ?? 0);
            $dbHelper->query("DELETE FROM positions WHERE id = ?", [$positionId]);
            $message = 'Position deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error deleting position: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get compensation data
$compensationData = $dbHelper->getCompensationData();
$departments = $dbHelper->getDepartments();
$salaryGrades = $dbHelper->fetchAll("SELECT * FROM salary_grades ORDER BY grade_level");
$budgetUtilization = $dbHelper->getDepartmentBudgetUtilization();

// Get positions for CRUD operations
$positions = $dbHelper->fetchAll("
    SELECT p.*, sg.grade_level, sg.min_salary, sg.max_salary,
           p2.position_title as reports_to_title
    FROM positions p
    JOIN salary_grades sg ON p.salary_grade_id = sg.id
    LEFT JOIN positions p2 ON p.reports_to_position_id = p2.id
    ORDER BY p.position_title
");

// Calculate compensation metrics
$totalBudget = array_sum(array_column($departments, 'budget_allocation'));
$totalSalary = array_sum(array_column($compensationData, 'basic_salary'));
$budgetUtilizationPercent = $totalBudget > 0 ? ($totalSalary / $totalBudget) * 100 : 0;
$avgSalary = count($compensationData) > 0 ? array_sum(array_column($compensationData, 'basic_salary')) / count($compensationData) : 0;

// Get salary distribution by grade
$salaryDistribution = [];
foreach ($salaryGrades as $grade) {
    $count = count(array_filter($compensationData, function ($emp) use ($grade) {
        return $emp['grade_level'] === $grade['grade_level'];
    }));
    $salaryDistribution[$grade['grade_level']] = $count;
}

// Get pending approvals (using audit logs as proxy)
$pendingApprovals = $dbHelper->fetchAll("
    SELECT COUNT(*) as count 
    FROM audit_logs 
    WHERE action_type IN ('salary_increase', 'promotion', 'bonus_approval') 
    AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
")[0]['count'] ?? 0;

// Get recent compensation activities
$recentActivities = $dbHelper->getRecentActivities(10);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Compensation</title>
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
                            <h1 class="text-lg font-semibold">Compensation Planning</h1>
                            <p class="text-xs text-slate-500 mt-1">Budgets, increases, equity, and approvals</p>
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
                        <!-- Compensation Stats -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Total Budget</div>
                                        <div class="text-2xl font-semibold">
                                            ₱<?php echo number_format($totalBudget, 0); ?></div>
                                        <div class="text-xs text-slate-500">All departments</div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
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
                                        <div class="text-xs text-slate-500 mb-1">Budget Utilization</div>
                                        <div class="text-2xl font-semibold">
                                            <?php echo number_format($budgetUtilizationPercent, 1); ?>%
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            ₱<?php echo number_format($totalSalary, 0); ?> used</div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Pending Approvals</div>
                                        <div class="text-2xl font-semibold"><?php echo $pendingApprovals; ?></div>
                                        <div class="text-xs text-slate-500">Last 30 days</div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
                                        <div class="text-xs text-slate-500">Across all employees</div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Department Budget Analysis -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <h3 class="font-semibold">Department Budget Analysis</h3>
                                        <span
                                            class="px-2 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400 text-xs rounded-full">
                                            <?php echo count($departments); ?> departments
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        <select
                                            class="px-3 py-1 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] text-sm">
                                            <option>All Departments</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?php echo $dept['id']; ?>">
                                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button
                                            class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-3 py-1 rounded-md text-sm hover:opacity-95 transition-opacity">
                                            Export Report
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Department</th>
                                            <th class="text-left px-3 py-2 font-semibold">Budget Allocation</th>
                                            <th class="text-left px-3 py-2 font-semibold">Current Salary</th>
                                            <th class="text-left px-3 py-2 font-semibold">Utilization</th>
                                            <th class="text-left px-3 py-2 font-semibold">Remaining</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($budgetUtilization)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="6">
                                                    <div class="text-center py-10">
                                                        <div class="text-sm font-medium">No budget data</div>
                                                        <div class="text-xs text-slate-500 mt-1">Budget allocation data not
                                                            available.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($budgetUtilization as $dept): ?>
                                                <tr
                                                    class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                    <td class="px-3 py-3">
                                                        <div class="font-medium text-slate-900 dark:text-slate-100">
                                                            <?php echo htmlspecialchars($dept['department_name']); ?>
                                                        </div>
                                                        <div class="text-xs text-slate-500">
                                                            <?php echo $dept['employee_count']; ?> employees
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        ₱<?php echo number_format($dept['budget_allocation'], 0); ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        ₱<?php echo number_format($dept['total_salary'] ?? 0, 0); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-16 bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                                                                <div class="bg-blue-600 h-2 rounded-full"
                                                                    style="width: <?php echo min(100, $dept['utilization_percentage'] ?? 0); ?>%">
                                                                </div>
                                                            </div>
                                                            <span class="text-xs font-medium">
                                                                <?php echo number_format($dept['utilization_percentage'] ?? 0, 1); ?>%
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        ₱<?php echo number_format(($dept['budget_allocation'] ?? 0) - ($dept['total_salary'] ?? 0), 0); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php
                                                        $utilization = $dept['utilization_percentage'] ?? 0;
                                                        $statusClass = $utilization > 90 ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' :
                                                            ($utilization > 75 ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400' :
                                                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400');
                                                        $statusText = $utilization > 90 ? 'Over Budget' : ($utilization > 75 ? 'High Usage' : 'Normal');
                                                        ?>
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                                            <?php echo $statusText; ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Salary Grade Distribution -->
                        <div class="grid lg:grid-cols-2 gap-4">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Salary Grade
                                    Distribution</div>
                                <div class="p-4">
                                    <?php if (empty($salaryDistribution)): ?>
                                        <div class="text-sm text-slate-500">No salary data available</div>
                                    <?php else: ?>
                                        <div class="space-y-3">
                                            <?php foreach ($salaryDistribution as $grade => $count): ?>
                                                <div
                                                    class="flex items-center justify-between p-3 rounded-lg border border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                    <div class="font-medium text-slate-900 dark:text-slate-100">
                                                        <?php echo htmlspecialchars($grade); ?>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-16 bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                                                            <div class="bg-blue-600 h-2 rounded-full"
                                                                style="width: <?php echo min(100, ($count / max(array_values($salaryDistribution))) * 100); ?>%">
                                                            </div>
                                                        </div>
                                                        <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                                            <?php echo $count; ?> employees
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Compensation Summary
                                </div>
                                <div class="p-4">
                                    <div class="space-y-4">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-slate-600 dark:text-slate-300">Total
                                                Employees</span>
                                            <span class="font-semibold"><?php echo count($compensationData); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-slate-600 dark:text-slate-300">Total Salary
                                                Cost</span>
                                            <span
                                                class="font-semibold">₱<?php echo number_format($totalSalary, 0); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-slate-600 dark:text-slate-300">Average
                                                Salary</span>
                                            <span
                                                class="font-semibold">₱<?php echo number_format($avgSalary, 0); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-slate-600 dark:text-slate-300">Budget
                                                Utilization</span>
                                            <span
                                                class="font-semibold text-blue-600"><?php echo number_format($budgetUtilizationPercent, 1); ?>%</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-slate-600 dark:text-slate-300">Remaining
                                                Budget</span>
                                            <span
                                                class="font-semibold text-green-600">₱<?php echo number_format($totalBudget - $totalSalary, 0); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Salary Grades Management -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <h3 class="font-semibold">Salary Grades Management</h3>
                                        <span
                                            class="px-2 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400 text-xs rounded-full">
                                            <?php echo count($salaryGrades); ?> grades
                                        </span>
                                    </div>
                                    <button onclick="openCreateGradeModal()"
                                        class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-3 py-1 rounded-md text-sm hover:opacity-95 transition-opacity">
                                        Add Grade
                                    </button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Grade Level</th>
                                            <th class="text-left px-3 py-2 font-semibold">Min Salary</th>
                                            <th class="text-left px-3 py-2 font-semibold">Max Salary</th>
                                            <th class="text-left px-3 py-2 font-semibold">Range</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($salaryGrades as $grade): ?>
                                            <tr
                                                class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                <td class="px-3 py-3 font-medium text-slate-900 dark:text-slate-100">
                                                    <?php echo htmlspecialchars($grade['grade_level']); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    ₱<?php echo number_format($grade['min_salary'], 2); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    ₱<?php echo number_format($grade['max_salary'], 2); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    ₱<?php echo number_format($grade['max_salary'] - $grade['min_salary'], 2); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex items-center gap-1">
                                                        <button
                                                            onclick="openEditGradeModal(<?php echo $grade['id']; ?>, '<?php echo htmlspecialchars($grade['grade_level']); ?>', <?php echo $grade['min_salary']; ?>, <?php echo $grade['max_salary']; ?>)"
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
                                                            onclick="openDeleteGradeModal(<?php echo $grade['id']; ?>, '<?php echo htmlspecialchars($grade['grade_level']); ?>')"
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

                        <!-- Positions Management -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <h3 class="font-semibold">Positions Management</h3>
                                        <span
                                            class="px-2 py-1 bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400 text-xs rounded-full">
                                            <?php echo count($positions); ?> positions
                                        </span>
                                    </div>
                                    <button onclick="openCreatePositionModal()"
                                        class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-3 py-1 rounded-md text-sm hover:opacity-95 transition-opacity">
                                        Add Position
                                    </button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Position Title</th>
                                            <th class="text-left px-3 py-2 font-semibold">Salary Grade</th>
                                            <th class="text-left px-3 py-2 font-semibold">Salary Range</th>
                                            <th class="text-left px-3 py-2 font-semibold">Reports To</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($positions as $position): ?>
                                            <tr
                                                class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium text-slate-900 dark:text-slate-100">
                                                        <?php echo htmlspecialchars($position['position_title']); ?>
                                                    </div>
                                                    <div class="text-xs text-slate-500 truncate max-w-xs"
                                                        title="<?php echo htmlspecialchars($position['job_description']); ?>">
                                                        <?php echo htmlspecialchars(substr($position['job_description'], 0, 50)) . (strlen($position['job_description']) > 50 ? '...' : ''); ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    <?php echo htmlspecialchars($position['grade_level']); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    ₱<?php echo number_format($position['min_salary'], 0); ?> -
                                                    ₱<?php echo number_format($position['max_salary'], 0); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    <?php echo htmlspecialchars($position['reports_to_title'] ?? 'N/A'); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex items-center gap-1">
                                                        <button
                                                            onclick="openEditPositionModal(<?php echo $position['id']; ?>, '<?php echo htmlspecialchars($position['position_title']); ?>', <?php echo $position['salary_grade_id']; ?>, '<?php echo htmlspecialchars($position['job_description']); ?>', <?php echo $position['reports_to_position_id'] ?: 'null'; ?>)"
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
                                                            onclick="openDeletePositionModal(<?php echo $position['id']; ?>, '<?php echo htmlspecialchars($position['position_title']); ?>')"
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

    <!-- Create Salary Grade Modal -->
    <div id="createGradeModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Add Salary Grade</h3>
                    <button onclick="closeCreateGradeModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create_salary_grade">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Grade Level
                        *</label>
                    <input type="text" name="grade_level" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Min Salary
                            *</label>
                        <input type="number" name="min_salary" step="0.01" min="0" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Max Salary
                            *</label>
                        <input type="number" name="max_salary" step="0.01" min="0" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCreateGradeModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Create Grade
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Salary Grade Modal -->
    <div id="editGradeModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Edit Salary Grade</h3>
                    <button onclick="closeEditGradeModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="update_salary_grade">
                <input type="hidden" name="grade_id" id="edit_grade_id">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Grade Level
                        *</label>
                    <input type="text" name="grade_level" id="edit_grade_level" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Min Salary
                            *</label>
                        <input type="number" name="min_salary" id="edit_min_salary" step="0.01" min="0" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Max Salary
                            *</label>
                        <input type="number" name="max_salary" id="edit_max_salary" step="0.01" min="0" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditGradeModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Update Grade
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Grade Confirmation Modal -->
    <div id="deleteGradeModal"
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
                    Are you sure you want to delete this salary grade? This will affect all positions using this grade.
                </p>
                <form method="POST" id="deleteGradeForm">
                    <input type="hidden" name="action" value="delete_salary_grade">
                    <input type="hidden" name="grade_id" id="delete_grade_id">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeDeleteGradeModal()"
                            class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            Delete Grade
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Position Modal -->
    <div id="createPositionModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-lg w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Add Position</h3>
                    <button onclick="closeCreatePositionModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create_position">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Position Title
                        *</label>
                    <input type="text" name="position_title" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Salary Grade
                        *</label>
                    <select name="salary_grade_id" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Grade</option>
                        <?php foreach ($salaryGrades as $grade): ?>
                            <option value="<?php echo $grade['id']; ?>">
                                <?php echo htmlspecialchars($grade['grade_level']); ?>
                                (₱<?php echo number_format($grade['min_salary'], 0); ?> -
                                ₱<?php echo number_format($grade['max_salary'], 0); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Reports To
                        Position</label>
                    <select name="reports_to_position_id"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Position (Optional)</option>
                        <?php foreach ($positions as $pos): ?>
                            <option value="<?php echo $pos['id']; ?>">
                                <?php echo htmlspecialchars($pos['position_title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Job Description
                        *</label>
                    <textarea name="job_description" rows="4" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCreatePositionModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Create Position
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Position Modal -->
    <div id="editPositionModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-lg w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Edit Position</h3>
                    <button onclick="closeEditPositionModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="update_position">
                <input type="hidden" name="position_id" id="edit_position_id">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Position Title
                        *</label>
                    <input type="text" name="position_title" id="edit_position_title" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Salary Grade
                        *</label>
                    <select name="salary_grade_id" id="edit_salary_grade_id" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Grade</option>
                        <?php foreach ($salaryGrades as $grade): ?>
                            <option value="<?php echo $grade['id']; ?>">
                                <?php echo htmlspecialchars($grade['grade_level']); ?>
                                (₱<?php echo number_format($grade['min_salary'], 0); ?> -
                                ₱<?php echo number_format($grade['max_salary'], 0); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Reports To
                        Position</label>
                    <select name="reports_to_position_id" id="edit_reports_to_position_id"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Position (Optional)</option>
                        <?php foreach ($positions as $pos): ?>
                            <option value="<?php echo $pos['id']; ?>">
                                <?php echo htmlspecialchars($pos['position_title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Job Description
                        *</label>
                    <textarea name="job_description" id="edit_job_description" rows="4" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditPositionModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Update Position
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Position Confirmation Modal -->
    <div id="deletePositionModal"
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
                    Are you sure you want to delete this position? This will affect all employees assigned to this
                    position.
                </p>
                <form method="POST" id="deletePositionForm">
                    <input type="hidden" name="action" value="delete_position">
                    <input type="hidden" name="position_id" id="delete_position_id">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeDeletePositionModal()"
                            class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            Delete Position
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        // Grade Modal functions
        function openCreateGradeModal() {
            document.getElementById('createGradeModal').classList.remove('hidden');
        }

        function closeCreateGradeModal() {
            document.getElementById('createGradeModal').classList.add('hidden');
        }

        function openEditGradeModal(gradeId, gradeLevel, minSalary, maxSalary) {
            document.getElementById('edit_grade_id').value = gradeId;
            document.getElementById('edit_grade_level').value = gradeLevel;
            document.getElementById('edit_min_salary').value = minSalary;
            document.getElementById('edit_max_salary').value = maxSalary;
            document.getElementById('editGradeModal').classList.remove('hidden');
        }

        function closeEditGradeModal() {
            document.getElementById('editGradeModal').classList.add('hidden');
        }

        function openDeleteGradeModal(gradeId, gradeLevel) {
            document.getElementById('delete_grade_id').value = gradeId;
            document.getElementById('deleteGradeModal').classList.remove('hidden');
        }

        function closeDeleteGradeModal() {
            document.getElementById('deleteGradeModal').classList.add('hidden');
        }

        // Position Modal functions
        function openCreatePositionModal() {
            document.getElementById('createPositionModal').classList.remove('hidden');
        }

        function closeCreatePositionModal() {
            document.getElementById('createPositionModal').classList.add('hidden');
        }

        function openEditPositionModal(positionId, positionTitle, salaryGradeId, jobDescription, reportsToPositionId) {
            document.getElementById('edit_position_id').value = positionId;
            document.getElementById('edit_position_title').value = positionTitle;
            document.getElementById('edit_salary_grade_id').value = salaryGradeId;
            document.getElementById('edit_job_description').value = jobDescription;
            document.getElementById('edit_reports_to_position_id').value = reportsToPositionId || '';
            document.getElementById('editPositionModal').classList.remove('hidden');
        }

        function closeEditPositionModal() {
            document.getElementById('editPositionModal').classList.add('hidden');
        }

        function openDeletePositionModal(positionId, positionTitle) {
            document.getElementById('delete_position_id').value = positionId;
            document.getElementById('deletePositionModal').classList.remove('hidden');
        }

        function closeDeletePositionModal() {
            document.getElementById('deletePositionModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('fixed')) {
                closeCreateGradeModal();
                closeEditGradeModal();
                closeDeleteGradeModal();
                closeCreatePositionModal();
                closeEditPositionModal();
                closeDeletePositionModal();
            }
        });
    </script>
</body>

</html>