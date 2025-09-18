<?php
// HR Manager Employee Management Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../shared/database_helper.php';
include_once __DIR__ . '/../../routing/rbac.php';

$activeId = 'employees';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Initialize database helper
$dbHelper = new DatabaseHelper();

// Handle CRUD operations
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_employee') {
        try {
            $employeeNumber = $_POST['employee_number'] ?? '';
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $departmentId = intval($_POST['department_id'] ?? 0);
            $positionId = intval($_POST['position_id'] ?? 0);
            $hireDate = $_POST['hire_date'] ?? '';
            $email = $_POST['email'] ?? '';
            $contactNumber = $_POST['contact_number'] ?? '';
            $address = $_POST['address'] ?? '';
            $birthDate = $_POST['birth_date'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $civilStatus = $_POST['civil_status'] ?? '';
            $employmentType = $_POST['employment_type'] ?? '';

            // Insert employee
            $dbHelper->query("
                INSERT INTO employees (employee_number, first_name, last_name, department_id, position_id, hire_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'Active')
            ", [$employeeNumber, $firstName, $lastName, $departmentId, $positionId, $hireDate]);

            $employeeId = $dbHelper->fetchOne("SELECT LAST_INSERT_ID() as id")['id'];

            // Insert employee details
            $dbHelper->query("
                INSERT INTO employee_details (employee_id, birth_date, gender, civil_status, contact_number, email, address, employment_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ", [$employeeId, $birthDate, $gender, $civilStatus, $contactNumber, $email, $address, $employmentType]);

            $message = 'Employee created successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error creating employee: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'update_employee') {
        try {
            $employeeId = intval($_POST['employee_id'] ?? 0);
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $departmentId = intval($_POST['department_id'] ?? 0);
            $positionId = intval($_POST['position_id'] ?? 0);
            $hireDate = $_POST['hire_date'] ?? '';
            $email = $_POST['email'] ?? '';
            $contactNumber = $_POST['contact_number'] ?? '';
            $address = $_POST['address'] ?? '';
            $birthDate = $_POST['birth_date'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $civilStatus = $_POST['civil_status'] ?? '';
            $employmentType = $_POST['employment_type'] ?? '';

            // Update employee
            $dbHelper->query("
                UPDATE employees 
                SET first_name = ?, last_name = ?, department_id = ?, position_id = ?, hire_date = ?
                WHERE id = ?
            ", [$firstName, $lastName, $departmentId, $positionId, $hireDate, $employeeId]);

            // Update employee details
            $dbHelper->query("
                UPDATE employee_details 
                SET birth_date = ?, gender = ?, civil_status = ?, contact_number = ?, email = ?, address = ?, employment_type = ?
                WHERE employee_id = ?
            ", [$birthDate, $gender, $civilStatus, $contactNumber, $email, $address, $employmentType, $employeeId]);

            $message = 'Employee updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error updating employee: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete_employee') {
        try {
            $employeeId = intval($_POST['employee_id'] ?? 0);
            $dbHelper->deactivateEmployeeById($employeeId);
            $message = 'Employee deactivated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error deactivating employee: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get search parameters
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['p'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Department filter
$departmentId = isset($_GET['dept']) ? intval($_GET['dept']) : null;

// Get employees data
$employees = $dbHelper->getEmployees($limit, $offset, $search, $departmentId);

// Total for pagination (respect filters)
$countSql = "SELECT COUNT(*) as count FROM employees e WHERE e.status = 'Active'";
$countParams = [];
if ($search) {
    $countSql .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_number LIKE ?)";
    $countParams = array_merge($countParams, ["%$search%", "%$search%", "%$search%"]);
}
if ($departmentId) {
    $countSql .= " AND e.department_id = ?";
    $countParams[] = $departmentId;
}
$totalEmployees = $dbHelper->fetchOne($countSql, $countParams)['count'] ?? 0;

// Get departments for filter
$departments = $dbHelper->getDepartments();

// Get positions
$positions = $dbHelper->getPositions();

// Get salary grades for positions
$salaryGrades = $dbHelper->fetchAll("SELECT * FROM salary_grades ORDER BY grade_level");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Employee Management</title>
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
                            <h1 class="text-lg font-semibold">Employee Management</h1>
                            <p class="text-xs text-slate-500 mt-1">Directory, profiles, onboarding and offboarding</p>
                        </div>

                        <!-- Search and Filters -->
                        <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="flex-1">
                                    <div class="relative">
                                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <input type="text" id="searchInput" placeholder="Search employees..."
                                            value="<?php echo htmlspecialchars($search); ?>"
                                            class="w-full pl-10 pr-4 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <form method="get" class="flex gap-2">
                                        <input type="hidden" name="page" value="employees">
                                        <input type="hidden" name="search"
                                            value="<?php echo htmlspecialchars($search); ?>">
                                        <select name="dept"
                                            class="px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"
                                            onchange="this.form.submit()">
                                            <option value="">All Departments</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?php echo $dept['id']; ?>" <?php echo ($departmentId === (int) $dept['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                    <button onclick="openCreateModal()"
                                        class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-4 py-2 rounded-md hover:opacity-95 transition-opacity">
                                        Add Employee
                                    </button>
                                </div>
                            </div>
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

                        <!-- Employee Stats -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Total Employees</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($totalEmployees); ?></div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Active</div>
                                <div class="text-2xl font-semibold text-green-600">
                                    <?php echo number_format($totalEmployees); ?>
                                </div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Departments</div>
                                <div class="text-2xl font-semibold"><?php echo count($departments); ?></div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Positions</div>
                                <div class="text-2xl font-semibold"><?php echo count($positions); ?></div>
                            </div>
                        </div>

                        <!-- Employee Table -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Department</th>
                                            <th class="text-left px-3 py-2 font-semibold">Position</th>
                                            <th class="text-left px-3 py-2 font-semibold">Contact</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($employees)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="6">
                                                    <div class="text-center py-10">
                                                        <div class="text-sm font-medium">No employees found</div>
                                                        <div class="text-xs text-slate-500 mt-1">Add employees to get
                                                            started.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($employees as $employee): ?>
                                                <tr
                                                    class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                    <td class="px-3 py-3">
                                                        <div class="flex items-center gap-3">
                                                            <div
                                                                class="w-8 h-8 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center">
                                                                <span
                                                                    class="text-xs font-semibold text-blue-600 dark:text-blue-400">
                                                                    <?php echo strtoupper(substr($employee['first_name'], 0, 1) . substr($employee['last_name'], 0, 1)); ?>
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <div class="font-medium text-slate-900 dark:text-slate-100">
                                                                    <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                                                                </div>
                                                                <div class="text-xs text-slate-500">
                                                                    <?php echo htmlspecialchars($employee['employee_number']); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <?php echo htmlspecialchars($employee['department_name'] ?? 'N/A'); ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <?php echo htmlspecialchars($employee['position_title'] ?? 'N/A'); ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <div class="text-xs">
                                                            <?php echo htmlspecialchars($employee['email'] ?? 'N/A'); ?>
                                                        </div>
                                                        <div class="text-xs text-slate-500">
                                                            <?php echo htmlspecialchars($employee['contact_number'] ?? 'N/A'); ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                                            <?php echo htmlspecialchars($employee['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="flex items-center gap-1">
                                                            <a href="?page=employees&action=view&id=<?php echo (int) $employee['id']; ?>"
                                                                class="p-1 text-slate-400 hover:text-blue-600 transition-colors"
                                                                title="View">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                                                    </path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                                    </path>
                                                                </svg>
                                                            </a>
                                                            <button onclick="openEditModal(<?php echo (int) $employee['id']; ?>, {
                                                                first_name: '<?php echo htmlspecialchars($employee['first_name']); ?>',
                                                                last_name: '<?php echo htmlspecialchars($employee['last_name']); ?>',
                                                                department_id: '<?php echo (int) $employee['department_id']; ?>',
                                                                position_id: '<?php echo (int) $employee['position_id']; ?>',
                                                                email: '<?php echo htmlspecialchars($employee['email'] ?? ''); ?>',
                                                                contact_number: '<?php echo htmlspecialchars($employee['contact_number'] ?? ''); ?>',
                                                                address: '<?php echo htmlspecialchars($employee['address'] ?? ''); ?>',
                                                                birth_date: '<?php echo htmlspecialchars($employee['birth_date'] ?? ''); ?>',
                                                                gender: '<?php echo htmlspecialchars($employee['gender'] ?? ''); ?>',
                                                                civil_status: '<?php echo htmlspecialchars($employee['civil_status'] ?? ''); ?>',
                                                                employment_type: '<?php echo htmlspecialchars($employee['employment_type'] ?? ''); ?>'
                                                            })"
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
                                                                onclick="openDeleteModal(<?php echo (int) $employee['id']; ?>, '<?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>')"
                                                                class="p-1 text-slate-400 hover:text-red-600 transition-colors"
                                                                title="Deactivate">
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

                        <!-- Pagination -->
                        <?php if ($totalEmployees > $limit): ?>
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-slate-500">
                                    Showing <?php echo $offset + 1; ?> to
                                    <?php echo min($offset + $limit, $totalEmployees); ?> of <?php echo $totalEmployees; ?>
                                    employees
                                </div>
                                <div class="flex items-center gap-2">
                                    <?php if ($page > 1): ?>
                                        <a href="?page=employees&p=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>"
                                            class="px-3 py-1 border border-[hsl(var(--border))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                                            Previous
                                        </a>
                                    <?php endif; ?>

                                    <?php
                                    $totalPages = ceil($totalEmployees / $limit);
                                    $startPage = max(1, $page - 2);
                                    $endPage = min($totalPages, $page + 2);

                                    for ($i = $startPage; $i <= $endPage; $i++):
                                        ?>
                                        <a href="?page=employees&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                                            class="px-3 py-1 border border-[hsl(var(--border))] rounded-md <?php echo $i === $page ? 'bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))]' : 'hover:bg-[hsl(var(--accent))]'; ?> transition-colors">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>

                                    <?php if ($page < $totalPages): ?>
                                        <a href="?page=employees&p=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>"
                                            class="px-3 py-1 border border-[hsl(var(--border))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                                            Next
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </section>
                </main>
            </div>
        </div>
    </div>

    <!-- Create Employee Modal -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Add New Employee</h3>
                    <button onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create_employee">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Employee Number
                            *</label>
                        <input type="text" name="employee_number" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Hire Date
                            *</label>
                        <input type="date" name="hire_date" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">First Name
                            *</label>
                        <input type="text" name="first_name" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Last Name
                            *</label>
                        <input type="text" name="last_name" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Department
                            *</label>
                        <select name="department_id" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Position
                            *</label>
                        <select name="position_id" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                            <option value="">Select Position</option>
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?php echo $pos['id']; ?>">
                                    <?php echo htmlspecialchars($pos['position_title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email *</label>
                        <input type="email" name="email" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Contact Number
                            *</label>
                        <input type="tel" name="contact_number" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Address *</label>
                    <textarea name="address" rows="3" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Birth Date
                            *</label>
                        <input type="date" name="birth_date" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Gender
                            *</label>
                        <select name="gender" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                            <option value="">Select Gender</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Civil Status
                            *</label>
                        <select name="civil_status" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                            <option value="">Select Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Employment Type
                        *</label>
                    <select name="employment_type" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Type</option>
                        <option value="Regular">Regular</option>
                        <option value="Contractual">Contractual</option>
                        <option value="Probationary">Probationary</option>
                        <option value="Part-time">Part-time</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCreateModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Create Employee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Edit Employee</h3>
                    <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="update_employee">
                <input type="hidden" name="employee_id" id="edit_employee_id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">First Name
                            *</label>
                        <input type="text" name="first_name" id="edit_first_name" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Last Name
                            *</label>
                        <input type="text" name="last_name" id="edit_last_name" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Department
                            *</label>
                        <select name="department_id" id="edit_department_id" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>">
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Position
                            *</label>
                        <select name="position_id" id="edit_position_id" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                            <option value="">Select Position</option>
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?php echo $pos['id']; ?>">
                                    <?php echo htmlspecialchars($pos['position_title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email *</label>
                        <input type="email" name="email" id="edit_email" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Contact Number
                            *</label>
                        <input type="tel" name="contact_number" id="edit_contact_number" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Address *</label>
                    <textarea name="address" id="edit_address" rows="3" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Birth Date
                            *</label>
                        <input type="date" name="birth_date" id="edit_birth_date" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Gender
                            *</label>
                        <select name="gender" id="edit_gender" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                            <option value="">Select Gender</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Civil Status
                            *</label>
                        <select name="civil_status" id="edit_civil_status" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                            <option value="">Select Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Employment Type
                        *</label>
                    <select name="employment_type" id="edit_employment_type" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Type</option>
                        <option value="Regular">Regular</option>
                        <option value="Contractual">Contractual</option>
                        <option value="Probationary">Probationary</option>
                        <option value="Part-time">Part-time</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Update Employee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
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
                        <h3 class="text-lg font-semibold">Confirm Deactivation</h3>
                        <p class="text-sm text-slate-500">This action cannot be undone.</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300 mb-6">
                    Are you sure you want to deactivate this employee? They will be marked as inactive and removed from
                    active employee lists.
                </p>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="action" value="delete_employee">
                    <input type="hidden" name="employee_id" id="delete_employee_id">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeDeleteModal()"
                            class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            Deactivate Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                const search = this.value;
                window.location.href = '?page=employees&search=' + encodeURIComponent(search);
            }
        });

        // Modal functions
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        function openEditModal(employeeId, employeeData) {
            document.getElementById('edit_employee_id').value = employeeId;
            document.getElementById('edit_first_name').value = employeeData.first_name || '';
            document.getElementById('edit_last_name').value = employeeData.last_name || '';
            document.getElementById('edit_department_id').value = employeeData.department_id || '';
            document.getElementById('edit_position_id').value = employeeData.position_id || '';
            document.getElementById('edit_email').value = employeeData.email || '';
            document.getElementById('edit_contact_number').value = employeeData.contact_number || '';
            document.getElementById('edit_address').value = employeeData.address || '';
            document.getElementById('edit_birth_date').value = employeeData.birth_date || '';
            document.getElementById('edit_gender').value = employeeData.gender || '';
            document.getElementById('edit_civil_status').value = employeeData.civil_status || '';
            document.getElementById('edit_employment_type').value = employeeData.employment_type || '';
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function openDeleteModal(employeeId, employeeName) {
            document.getElementById('delete_employee_id').value = employeeId;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('fixed')) {
                closeCreateModal();
                closeEditModal();
                closeDeleteModal();
            }
        });
    </script>
</body>

</html>