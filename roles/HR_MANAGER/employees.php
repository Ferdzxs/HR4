<?php
// HR Manager Employee Management Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../routing/rbac.php';
include_once __DIR__ . '/../../config/database.php';

$activeId = 'employees';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_employee':
                try {
                    $pdo->beginTransaction();

                    // Insert employee
                    $stmt = $pdo->prepare("INSERT INTO employees (employee_number, first_name, last_name, department_id, position_id, hire_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['employee_number'],
                        $_POST['first_name'],
                        $_POST['last_name'],
                        $_POST['department_id'],
                        $_POST['position_id'],
                        $_POST['hire_date'],
                        'Active'
                    ]);
                    $employeeId = $pdo->lastInsertId();

                    // Insert employee details
                    $stmt = $pdo->prepare("INSERT INTO employee_details (employee_id, birth_date, gender, civil_status, contact_number, email, address, emergency_contact_name, emergency_contact_number, sss_no, philhealth_no, pagibig_no, tin_no, employment_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $employeeId,
                        $_POST['birth_date'],
                        $_POST['gender'],
                        $_POST['civil_status'],
                        $_POST['contact_number'],
                        $_POST['email'],
                        $_POST['address'],
                        $_POST['emergency_contact_name'],
                        $_POST['emergency_contact_number'],
                        $_POST['sss_no'],
                        $_POST['philhealth_no'],
                        $_POST['pagibig_no'],
                        $_POST['tin_no'],
                        $_POST['employment_type']
                    ]);

                    // Create user account
                    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role_id, employee_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        strtolower($_POST['first_name'] . '.' . $_POST['last_name']),
                        password_hash('password123', PASSWORD_DEFAULT),
                        6, // Hospital Employee role
                        $employeeId
                    ]);

                    $pdo->commit();
                    $success = "Employee added successfully with user account!";
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $error = "Error adding employee: " . $e->getMessage();
                }
                break;

            case 'update_status':
                try {
                    $stmt = $pdo->prepare("UPDATE employees SET status = ? WHERE id = ?");
                    $stmt->execute([$_POST['status'], $_POST['employee_id']]);
                    $success = "Employee status updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating employee: " . $e->getMessage();
                }
                break;

            case 'onboard_employee':
                try {
                    $stmt = $pdo->prepare("UPDATE employees SET status = 'Active' WHERE id = ?");
                    $stmt->execute([$_POST['employee_id']]);
                    $success = "Employee onboarded successfully!";
                } catch (PDOException $e) {
                    $error = "Error onboarding employee: " . $e->getMessage();
                }
                break;

            case 'offboard_employee':
                try {
                    $stmt = $pdo->prepare("UPDATE employees SET status = 'Resigned' WHERE id = ?");
                    $stmt->execute([$_POST['employee_id']]);
                    $success = "Employee offboarded successfully!";
                } catch (PDOException $e) {
                    $error = "Error offboarding employee: " . $e->getMessage();
                }
                break;
        }
    }
}

