<?php
// HR Manager Organization Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../shared/database_helper.php';
include_once __DIR__ . '/../../routing/rbac.php';

$activeId = 'organization';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Initialize database helper
$dbHelper = new DatabaseHelper();

// Handle CRUD operations
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_department') {
        try {
            $departmentName = $_POST['department_name'] ?? '';
            $description = $_POST['description'] ?? '';
            $budgetAllocation = floatval($_POST['budget_allocation'] ?? 0);
            $headEmployeeId = intval($_POST['head_employee_id'] ?? 0);

            $dbHelper->query("
                INSERT INTO departments (department_name, description, budget_allocation, head_employee_id) 
                VALUES (?, ?, ?, ?)
            ", [$departmentName, $description, $budgetAllocation, $headEmployeeId ?: null]);

            $message = 'Department created successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error creating department: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'update_department') {
        try {
            $departmentId = intval($_POST['department_id'] ?? 0);
            $departmentName = $_POST['department_name'] ?? '';
            $description = $_POST['description'] ?? '';
            $budgetAllocation = floatval($_POST['budget_allocation'] ?? 0);
            $headEmployeeId = intval($_POST['head_employee_id'] ?? 0);

            $dbHelper->query("
                UPDATE departments 
                SET department_name = ?, description = ?, budget_allocation = ?, head_employee_id = ?
                WHERE id = ?
            ", [$departmentName, $description, $budgetAllocation, $headEmployeeId ?: null, $departmentId]);

            $message = 'Department updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error updating department: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete_department') {
        try {
            $departmentId = intval($_POST['department_id'] ?? 0);
            $dbHelper->query("DELETE FROM departments WHERE id = ?", [$departmentId]);
            $message = 'Department deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error deleting department: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get departments data
$departments = $dbHelper->getDepartments();

// Get positions
$positions = $dbHelper->getPositions();

// Get salary grades
$salaryGrades = $dbHelper->fetchAll("SELECT * FROM salary_grades ORDER BY grade_level");

// Calculate total budget
$totalBudget = array_sum(array_column($departments, 'budget_allocation'));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Department Control</title>
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
                            <h1 class="text-lg font-semibold">Department Control</h1>
                            <p class="text-xs text-slate-500 mt-1">Structure, heads, and budget allocation</p>
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
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Departments</div>
                                        <div class="text-2xl font-semibold"><?php echo count($departments); ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
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
                                        <div class="text-xs text-slate-500 mb-1">Positions</div>
                                        <div class="text-2xl font-semibold"><?php echo count($positions); ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Salary Grades</div>
                                        <div class="text-2xl font-semibold"><?php echo count($salaryGrades); ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Total Budget</div>
                                        <div class="text-2xl font-semibold">
                                            ₱<?php echo number_format($totalBudget, 0); ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Department Structure -->
                        <div class="grid lg:grid-cols-2 gap-4">
                            <!-- Department List -->
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div
                                    class="p-4 border-b border-[hsl(var(--border))] font-semibold flex items-center justify-between">
                                    <span>Departments</span>
                                    <button onclick="openCreateDepartmentModal()"
                                        class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-3 py-1 rounded-md text-sm hover:opacity-95 transition-opacity">
                                        Add Department
                                    </button>
                                </div>
                                <div class="p-4">
                                    <?php if (empty($departments)): ?>
                                        <div class="text-center py-10 text-slate-500">
                                            <div class="text-sm font-medium">No departments</div>
                                            <div class="text-xs mt-1">Create departments to build your organization.</div>
                                        </div>
                                    <?php else: ?>
                                        <div class="space-y-3">
                                            <?php foreach ($departments as $dept): ?>
                                                <div
                                                    class="flex items-center justify-between p-3 rounded-lg border border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                    <div class="flex-1">
                                                        <div class="font-medium text-slate-900 dark:text-slate-100">
                                                            <?php echo htmlspecialchars($dept['department_name']); ?>
                                                        </div>
                                                        <div class="text-xs text-slate-500">
                                                            Head:
                                                            <?php echo htmlspecialchars($dept['head_name'] ?? 'Not assigned'); ?>
                                                            •
                                                            <?php echo $dept['employee_count']; ?> employees
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-sm font-semibold text-green-600 dark:text-green-400">
                                                            ₱<?php echo number_format($dept['budget_allocation'], 0); ?>
                                                        </div>
                                                        <div class="flex items-center gap-1 mt-1">
                                                            <button
                                                                onclick="openEditDepartmentModal(<?php echo $dept['id']; ?>, '<?php echo htmlspecialchars($dept['department_name']); ?>', '<?php echo htmlspecialchars($dept['description'] ?? ''); ?>', <?php echo $dept['budget_allocation']; ?>, <?php echo $dept['head_employee_id'] ?: 'null'; ?>)"
                                                                class="p-1 text-slate-400 hover:text-blue-600 transition-colors"
                                                                title="Edit">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                    </path>
                                                                </svg>
                                                            </button>
                                                            <button
                                                                onclick="openDeleteDepartmentModal(<?php echo $dept['id']; ?>, '<?php echo htmlspecialchars($dept['department_name']); ?>')"
                                                                class="p-1 text-slate-400 hover:text-red-600 transition-colors"
                                                                title="Delete">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                    </path>
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Position Structure -->
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div
                                    class="p-4 border-b border-[hsl(var(--border))] font-semibold flex items-center justify-between">
                                    <span>Positions</span>
                                    <button
                                        class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-3 py-1 rounded-md text-sm hover:opacity-95 transition-opacity">
                                        Add Position
                                    </button>
                                </div>
                                <div class="p-4">
                                    <?php if (empty($positions)): ?>
                                        <div class="text-center py-10 text-slate-500">
                                            <div class="text-sm font-medium">No positions</div>
                                            <div class="text-xs mt-1">Create positions to define roles.</div>
                                        </div>
                                    <?php else: ?>
                                        <div class="space-y-2 max-h-96 overflow-y-auto">
                                            <?php foreach (array_slice($positions, 0, 10) as $position): ?>
                                                <div
                                                    class="flex items-center justify-between p-2 rounded border border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                    <div class="flex-1">
                                                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                                            <?php echo htmlspecialchars($position['position_title']); ?>
                                                        </div>
                                                        <div class="text-xs text-slate-500">
                                                            <?php echo htmlspecialchars($position['grade_level']); ?>
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-xs text-slate-600 dark:text-slate-300">
                                                            ₱<?php echo number_format($position['min_salary'], 0); ?> -
                                                            ₱<?php echo number_format($position['max_salary'], 0); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if (count($positions) > 10): ?>
                                                <div class="text-center py-2">
                                                    <button class="text-xs text-blue-600 hover:text-blue-800 transition-colors">
                                                        View all <?php echo count($positions); ?> positions
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Salary Grades Table -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div
                                class="p-4 border-b border-[hsl(var(--border))] font-semibold flex items-center justify-between">
                                <span>Salary Grades</span>
                                <button
                                    class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-3 py-1 rounded-md text-sm hover:opacity-95 transition-opacity">
                                    Add Grade
                                </button>
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
                                        <?php if (empty($salaryGrades)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="5">
                                                    <div class="text-center py-10">
                                                        <div class="text-sm font-medium">No salary grades</div>
                                                        <div class="text-xs text-slate-500 mt-1">Create salary grades to
                                                            define pay ranges.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
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
                                                                class="p-1 text-slate-400 hover:text-blue-600 transition-colors"
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

    <!-- Create Department Modal -->
    <div id="createDepartmentModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-lg w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Add Department</h3>
                    <button onclick="closeCreateDepartmentModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create_department">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Department Name
                        *</label>
                    <input type="text" name="department_name" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Budget Allocation
                        *</label>
                    <input type="number" name="budget_allocation" step="0.01" min="0" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Department
                        Head</label>
                    <select name="head_employee_id"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Head (Optional)</option>
                        <?php
                        $allEmployees = $dbHelper->getEmployees(1000);
                        foreach ($allEmployees as $emp):
                            ?>
                            <option value="<?php echo $emp['id']; ?>">
                                <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_number'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCreateDepartmentModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Create Department
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Department Modal -->
    <div id="editDepartmentModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-lg w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Edit Department</h3>
                    <button onclick="closeEditDepartmentModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="update_department">
                <input type="hidden" name="department_id" id="edit_department_id">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Department Name
                        *</label>
                    <input type="text" name="department_name" id="edit_department_name" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" id="edit_description" rows="3"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Budget Allocation
                        *</label>
                    <input type="number" name="budget_allocation" id="edit_budget_allocation" step="0.01" min="0"
                        required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Department
                        Head</label>
                    <select name="head_employee_id" id="edit_head_employee_id"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Head (Optional)</option>
                        <?php foreach ($allEmployees as $emp): ?>
                            <option value="<?php echo $emp['id']; ?>">
                                <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_number'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditDepartmentModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Update Department
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Department Confirmation Modal -->
    <div id="deleteDepartmentModal"
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
                    Are you sure you want to delete this department? This will affect all employees and positions in
                    this department.
                </p>
                <form method="POST" id="deleteDepartmentForm">
                    <input type="hidden" name="action" value="delete_department">
                    <input type="hidden" name="department_id" id="delete_department_id">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeDeleteDepartmentModal()"
                            class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            Delete Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        // Department Modal functions
        function openCreateDepartmentModal() {
            document.getElementById('createDepartmentModal').classList.remove('hidden');
        }

        function closeCreateDepartmentModal() {
            document.getElementById('createDepartmentModal').classList.add('hidden');
        }

        function openEditDepartmentModal(departmentId, departmentName, description, budgetAllocation, headEmployeeId) {
            document.getElementById('edit_department_id').value = departmentId;
            document.getElementById('edit_department_name').value = departmentName;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_budget_allocation').value = budgetAllocation;
            document.getElementById('edit_head_employee_id').value = headEmployeeId || '';
            document.getElementById('editDepartmentModal').classList.remove('hidden');
        }

        function closeEditDepartmentModal() {
            document.getElementById('editDepartmentModal').classList.add('hidden');
        }

        function openDeleteDepartmentModal(departmentId, departmentName) {
            document.getElementById('delete_department_id').value = departmentId;
            document.getElementById('deleteDepartmentModal').classList.remove('hidden');
        }

        function closeDeleteDepartmentModal() {
            document.getElementById('deleteDepartmentModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('fixed')) {
                closeCreateDepartmentModal();
                closeEditDepartmentModal();
                closeDeleteDepartmentModal();
            }
        });
    </script>
</body>

</html>