<?php
// HR Manager Compensation Planning Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../routing/rbac.php';
include_once __DIR__ . '/../../config/database.php';

$activeId = 'compensation';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_salary_component':
                try {
                    $stmt = $pdo->prepare("INSERT INTO salary_components (employee_id, component_type, amount, effective_date) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['employee_id'],
                        $_POST['component_type'],
                        $_POST['amount'],
                        $_POST['effective_date']
                    ]);
                    $success = "Salary component added successfully!";
                } catch (PDOException $e) {
                    $error = "Error adding salary component: " . $e->getMessage();
                }
                break;

            case 'update_salary_grade':
                try {
                    $stmt = $pdo->prepare("UPDATE salary_grades SET min_salary = ?, max_salary = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['min_salary'],
                        $_POST['max_salary'],
                        $_POST['grade_id']
                    ]);
                    $success = "Salary grade updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating salary grade: " . $e->getMessage();
                }
                break;
        }
    }
}

// Fetch compensation data
try {
    // Salary grades
    $stmt = $pdo->query("SELECT * FROM salary_grades ORDER BY grade_level");
    $salaryGrades = $stmt->fetchAll();

    // Positions with salary grades
    $stmt = $pdo->query("SELECT p.*, sg.grade_level, sg.min_salary, sg.max_salary, COUNT(e.id) as employee_count
                        FROM positions p
                        LEFT JOIN salary_grades sg ON p.salary_grade_id = sg.id
                        LEFT JOIN employees e ON p.id = e.position_id AND e.status = 'Active'
                        GROUP BY p.id
                        ORDER BY sg.grade_level, p.position_title");
    $positions = $stmt->fetchAll();

    // Recent salary components
    $stmt = $pdo->query("SELECT sc.*, e.first_name, e.last_name, e.employee_number, d.department_name
                        FROM salary_components sc
                        JOIN employees e ON sc.employee_id = e.id
                        LEFT JOIN departments d ON e.department_id = d.id
                        ORDER BY sc.effective_date DESC, sc.created_at DESC
                        LIMIT 20");
    $recentComponents = $stmt->fetchAll();

    // Department budget allocation
    $stmt = $pdo->query("SELECT d.department_name, d.budget_allocation, 
                        COUNT(e.id) as employee_count,
                        SUM(sg.min_salary + sg.max_salary) / 2 as total_salary_budget,
                        AVG(sg.min_salary + sg.max_salary) / 2 as avg_salary
                        FROM departments d
                        LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'Active'
                        LEFT JOIN positions p ON e.position_id = p.id
                        LEFT JOIN salary_grades sg ON p.salary_grade_id = sg.id
                        GROUP BY d.id
                        ORDER BY d.department_name");
    $departmentBudgets = $stmt->fetchAll();

    // Equity tracking (simulated data)
    $equityData = $pdo->query("SELECT 
                              e.first_name, e.last_name, e.employee_number, d.department_name,
                              p.position_title, sg.grade_level,
                              (sg.min_salary + sg.max_salary) / 2 as current_salary,
                              (sg.min_salary + sg.max_salary) / 2 * 0.1 as equity_value
                              FROM employees e
                              LEFT JOIN departments d ON e.department_id = d.id
                              LEFT JOIN positions p ON e.position_id = p.id
                              LEFT JOIN salary_grades sg ON p.salary_grade_id = sg.id
                              WHERE e.status = 'Active'
                              ORDER BY equity_value DESC
                              LIMIT 20")->fetchAll();

    // Compensation statistics
    $totalSalaryBudget = $pdo->query("SELECT SUM(sg.max_salary * COUNT(e.id)) FROM salary_grades sg 
                                      LEFT JOIN positions p ON sg.id = p.salary_grade_id 
                                      LEFT JOIN employees e ON p.id = e.position_id AND e.status = 'Active'
                                      GROUP BY sg.id")->fetchColumn();

    $averageSalary = $pdo->query("SELECT AVG(sg.min_salary + sg.max_salary) / 2 FROM salary_grades sg 
                                 JOIN positions p ON sg.id = p.salary_grade_id 
                                 JOIN employees e ON p.id = e.position_id AND e.status = 'Active'")->fetchColumn();

    $totalComponents = $pdo->query("SELECT COUNT(*) FROM salary_components")->fetchColumn();

    $pendingApprovals = $pdo->query("SELECT COUNT(*) FROM salary_components WHERE effective_date > CURDATE()")->fetchColumn();

    // Budget utilization
    $totalBudgetAllocated = array_sum(array_column($departmentBudgets, 'budget_allocation'));
    $totalSalarySpent = array_sum(array_column($departmentBudgets, 'total_salary_budget'));
    $budgetUtilization = $totalBudgetAllocated > 0 ? ($totalSalarySpent / $totalBudgetAllocated) * 100 : 0;

} catch (PDOException $e) {
    $salaryGrades = [];
    $positions = [];
    $recentComponents = [];
    $departmentBudgets = [];
    $equityData = [];
    $totalSalaryBudget = $averageSalary = $totalComponents = $pendingApprovals = 0;
    $totalBudgetAllocated = $totalSalarySpent = $budgetUtilization = 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Compensation Planning</title>
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

                        <?php if (isset($success)): ?>
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Quick Actions -->
                        <div class="flex flex-wrap gap-2">
                            <button onclick="openAddComponentModal()"
                                class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Add Salary Component
                            </button>
                            <button onclick="openBudgetPlanning()"
                                class="bg-purple-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Budget Planning
                            </button>
                            <button onclick="openEquityManagement()"
                                class="bg-green-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Equity Management
                            </button>
                            <button onclick="openCompensationAnalysis()"
                                class="bg-blue-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Analysis
                            </button>
                            <button onclick="openSalaryReviewModal()"
                                class="bg-orange-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Salary Review
                            </button>
                            <button onclick="exportCompensation()"
                                class="bg-slate-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Export Data
                            </button>
                        </div>

                        <!-- Compensation Statistics -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Total Salary Budget</div>
                                <div class="text-2xl font-semibold">₱<?php echo number_format($totalSalaryBudget, 0); ?>
                                </div>
                                <div class="text-xs text-blue-600 mt-1">Annual budget</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Average Salary</div>
                                <div class="text-2xl font-semibold">₱<?php echo number_format($averageSalary, 0); ?>
                                </div>
                                <div class="text-xs text-green-600 mt-1">Per employee</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Salary Components</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($totalComponents); ?></div>
                                <div class="text-xs text-purple-600 mt-1">Active components</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Pending Approvals</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($pendingApprovals); ?>
                                </div>
                                <div class="text-xs text-orange-600 mt-1">Awaiting review</div>
                            </div>
                        </div>

                        <!-- Budget Management -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Department Budget Management</div>
                                    <div class="text-sm text-slate-500">Budget utilization:
                                        <?php echo number_format($budgetUtilization, 1); ?>%</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Department</th>
                                            <th class="text-left px-3 py-2 font-semibold">Allocated Budget</th>
                                            <th class="text-left px-3 py-2 font-semibold">Salary Spent</th>
                                            <th class="text-left px-3 py-2 font-semibold">Remaining</th>
                                            <th class="text-left px-3 py-2 font-semibold">Utilization</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($departmentBudgets as $budget): ?>
                                            <?php
                                            $remaining = $budget['budget_allocation'] - $budget['total_salary_budget'];
                                            $utilization = $budget['budget_allocation'] > 0 ? ($budget['total_salary_budget'] / $budget['budget_allocation']) * 100 : 0;
                                            ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($budget['department_name']); ?></div>
                                                    <div class="text-xs text-gray-500">
                                                        <?php echo $budget['employee_count']; ?> employees</div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    ₱<?php echo number_format($budget['budget_allocation'], 0); ?></td>
                                                <td class="px-3 py-3">
                                                    ₱<?php echo number_format($budget['total_salary_budget'], 0); ?></td>
                                                <td class="px-3 py-3">
                                                    <span
                                                        class="<?php echo $remaining >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                                        ₱<?php echo number_format($remaining, 0); ?>
                                                    </span>
                                                </td>
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
                                                <td class="px-3 py-3">
                                                    <div class="flex gap-1">
                                                        <button
                                                            onclick="adjustBudget('<?php echo $budget['department_name']; ?>')"
                                                            class="text-blue-600 hover:text-blue-800 text-xs">Adjust</button>
                                                        <button
                                                            onclick="viewBudgetDetails('<?php echo $budget['department_name']; ?>')"
                                                            class="text-green-600 hover:text-green-800 text-xs">Details</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Equity Tracking -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Equity Tracking</div>
                                    <div class="text-sm text-slate-500">Top 20 employees by equity value</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Position</th>
                                            <th class="text-left px-3 py-2 font-semibold">Current Salary</th>
                                            <th class="text-left px-3 py-2 font-semibold">Equity Value</th>
                                            <th class="text-left px-3 py-2 font-semibold">Equity %</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($equityData as $equity): ?>
                                            <?php $equityPercentage = $equity['current_salary'] > 0 ? ($equity['equity_value'] / $equity['current_salary']) * 100 : 0; ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($equity['first_name'] . ' ' . $equity['last_name']); ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        <?php echo htmlspecialchars($equity['employee_number']); ?></div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($equity['position_title']); ?></div>
                                                    <div class="text-xs text-gray-500">
                                                        <?php echo htmlspecialchars($equity['department_name']); ?></div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    ₱<?php echo number_format($equity['current_salary'], 0); ?></td>
                                                <td class="px-3 py-3">
                                                    <span
                                                        class="font-medium text-green-600">₱<?php echo number_format($equity['equity_value'], 0); ?></span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span
                                                        class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                                        <?php echo number_format($equityPercentage, 1); ?>%
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex gap-1">
                                                        <button
                                                            onclick="adjustEquity(<?php echo $equity['employee_number']; ?>)"
                                                            class="text-blue-600 hover:text-blue-800 text-xs">Adjust</button>
                                                        <button
                                                            onclick="viewEquityHistory(<?php echo $equity['employee_number']; ?>)"
                                                            class="text-green-600 hover:text-green-800 text-xs">History</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Salary Grades -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Salary Grades</div>
                                    <div class="text-sm text-slate-500"><?php echo count($salaryGrades); ?> grades</div>
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
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($grade['grade_level']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">₱<?php echo number_format($grade['min_salary'], 0); ?>
                                                </td>
                                                <td class="px-3 py-3">₱<?php echo number_format($grade['max_salary'], 0); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                        ₱<?php echo number_format($grade['max_salary'] - $grade['min_salary'], 0); ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex gap-1">
                                                        <button onclick="editSalaryGrade(<?php echo $grade['id']; ?>)"
                                                            class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                                                        <button onclick="viewGradePositions(<?php echo $grade['id']; ?>)"
                                                            class="text-green-600 hover:text-green-800 text-xs">Positions</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Positions by Grade -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Positions by Salary Grade</div>
                                    <div class="text-sm text-slate-500"><?php echo count($positions); ?> positions</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Position</th>
                                            <th class="text-left px-3 py-2 font-semibold">Grade</th>
                                            <th class="text-left px-3 py-2 font-semibold">Salary Range</th>
                                            <th class="text-left px-3 py-2 font-semibold">Employees</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($positions as $position): ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($position['position_title']); ?>
                                                    </div>
                                                    <div class="text-xs text-slate-500">
                                                        <?php echo htmlspecialchars(substr($position['job_description'], 0, 50)) . '...'; ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span
                                                        class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded-full">
                                                        <?php echo htmlspecialchars($position['grade_level'] ?? 'N/A'); ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <?php if ($position['min_salary'] && $position['max_salary']): ?>
                                                        ₱<?php echo number_format($position['min_salary'], 0); ?> -
                                                        ₱<?php echo number_format($position['max_salary'], 0); ?>
                                                    <?php else: ?>
                                                        Not set
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                        <?php echo $position['employee_count']; ?> employees
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex gap-1">
                                                        <button onclick="editPosition(<?php echo $position['id']; ?>)"
                                                            class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                                                        <button
                                                            onclick="viewPositionDetails(<?php echo $position['id']; ?>)"
                                                            class="text-green-600 hover:text-green-800 text-xs">View</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Recent Salary Components -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Recent Salary Components</div>
                                    <div class="text-sm text-slate-500">Last 20 changes</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Department</th>
                                            <th class="text-left px-3 py-2 font-semibold">Component Type</th>
                                            <th class="text-left px-3 py-2 font-semibold">Amount</th>
                                            <th class="text-left px-3 py-2 font-semibold">Effective Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentComponents)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="5">
                                                    <div
                                                        class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
                                                        <div class="text-sm font-medium">No salary components</div>
                                                        <div class="text-xs text-slate-500 mt-1">Add salary components to
                                                            track compensation changes.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recentComponents as $component): ?>
                                                <tr
                                                    class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                    <td class="px-3 py-3">
                                                        <div>
                                                            <div class="font-medium">
                                                                <?php echo htmlspecialchars($component['first_name'] . ' ' . $component['last_name']); ?>
                                                            </div>
                                                            <div class="text-xs text-slate-500">
                                                                <?php echo htmlspecialchars($component['employee_number']); ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php echo htmlspecialchars($component['department_name'] ?? 'N/A'); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span class="px-2 py-1 text-xs rounded-full <?php
                                                        echo $component['component_type'] === 'Bonus' ? 'bg-green-100 text-green-800' :
                                                            ($component['component_type'] === 'Allowance' ? 'bg-blue-100 text-blue-800' :
                                                                ($component['component_type'] === 'HazardPay' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'));
                                                        ?>">
                                                            <?php echo htmlspecialchars($component['component_type']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="font-semibold">
                                                            ₱<?php echo number_format($component['amount'], 2); ?></div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php echo date('M j, Y', strtotime($component['effective_date'])); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>

    <!-- Add Salary Component Modal -->
    <div id="addComponentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Add Salary Component</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_salary_component">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Employee</label>
                            <select name="employee_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Employee</option>
                                <?php
                                $stmt = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as full_name, employee_number FROM employees WHERE status = 'Active' ORDER BY first_name, last_name");
                                $employees = $stmt->fetchAll();
                                foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>">
                                        <?php echo htmlspecialchars($emp['full_name'] . ' (' . $emp['employee_number'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Component Type</label>
                            <select name="component_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Type</option>
                                <option value="Allowance">Allowance</option>
                                <option value="Bonus">Bonus</option>
                                <option value="HazardPay">Hazard Pay</option>
                                <option value="LoanDeduction">Loan Deduction</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Amount</label>
                            <input type="number" name="amount" step="0.01" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Effective Date</label>
                            <input type="date" name="effective_date" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Add
                            Component</button>
                        <button type="button" onclick="closeAddComponentModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        function openAddComponentModal() {
            document.getElementById('addComponentModal').classList.remove('hidden');
        }

        function closeAddComponentModal() {
            document.getElementById('addComponentModal').classList.add('hidden');
        }

        function openSalaryReviewModal() {
            alert('Salary review functionality coming soon');
        }

        function editSalaryGrade(id) {
            alert('Edit salary grade ' + id);
        }

        function viewGradePositions(id) {
            alert('View positions for grade ' + id);
        }

        function editPosition(id) {
            alert('Edit position ' + id);
        }

        function viewPositionDetails(id) {
            alert('View position details ' + id);
        }

        function exportCompensation() {
            alert('Export compensation functionality coming soon');
        }

        // Budget Management Functions
        function adjustBudget(departmentName) {
            const newBudget = prompt(`Adjust budget for ${departmentName}:`, '');
            if (newBudget && !isNaN(newBudget)) {
                alert(`Budget adjustment for ${departmentName}: ₱${parseFloat(newBudget).toLocaleString()}`);
                // This would submit a form to update the budget
            }
        }

        function viewBudgetDetails(departmentName) {
            alert(`Budget details for ${departmentName} - This would show detailed budget breakdown and history`);
        }

        // Equity Management Functions
        function adjustEquity(employeeNumber) {
            const newEquity = prompt(`Adjust equity for employee ${employeeNumber}:`, '');
            if (newEquity && !isNaN(newEquity)) {
                alert(`Equity adjustment for employee ${employeeNumber}: ₱${parseFloat(newEquity).toLocaleString()}`);
                // This would submit a form to update equity
            }
        }

        function viewEquityHistory(employeeNumber) {
            alert(`Equity history for employee ${employeeNumber} - This would show equity changes over time`);
        }

        // Enhanced Quick Actions
        function openBudgetPlanning() {
            alert('Budget planning functionality - Create annual compensation budgets and allocations');
        }

        function openEquityManagement() {
            alert('Equity management functionality - Manage employee equity programs and vesting schedules');
        }

        function openCompensationAnalysis() {
            alert('Compensation analysis functionality - Analyze pay equity and market competitiveness');
        }
    </script>
</body>

</html>