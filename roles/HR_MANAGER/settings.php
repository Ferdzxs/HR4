<?php
// HR Manager Settings Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../routing/rbac.php';
include_once __DIR__ . '/../../config/database.php';

$activeId = 'settings';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                try {
                    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role_id, employee_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['username'],
                        password_hash($_POST['password'], PASSWORD_DEFAULT),
                        $_POST['role_id'],
                        $_POST['employee_id'] ?: null
                    ]);
                    $success = "User added successfully!";
                } catch (PDOException $e) {
                    $error = "Error adding user: " . $e->getMessage();
                }
                break;

            case 'update_role':
                try {
                    $stmt = $pdo->prepare("UPDATE roles SET role_name = ?, permissions = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['role_name'],
                        $_POST['permissions'],
                        $_POST['role_id']
                    ]);
                    $success = "Role updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating role: " . $e->getMessage();
                }
                break;

            case 'update_system_config':
                try {
                    // In a real implementation, this would update system configuration
                    $success = "System configuration updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating configuration: " . $e->getMessage();
                }
                break;
        }
    }
}

// Fetch settings data
try {
    // Get all users with their roles and employee info
    $stmt = $pdo->query("SELECT u.*, r.role_name, e.first_name, e.last_name, e.employee_number
                        FROM users u
                        LEFT JOIN roles r ON u.role_id = r.id
                        LEFT JOIN employees e ON u.employee_id = e.id
                        ORDER BY u.username");
    $users = $stmt->fetchAll();

    // Get all roles
    $stmt = $pdo->query("SELECT * FROM roles ORDER BY role_name");
    $roles = $stmt->fetchAll();

    // Get employees without user accounts
    $stmt = $pdo->query("SELECT e.*, d.department_name FROM employees e
                        LEFT JOIN departments d ON e.department_id = d.id
                        LEFT JOIN users u ON e.id = u.employee_id
                        WHERE u.id IS NULL AND e.status = 'Active'
                        ORDER BY e.first_name, e.last_name");
    $employeesWithoutAccounts = $stmt->fetchAll();

    // System statistics
    $totalUsers = count($users);
    $totalRoles = count($roles);
    $activeUsers = count(array_filter($users, function ($user) {
        return $user['employee_id'] !== null;
    }));
    $systemAdmins = count(array_filter($users, function ($user) {
        return strpos($user['role_name'], 'Manager') !== false || strpos($user['role_name'], 'Admin') !== false;
    }));

} catch (PDOException $e) {
    $users = [];
    $roles = [];
    $employeesWithoutAccounts = [];
    $totalUsers = $totalRoles = $activeUsers = $systemAdmins = 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Settings</title>
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
                            <h1 class="text-lg font-semibold">Settings</h1>
                            <p class="text-xs text-slate-500 mt-1">Users, roles, permissions, and system configuration
                            </p>
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
                            <button onclick="openAddUserModal()"
                                class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Add User
                            </button>
                            <button onclick="openRoleModal()"
                                class="bg-orange-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Manage Roles
                            </button>
                            <button onclick="exportSettings()"
                                class="bg-slate-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Export Data
                            </button>
                        </div>

                        <!-- System Statistics -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Total Users</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($totalUsers); ?></div>
                                <div class="text-xs text-blue-600 mt-1">System accounts</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Active Users</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($activeUsers); ?></div>
                                <div class="text-xs text-green-600 mt-1">Linked to employees</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Roles</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($totalRoles); ?></div>
                                <div class="text-xs text-purple-600 mt-1">Permission sets</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Administrators</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($systemAdmins); ?></div>
                                <div class="text-xs text-orange-600 mt-1">Manager level</div>
                            </div>
                        </div>

                        <!-- User Management -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">User Management</div>
                                    <div class="text-sm text-slate-500"><?php echo count($users); ?> users</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Username</th>
                                            <th class="text-left px-3 py-2 font-semibold">Role</th>
                                            <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Created</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $userData): ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($userData['username']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                        <?php echo htmlspecialchars($userData['role_name'] ?? 'No Role'); ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <?php if ($userData['first_name'] && $userData['last_name']): ?>
                                                        <div>
                                                            <div class="text-sm">
                                                                <?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?>
                                                            </div>
                                                            <div class="text-xs text-slate-500">
                                                                <?php echo htmlspecialchars($userData['employee_number']); ?>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-xs text-slate-500">No employee linked</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <?php echo date('M j, Y', strtotime($userData['created_at'])); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex gap-1">
                                                        <button onclick="editUser(<?php echo $userData['id']; ?>)"
                                                            class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                                                        <button onclick="resetPassword(<?php echo $userData['id']; ?>)"
                                                            class="text-orange-600 hover:text-orange-800 text-xs">Reset</button>
                                                        <button onclick="deleteUser(<?php echo $userData['id']; ?>)"
                                                            class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Role Management -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Role Management</div>
                                    <div class="text-sm text-slate-500"><?php echo count($roles); ?> roles</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Role Name</th>
                                            <th class="text-left px-3 py-2 font-semibold">Permissions</th>
                                            <th class="text-left px-3 py-2 font-semibold">Users</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($roles as $role): ?>
                                            <?php
                                            $permissions = json_decode($role['permissions'], true);
                                            $modules = $permissions['modules'] ?? [];
                                            $userCount = count(array_filter($users, function ($user) use ($role) {
                                                return $user['role_id'] == $role['id'];
                                            }));
                                            ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($role['role_name']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex flex-wrap gap-1">
                                                        <?php foreach (array_slice($modules, 0, 3) as $module): ?>
                                                            <span
                                                                class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                                                <?php echo htmlspecialchars($module); ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                        <?php if (count($modules) > 3): ?>
                                                            <span
                                                                class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">
                                                                +<?php echo count($modules) - 3; ?> more
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                        <?php echo $userCount; ?> users
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex gap-1">
                                                        <button onclick="editRole(<?php echo $role['id']; ?>)"
                                                            class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                                                        <button onclick="viewRolePermissions(<?php echo $role['id']; ?>)"
                                                            class="text-green-600 hover:text-green-800 text-xs">Permissions</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Role Permissions Management -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Role Permissions Management</div>
                                    <div class="text-sm text-slate-500">Configure access levels for each role</div>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="space-y-4">
                                    <?php foreach ($roles as $role): ?>
                                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-3">
                                                <div>
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($role['role_name']); ?></div>
                                                    <div class="text-xs text-gray-500">
                                                        <?php echo count(array_filter($users, function ($u) use ($role) {
                                                            return $u['role_id'] == $role['id']; })); ?>
                                                        users assigned
                                                    </div>
                                                </div>
                                                <div class="flex space-x-2">
                                                    <button onclick="editRolePermissions(<?php echo $role['id']; ?>)"
                                                        class="text-blue-600 hover:text-blue-800 text-xs px-2 py-1 rounded hover:bg-blue-50">
                                                        Edit Permissions
                                                    </button>
                                                    <button onclick="viewRoleUsers(<?php echo $role['id']; ?>)"
                                                        class="text-green-600 hover:text-green-800 text-xs px-2 py-1 rounded hover:bg-green-50">
                                                        View Users
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                                <div class="text-xs">
                                                    <span class="font-medium">Dashboard:</span>
                                                    <span class="text-green-600">✓ Full Access</span>
                                                </div>
                                                <div class="text-xs">
                                                    <span class="font-medium">Employees:</span>
                                                    <span class="text-green-600">✓ Full Access</span>
                                                </div>
                                                <div class="text-xs">
                                                    <span class="font-medium">Payroll:</span>
                                                    <span class="text-green-600">✓ Full Access</span>
                                                </div>
                                                <div class="text-xs">
                                                    <span class="font-medium">Reports:</span>
                                                    <span class="text-green-600">✓ Full Access</span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- System Configuration -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">System Configuration</div>
                                    <div class="text-sm text-slate-500">Global system settings and preferences</div>
                                </div>
                            </div>
                            <div class="p-4 space-y-4">
                                <form method="POST">
                                    <input type="hidden" name="action" value="update_system_config">
                                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium mb-1">System Name</label>
                                            <input type="text" name="system_name"
                                                value="HR4 Compensation & Intelligence"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Default Language</label>
                                            <select name="default_language"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="en">English</option>
                                                <option value="es">Spanish</option>
                                                <option value="fr">French</option>
                                                <option value="de">German</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Time Zone</label>
                                            <select name="timezone"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="UTC">UTC</option>
                                                <option value="America/New_York">Eastern Time</option>
                                                <option value="America/Chicago">Central Time</option>
                                                <option value="America/Denver">Mountain Time</option>
                                                <option value="America/Los_Angeles">Pacific Time</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Session Timeout
                                                (minutes)</label>
                                            <input type="number" name="session_timeout" value="30" min="5" max="480"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Max Login Attempts</label>
                                            <input type="number" name="max_login_attempts" value="5" min="3" max="10"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Password Expiry (days)</label>
                                            <input type="number" name="password_expiry" value="90" min="30" max="365"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Email Notifications</label>
                                            <select name="email_notifications"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="enabled">Enabled</option>
                                                <option value="disabled">Disabled</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Auto Backup</label>
                                            <select name="auto_backup"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="enabled">Enabled</option>
                                                <option value="disabled">Disabled</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Maintenance Mode</label>
                                            <select name="maintenance_mode"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="disabled">Disabled</option>
                                                <option value="enabled">Enabled</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 mt-4">
                                        <button type="submit"
                                            class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Save
                                            Configuration</button>
                                        <button type="button" onclick="resetSystemConfig()"
                                            class="bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Reset
                                            to Defaults</button>
                                        <button type="button" onclick="testConfiguration()"
                                            class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">Test
                                            Config</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Security Settings -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Security Settings</div>
                                    <div class="text-sm text-slate-500">Authentication and security policies</div>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                        <div class="text-xs text-red-600 mb-1">Password Policy</div>
                                        <div class="text-sm font-medium text-red-800">Strong Required</div>
                                        <div class="text-xs text-red-600">Min 8 chars, mixed case, numbers</div>
                                    </div>
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                        <div class="text-xs text-yellow-600 mb-1">Two-Factor Auth</div>
                                        <div class="text-sm font-medium text-yellow-800">Optional</div>
                                        <div class="text-xs text-yellow-600">Recommended for admins</div>
                                    </div>
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                        <div class="text-xs text-green-600 mb-1">Session Security</div>
                                        <div class="text-sm font-medium text-green-800">Enabled</div>
                                        <div class="text-xs text-green-600">Secure session handling</div>
                                    </div>
                                </div>
                                <div class="flex gap-2 mt-4">
                                    <button onclick="updatePasswordPolicy()"
                                        class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-700">
                                        Update Password Policy
                                    </button>
                                    <button onclick="configure2FA()"
                                        class="bg-yellow-600 text-white px-4 py-2 rounded-md text-sm hover:bg-yellow-700">
                                        Configure 2FA
                                    </button>
                                    <button onclick="viewSecurityLogs()"
                                        class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">
                                        View Security Logs
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                </main>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Add New User</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_user">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Username</label>
                            <input type="text" name="username" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Password</label>
                            <input type="password" name="password" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Role</label>
                            <select name="role_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Role</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?php echo $role['id']; ?>">
                                        <?php echo htmlspecialchars($role['role_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Employee (Optional)</label>
                            <select name="employee_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">No Employee Link</option>
                                <?php foreach ($employeesWithoutAccounts as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>">
                                        <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_number'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Add
                            User</button>
                        <button type="button" onclick="closeAddUserModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        function openAddUserModal() {
            document.getElementById('addUserModal').classList.remove('hidden');
        }

        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.add('hidden');
        }

        function openRoleModal() {
            alert('Role management functionality coming soon');
        }

        function editUser(id) {
            alert('Edit user ' + id);
        }

        function resetPassword(id) {
            if (confirm('Are you sure you want to reset this user\'s password?')) {
                alert('Password reset for user ' + id);
            }
        }

        function deleteUser(id) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                alert('Delete user ' + id);
            }
        }

        function editRole(id) {
            alert('Edit role ' + id);
        }

        function viewRolePermissions(id) {
            alert('View role permissions ' + id);
        }

        function saveSystemConfig() {
            alert('System configuration saved successfully!');
        }

        function resetSystemConfig() {
            if (confirm('Are you sure you want to reset all system configuration to defaults?')) {
                alert('System configuration reset to defaults');
            }
        }

        function exportSettings() {
            // Export settings data to CSV
            const table = document.getElementById('usersTable');
            const rows = Array.from(table.querySelectorAll('tr'));
            const csvContent = rows.map(row => {
                const cells = Array.from(row.querySelectorAll('td, th'));
                return cells.map(cell => cell.textContent.trim()).join(',');
            }).join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'settings_export.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Role Permissions Management
        function editRolePermissions(roleId) {
            // Open modal to edit role permissions
            alert('Editing permissions for role ID: ' + roleId);
        }

        function viewRoleUsers(roleId) {
            // Open modal to view users with this role
            alert('Viewing users for role ID: ' + roleId);
        }

        function createRole() {
            // Open modal to create new role
            alert('Creating new role...');
        }

        function deleteRole(roleId) {
            if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
                alert('Deleting role ID: ' + roleId);
            }
        }

        // Security Settings
        function updatePasswordPolicy() {
            // Open modal to update password policy
            alert('Updating password policy...');
        }

        function configure2FA() {
            // Open modal to configure two-factor authentication
            alert('Configuring two-factor authentication...');
        }

        function viewSecurityLogs() {
            // Open modal to view security logs
            alert('Viewing security logs...');
        }

        function testConfiguration() {
            // Test system configuration
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = 'Testing...';
            button.disabled = true;

            // Simulate configuration test
            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
                alert('Configuration test completed successfully!');
            }, 2000);
        }

        // User Management
        function viewUserDetails(userId) {
            // Open modal to view user details
            alert('Viewing user details for ID: ' + userId);
        }

        function toggleUserStatus(userId) {
            // Toggle user active/inactive status
            if (confirm('Are you sure you want to toggle this user\'s status?')) {
                alert('Toggling user status for ID: ' + userId);
            }
        }

        function assignRole(userId) {
            // Open modal to assign role to user
            alert('Assigning role to user ID: ' + userId);
        }

        // System Administration
        function backupSystem() {
            // Create system backup
            if (confirm('Are you sure you want to create a system backup?')) {
                alert('Creating system backup...');
            }
        }

        function restoreSystem() {
            // Restore system from backup
            if (confirm('Are you sure you want to restore the system? This will overwrite current data.')) {
                alert('Restoring system from backup...');
            }
        }

        function clearCache() {
            // Clear system cache
            if (confirm('Are you sure you want to clear the system cache?')) {
                alert('Clearing system cache...');
            }
        }

        function viewSystemLogs() {
            // Open modal to view system logs
            alert('Viewing system logs...');
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function () {
            // Add hover effects to metric cards
            const metricCards = document.querySelectorAll('.metric-card');
            metricCards.forEach(card => {
                card.addEventListener('mouseenter', function () {
                    this.classList.add('shadow-lg', 'scale-105');
                });
                card.addEventListener('mouseleave', function () {
                    this.classList.remove('shadow-lg', 'scale-105');
                });
            });
        });
    </script>
</body>

</html>