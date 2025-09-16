<?php
// HR Manager Organization Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../routing/rbac.php';
include_once __DIR__ . '/../../config/database.php';

$activeId = 'organization';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_department':
                try {
                    $stmt = $pdo->prepare("INSERT INTO departments (department_name, department_head_id, parent_department_id, budget_allocation) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['department_name'],
                        $_POST['department_head_id'] ?: null,
                        $_POST['parent_department_id'] ?: null,
                        $_POST['budget_allocation']
                    ]);
                    $success = "Department added successfully!";
                } catch (PDOException $e) {
                    $error = "Error adding department: " . $e->getMessage();
                }
                break;

            case 'update_department':
                try {
                    $stmt = $pdo->prepare("UPDATE departments SET department_name = ?, department_head_id = ?, parent_department_id = ?, budget_allocation = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['department_name'],
                        $_POST['department_head_id'] ?: null,
                        $_POST['parent_department_id'] ?: null,
                        $_POST['budget_allocation'],
                        $_POST['department_id']
                    ]);
                    $success = "Department updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating department: " . $e->getMessage();
                }
                break;
        }
    }
}

// Fetch departments with related data
try {
    $stmt = $pdo->query("SELECT d.*, 
                        CONCAT(e.first_name, ' ', e.last_name) as head_name,
                        e.employee_number as head_employee_number,
                        pd.department_name as parent_name,
                        COUNT(emp.id) as employee_count,
                        AVG(sg.min_salary + sg.max_salary) / 2 as avg_salary
                        FROM departments d
                        LEFT JOIN employees e ON d.department_head_id = e.id
                        LEFT JOIN departments pd ON d.parent_department_id = pd.id
                        LEFT JOIN employees emp ON emp.department_id = d.id AND emp.status = 'Active'
                        LEFT JOIN positions p ON emp.position_id = p.id
                        LEFT JOIN salary_grades sg ON p.salary_grade_id = sg.id
                        GROUP BY d.id
                        ORDER BY d.department_name");
    $departments = $stmt->fetchAll();

    // Fetch all employees for department head selection
    $stmt = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as full_name, employee_number, department_id 
                        FROM employees WHERE status = 'Active' ORDER BY first_name, last_name");
    $employees = $stmt->fetchAll();

    // Get positions by department
    $stmt = $pdo->query("SELECT p.*, d.department_name, sg.grade_level, sg.min_salary, sg.max_salary,
                        COUNT(emp.id) as filled_positions
                        FROM positions p
                        LEFT JOIN departments d ON p.department_id = d.id
                        LEFT JOIN salary_grades sg ON p.salary_grade_id = sg.id
                        LEFT JOIN employees emp ON p.id = emp.position_id AND emp.status = 'Active'
                        GROUP BY p.id
                        ORDER BY d.department_name, p.position_title");
    $positions = $stmt->fetchAll();

    // Calculate statistics
    $totalDepartments = count($departments);
    $totalPositions = count($positions);
    $totalBudget = $pdo->query("SELECT SUM(budget_allocation) FROM departments")->fetchColumn();
    $openRoles = $pdo->query("SELECT COUNT(*) FROM positions p LEFT JOIN employees e ON p.id = e.position_id WHERE e.id IS NULL")->fetchColumn();
    $totalEmployees = array_sum(array_column($departments, 'employee_count'));

    // Department hierarchy for visualization
    $hierarchy = [];
    foreach ($departments as $dept) {
        if (!$dept['parent_department_id']) {
            $hierarchy[] = buildDepartmentHierarchy($dept, $departments);
        }
    }

} catch (PDOException $e) {
    $departments = [];
    $employees = [];
    $positions = [];
    $totalDepartments = $totalPositions = $totalBudget = $openRoles = $totalEmployees = 0;
    $hierarchy = [];
}

// Function to build department hierarchy
function buildDepartmentHierarchy($department, $allDepartments)
{
    $children = array_filter($allDepartments, function ($dept) use ($department) {
        return $dept['parent_department_id'] == $department['id'];
    });

    $department['children'] = array_map(function ($child) use ($allDepartments) {
        return buildDepartmentHierarchy($child, $allDepartments);
    }, $children);

    return $department;
}

// Function to render department node in hierarchy
function renderDepartmentNode($department, $level)
{
    $indent = $level * 20;
    $html = '<div class="flex items-center space-x-2" style="margin-left: ' . $indent . 'px;">';

    if ($level > 0) {
        $html .= '<div class="w-4 h-px bg-gray-300"></div>';
    }

    $html .= '<div class="flex-1 bg-white border border-gray-200 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow">';
    $html .= '<div class="flex items-center justify-between">';
    $html .= '<div>';
    $html .= '<div class="font-medium text-gray-900">' . htmlspecialchars($department['department_name']) . '</div>';
    $html .= '<div class="text-sm text-gray-500">';

    if ($department['head_name']) {
        $html .= 'Head: ' . htmlspecialchars($department['head_name']);
    } else {
        $html .= 'No head assigned';
    }

    $html .= ' • ' . $department['employee_count'] . ' employees';
    $html .= ' • ₱' . number_format($department['budget_allocation'], 0) . ' budget';
    $html .= '</div>';
    $html .= '</div>';

    $html .= '<div class="flex space-x-2">';
    $html .= '<button onclick="editDepartment(' . $department['id'] . ')" class="text-blue-600 hover:text-blue-800 text-xs px-2 py-1 rounded hover:bg-blue-50">Edit</button>';
    $html .= '<button onclick="viewDepartment(' . $department['id'] . ')" class="text-green-600 hover:text-green-800 text-xs px-2 py-1 rounded hover:bg-green-50">View</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    // Render children
    if (!empty($department['children'])) {
        $html .= '<div class="mt-2 space-y-2">';
        foreach ($department['children'] as $child) {
            $html .= renderDepartmentNode($child, $level + 1);
        }
        $html .= '</div>';
    }

    $html .= '</div>';
    return $html;
}
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

                        <!-- Organization Statistics -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Total Departments</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($totalDepartments); ?>
                                </div>
                                <div class="text-xs text-blue-600 mt-1">Organizational units</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Total Positions</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($totalPositions); ?></div>
                                <div class="text-xs text-green-600 mt-1">Job roles</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Open Roles</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($openRoles); ?></div>
                                <div class="text-xs text-orange-600 mt-1">Available positions</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Total Budget</div>
                                <div class="text-2xl font-semibold">₱<?php echo number_format($totalBudget, 0); ?></div>
                                <div class="text-xs text-purple-600 mt-1">Allocated budget</div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="flex flex-wrap gap-2">
                            <button onclick="openAddModal()"
                                class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Add Department
                            </button>
                            <button onclick="openBudgetModal()"
                                class="bg-green-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Budget Management
                            </button>
                            <button onclick="viewHierarchy()"
                                class="bg-purple-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                View Hierarchy
                            </button>
                            <button onclick="exportDepartments()"
                                class="bg-slate-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Export Data
                            </button>
                        </div>

                        <!-- Department Hierarchy Visualization -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Department Hierarchy</div>
                                    <div class="text-sm text-slate-500">Organizational structure</div>
                                </div>
                            </div>
                            <div class="p-4">
                                <div id="hierarchyContainer" class="space-y-2">
                                    <?php foreach ($hierarchy as $dept): ?>
                                        <?php echo renderDepartmentNode($dept, 0); ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Department</th>
                                            <th class="text-left px-3 py-2 font-semibold">Head</th>
                                            <th class="text-left px-3 py-2 font-semibold">Parent</th>
                                            <th class="text-left px-3 py-2 font-semibold">Employees</th>
                                            <th class="text-left px-3 py-2 font-semibold">Budget</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($departments)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="6">
                                                    <div
                                                        class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
                                                        <div class="text-sm font-medium">No departments</div>
                                                        <div class="text-xs text-slate-500 mt-1">Create departments to build
                                                            your organization.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($departments as $dept): ?>
                                                <tr
                                                    class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                    <td class="px-3 py-3">
                                                        <div class="font-medium">
                                                            <?php echo htmlspecialchars($dept['department_name']); ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php echo htmlspecialchars($dept['head_name'] ?? 'Not assigned'); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php echo htmlspecialchars($dept['parent_name'] ?? 'Root'); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                            <?php echo $dept['employee_count']; ?> employees
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        ₱<?php echo number_format($dept['budget_allocation'], 0); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="flex gap-1">
                                                            <button onclick="editDepartment(<?php echo $dept['id']; ?>)"
                                                                class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                                                            <button onclick="viewDepartment(<?php echo $dept['id']; ?>)"
                                                                class="text-green-600 hover:text-green-800 text-xs">View</button>
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

    <!-- Add Department Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Add New Department</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_department">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Department Name</label>
                            <input type="text" name="department_name" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Department Head</label>
                            <select name="department_head_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Department Head</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>">
                                        <?php echo htmlspecialchars($emp['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Parent Department</label>
                            <select name="parent_department_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Parent Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>">
                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Budget Allocation</label>
                            <input type="number" name="budget_allocation" step="0.01" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Add
                            Department</button>
                        <button type="button" onclick="closeAddModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        function editDepartment(id) {
            // Implement edit department functionality
            alert('Edit department ' + id);
        }

        function viewDepartment(id) {
            // Implement view department functionality
            alert('View department ' + id);
        }

        function openBudgetModal() {
            alert('Budget management functionality - Allocate and track department budgets');
        }

        function viewHierarchy() {
            // Toggle hierarchy view
            const container = document.getElementById('hierarchyContainer');
            container.classList.toggle('hidden');
        }

        function exportDepartments() {
            // Export departments data
            const table = document.querySelector('table');
            const rows = Array.from(table.querySelectorAll('tr'));
            const csvContent = rows.map(row =>
                Array.from(row.querySelectorAll('td, th')).map(cell =>
                    `"${cell.textContent.trim()}"`
                ).join(',')
            ).join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'departments_export.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }
    </script>
</body>

</html>