// Fetch employees with related data
try {
    $stmt = $pdo->query("SELECT e.*, d.department_name, p.position_title, sg.grade_level, sg.min_salary, sg.max_salary,
                        ed.birth_date, ed.gender, ed.civil_status, ed.contact_number, ed.email, ed.employment_type,
                        u.username, u.id as user_id
                        FROM employees e
                        LEFT JOIN departments d ON e.department_id = d.id
                        LEFT JOIN positions p ON e.position_id = p.id
                        LEFT JOIN salary_grades sg ON p.salary_grade_id = sg.id
                        LEFT JOIN employee_details ed ON e.id = ed.employee_id
                        LEFT JOIN users u ON e.id = u.employee_id
                        ORDER BY e.last_name, e.first_name");
    $employees = $stmt->fetchAll();

    // Fetch departments and positions for forms
    $stmt = $pdo->query("SELECT * FROM departments ORDER BY department_name");
    $departments = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT * FROM positions ORDER BY position_title");
    $positions = $stmt->fetchAll();

    // Fetch onboarding/offboarding statistics
    $onboardingStats = $pdo->query("SELECT 
        COUNT(*) as total_employees,
        SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_employees,
        SUM(CASE WHEN hire_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_hires_30_days,
        SUM(CASE WHEN status = 'Resigned' AND resignation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as resignations_30_days
        FROM employees")->fetch();

} catch (PDOException $e) {
    $employees = [];
    $departments = [];
    $positions = [];
    $onboardingStats = ['total_employees' => 0, 'active_employees' => 0, 'new_hires_30_days' => 0, 'resignations_30_days' => 0];
}
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

                        <!-- Onboarding Statistics -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Total Employees</div>
                                <div class="text-2xl font-semibold">
                                    <?php echo number_format($onboardingStats['total_employees']); ?></div>
                                <div class="text-xs text-blue-600 mt-1">All time</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Active Employees</div>
                                <div class="text-2xl font-semibold">
                                    <?php echo number_format($onboardingStats['active_employees']); ?></div>
                                <div class="text-xs text-green-600 mt-1">Currently working</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">New Hires (30 days)</div>
                                <div class="text-2xl font-semibold">
                                    <?php echo number_format($onboardingStats['new_hires_30_days']); ?></div>
                                <div class="text-xs text-purple-600 mt-1">Recent additions</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Resignations (30 days)</div>
                                <div class="text-2xl font-semibold">
                                    <?php echo number_format($onboardingStats['resignations_30_days']); ?></div>
                                <div class="text-xs text-orange-600 mt-1">Recent departures</div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="flex flex-wrap gap-2">
                            <button onclick="openAddModal()"
                                class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Add Employee
                            </button>
                            <button onclick="openOnboardingModal()"
                                class="bg-green-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Onboard Employee
                            </button>
                            <button onclick="openOffboardingModal()"
                                class="bg-red-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Offboard Employee
                            </button>
                            <button onclick="exportEmployees()"
                                class="bg-slate-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Export Data
                            </button>
                            <button onclick="bulkUpdate()"
                                class="bg-orange-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Bulk Update
                            </button>
                        </div>

                        <!-- Employee Directory -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <input type="text" placeholder="Search employees..."
                                            class="px-3 py-1 text-sm border border-[hsl(var(--border))] rounded-md focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"
                                            id="searchInput">
                                        <select
                                            class="px-3 py-1 text-sm border border-[hsl(var(--border))] rounded-md focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"
                                            id="statusFilter">
                                            <option value="">All Status</option>
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                            <option value="Resigned">Resigned</option>
                                            <option value="Terminated">Terminated</option>
                                            <option value="Retired">Retired</option>
                                        </select>
                                    </div>
                                    <div class="text-sm text-slate-500">
                                        <?php echo count($employees); ?> employees
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Contact</th>
                                            <th class="text-left px-3 py-2 font-semibold">Department</th>
                                            <th class="text-left px-3 py-2 font-semibold">Position</th>
                                            <th class="text-left px-3 py-2 font-semibold">Grade</th>
                                            <th class="text-left px-3 py-2 font-semibold">Hire Date</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($employees as $emp): ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div>
                                                        <div class="font-medium">
                                                            <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                                                        </div>
                                                        <div class="text-xs text-slate-500">
                                                            <?php echo htmlspecialchars($emp['employee_number']); ?>
                                                        </div>
                                                        <div class="text-xs text-slate-400">
                                                            <?php echo htmlspecialchars($emp['employment_type'] ?? 'N/A'); ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div>
                                                        <div class="text-sm">
                                                            <?php echo htmlspecialchars($emp['email'] ?? 'N/A'); ?></div>
                                                        <div class="text-xs text-slate-500">
                                                            <?php echo htmlspecialchars($emp['contact_number'] ?? 'N/A'); ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <?php echo htmlspecialchars($emp['department_name'] ?? 'N/A'); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div>
                                                        <div class="text-sm">
                                                            <?php echo htmlspecialchars($emp['position_title'] ?? 'N/A'); ?>
                                                        </div>
                                                        <?php if ($emp['min_salary'] && $emp['max_salary']): ?>
                                                            <div class="text-xs text-slate-500">
                                                                ₱<?php echo number_format($emp['min_salary'], 0); ?> -
                                                                ₱<?php echo number_format($emp['max_salary'], 0); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span
                                                        class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded-full">
                                                        <?php echo htmlspecialchars($emp['grade_level'] ?? 'N/A'); ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="text-sm">
                                                        <?php echo date('M j, Y', strtotime($emp['hire_date'])); ?></div>
                                                    <div class="text-xs text-slate-500">
                                                        <?php
                                                        $years = floor((time() - strtotime($emp['hire_date'])) / (365.25 * 24 * 60 * 60));
                                                        echo $years . ' year' . ($years != 1 ? 's' : '') . ' experience';
                                                        ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 text-xs rounded-full <?php
                                                    echo $emp['status'] === 'Active' ? 'bg-green-100 text-green-800' :
                                                        ($emp['status'] === 'Inactive' ? 'bg-gray-100 text-gray-800' :
                                                            ($emp['status'] === 'Resigned' ? 'bg-yellow-100 text-yellow-800' :
                                                                ($emp['status'] === 'Terminated' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')));
                                                    ?>">
                                                        <?php echo htmlspecialchars($emp['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex gap-1 flex-wrap">
                                                        <button onclick="viewEmployee(<?php echo $emp['id']; ?>)"
                                                            class="text-blue-600 hover:text-blue-800 text-xs px-2 py-1 rounded hover:bg-blue-50">View</button>
                                                        <button onclick="editEmployee(<?php echo $emp['id']; ?>)"
                                                            class="text-green-600 hover:text-green-800 text-xs px-2 py-1 rounded hover:bg-green-50">Edit</button>
                                                        <button onclick="viewProfile(<?php echo $emp['id']; ?>)"
                                                            class="text-purple-600 hover:text-purple-800 text-xs px-2 py-1 rounded hover:bg-purple-50">Profile</button>
                                                        <?php if ($emp['status'] === 'Active'): ?>
                                                            <button onclick="offboardEmployee(<?php echo $emp['id']; ?>)"
                                                                class="text-red-600 hover:text-red-800 text-xs px-2 py-1 rounded hover:bg-red-50">Offboard</button>
                                                        <?php elseif ($emp['status'] === 'Inactive'): ?>
                                                            <button onclick="onboardEmployee(<?php echo $emp['id']; ?>)"
                                                                class="text-green-600 hover:text-green-800 text-xs px-2 py-1 rounded hover:bg-green-50">Onboard</button>
                                                        <?php endif; ?>
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

    <!-- Add Employee Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold mb-4">Add New Employee</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_employee">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-900 border-b pb-2">Basic Information</h4>
                            <div>
                                <label class="block text-sm font-medium mb-1">Employee Number</label>
                                <input type="text" name="employee_number" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">First Name</label>
                                <input type="text" name="first_name" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Last Name</label>
                                <input type="text" name="last_name" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Department</label>
                                <select name="department_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>">
                                            <?php echo htmlspecialchars($dept['department_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Position</label>
                                <select name="position_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Position</option>
                                    <?php foreach ($positions as $pos): ?>
                                        <option value="<?php echo $pos['id']; ?>">
                                            <?php echo htmlspecialchars($pos['position_title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Hire Date</label>
                                <input type="date" name="hire_date" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Personal Details -->
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-900 border-b pb-2">Personal Details</h4>
                            <div>
                                <label class="block text-sm font-medium mb-1">Birth Date</label>
                                <input type="date" name="birth_date" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Gender</label>
                                <select name="gender" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Gender</option>
                                    <option value="M">Male</option>
                                    <option value="F">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Civil Status</label>
                                <select name="civil_status" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Civil Status</option>
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Widowed">Widowed</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Contact Number</label>
                                <input type="tel" name="contact_number" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Email</label>
                                <input type="email" name="email" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Address</label>
                                <textarea name="address" rows="3" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-900 border-b pb-2">Emergency Contact</h4>
                            <div>
                                <label class="block text-sm font-medium mb-1">Emergency Contact Name</label>
                                <input type="text" name="emergency_contact_name" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Emergency Contact Number</label>
                                <input type="tel" name="emergency_contact_number" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Government IDs -->
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-900 border-b pb-2">Government IDs</h4>
                            <div>
                                <label class="block text-sm font-medium mb-1">SSS Number</label>
                                <input type="text" name="sss_no" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">PhilHealth Number</label>
                                <input type="text" name="philhealth_no" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Pag-IBIG Number</label>
                                <input type="text" name="pagibig_no" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">TIN Number</label>
                                <input type="text" name="tin_no" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Employment Type</label>
                                <select name="employment_type" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select Employment Type</option>
                                    <option value="Regular">Regular</option>
                                    <option value="Contractual">Contractual</option>
                                    <option value="Probationary">Probationary</option>
                                    <option value="Part-time">Part-time</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Add
                            Employee</button>
                        <button type="button" onclick="closeAddModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Update Employee Status</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="employee_id" id="statusEmployeeId">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">New Status</label>
                            <select name="status" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Resigned">Resigned</option>
                                <option value="Terminated">Terminated</option>
                                <option value="Retired">Retired</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Update
                            Status</button>
                        <button type="button" onclick="closeStatusModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Onboarding Modal -->
    <div id="onboardingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Onboard Employee</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="onboard_employee">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Select Employee</label>
                            <select name="employee_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $emp): ?>
                                    <?php if ($emp['status'] === 'Inactive'): ?>
                                        <option value="<?php echo $emp['id']; ?>">
                                            <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_number'] . ')'); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-md p-3">
                            <div class="text-sm text-green-800">
                                <strong>Onboarding Process:</strong>
                                <ul class="mt-2 space-y-1 text-xs">
                                    <li>• Activate employee account</li>
                                    <li>• Assign department and position</li>
                                    <li>• Set up payroll and benefits</li>
                                    <li>• Send welcome email</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit"
                            class="flex-1 bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">Onboard
                            Employee</button>
                        <button type="button" onclick="closeOnboardingModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Offboarding Modal -->
    <div id="offboardingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Offboard Employee</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="offboard_employee">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Select Employee</label>
                            <select name="employee_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $emp): ?>
                                    <?php if ($emp['status'] === 'Active'): ?>
                                        <option value="<?php echo $emp['id']; ?>">
                                            <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_number'] . ')'); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="bg-red-50 border border-red-200 rounded-md p-3">
                            <div class="text-sm text-red-800">
                                <strong>Offboarding Process:</strong>
                                <ul class="mt-2 space-y-1 text-xs">
                                    <li>• Deactivate employee account</li>
                                    <li>• Process final payroll</li>
                                    <li>• Collect company assets</li>
                                    <li>• Update benefits and records</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit"
                            class="flex-1 bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700">Offboard
                            Employee</button>
                        <button type="button" onclick="closeOffboardingModal()"
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

        function updateStatus(employeeId, currentStatus) {
            document.getElementById('statusEmployeeId').value = employeeId;
            document.querySelector('#statusModal select[name="status"]').value = currentStatus;
            document.getElementById('statusModal').classList.remove('hidden');
        }

        function closeStatusModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }

        function openOnboardingModal() {
            document.getElementById('onboardingModal').classList.remove('hidden');
        }

        function closeOnboardingModal() {
            document.getElementById('onboardingModal').classList.add('hidden');
        }

        function openOffboardingModal() {
            document.getElementById('offboardingModal').classList.remove('hidden');
        }

        function closeOffboardingModal() {
            document.getElementById('offboardingModal').classList.add('hidden');
        }

        function viewEmployee(id) {
            // Show employee details in a modal or redirect to profile page
            window.open(`employee-profile.php?id=${id}`, '_blank');
        }

        function editEmployee(id) {
            // Show edit employee modal with pre-filled data
            alert('Edit employee ' + id + ' - This will open an edit modal with employee data');
        }

        function viewProfile(id) {
            // Show detailed employee profile
            window.open(`employee-profile.php?id=${id}`, '_blank');
        }

        function onboardEmployee(id) {
            if (confirm('Are you sure you want to onboard this employee?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="onboard_employee">
                    <input type="hidden" name="employee_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function offboardEmployee(id) {
            if (confirm('Are you sure you want to offboard this employee?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="offboard_employee">
                    <input type="hidden" name="employee_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function exportEmployees() {
            // Implement export functionality
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
            a.download = 'employees_export.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }

        function bulkUpdate() {
            // Implement bulk update functionality
            alert('Bulk update functionality - Select multiple employees and update their status or department');
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Status filter functionality
        document.getElementById('statusFilter').addEventListener('change', function () {
            const filterValue = this.value;
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                if (filterValue === '') {
                    row.style.display = '';
                } else {
                    const statusCell = row.querySelector('td:nth-child(6)');
                    const status = statusCell.textContent.trim();
                    row.style.display = status === filterValue ? '' : 'none';
                }
            });
        });
    </script>
</body>

</html>