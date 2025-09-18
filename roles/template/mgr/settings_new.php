<?php
// HR Manager Settings Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../shared/database_helper.php';
include_once __DIR__ . '/../../routing/rbac.php';

$activeId = 'settings';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Initialize database helper
$dbHelper = new DatabaseHelper();

// Get users and roles data
$users = $dbHelper->fetchAll("
    SELECT 
        u.*,
        r.role_name,
        d.department_name
    FROM users u
    LEFT JOIN roles r ON u.role = r.role_name
    LEFT JOIN departments d ON u.department_id = d.id
    ORDER BY u.username
");

$roles = $dbHelper->fetchAll("SELECT * FROM roles ORDER BY role_name");
$departments = $dbHelper->fetchAll("SELECT * FROM departments ORDER BY department_name");

// Calculate stats
$totalUsers = count($users);
$activeUsers = count(array_filter($users, function ($u) {
    return $u['status'] === 'Active'; }));
$totalRoles = count($roles);
$totalDepartments = count($departments);
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

                        <!-- Settings Stats -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Total Users</div>
                                        <div class="text-2xl font-semibold"><?php echo $totalUsers; ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Active Users</div>
                                        <div class="text-2xl font-semibold"><?php echo $activeUsers; ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">User Roles</div>
                                        <div class="text-2xl font-semibold"><?php echo $totalRoles; ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
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
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Management -->
                        <div
                            class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                            <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">User Management</div>
                            <div class="p-4">
                                <div class="flex flex-col sm:flex-row gap-4 sm:items-center justify-between mb-4">
                                    <div class="flex gap-2">
                                        <h3 class="font-semibold">Users</h3>
                                        <span
                                            class="px-2 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400 text-xs rounded-full">
                                            <?php echo $totalUsers; ?> users
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-3 py-1 rounded-md text-sm hover:opacity-95 transition-opacity">
                                            Add User
                                        </button>
                                        <button
                                            class="border border-[hsl(var(--border))] text-[hsl(var(--foreground))] px-3 py-1 rounded-md text-sm hover:bg-[hsl(var(--accent))] transition-colors">
                                            Import Users
                                        </button>
                                    </div>
                                </div>

                                <!-- Users Table -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-[hsl(var(--secondary))]">
                                            <tr>
                                                <th class="text-left px-3 py-2 font-semibold">User</th>
                                                <th class="text-left px-3 py-2 font-semibold">Role</th>
                                                <th class="text-left px-3 py-2 font-semibold">Department</th>
                                                <th class="text-left px-3 py-2 font-semibold">Status</th>
                                                <th class="text-left px-3 py-2 font-semibold">Last Login</th>
                                                <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($users, 0, 10) as $userData): ?>
                                                <tr
                                                    class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                    <td class="px-3 py-3">
                                                        <div class="flex items-center gap-3">
                                                            <div
                                                                class="w-8 h-8 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center">
                                                                <span
                                                                    class="text-xs font-medium text-blue-600 dark:text-blue-400">
                                                                    <?php echo strtoupper(substr($userData['first_name'], 0, 1) . substr($userData['last_name'], 0, 1)); ?>
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <div class="font-medium text-slate-900 dark:text-slate-100">
                                                                    <?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?>
                                                                </div>
                                                                <div class="text-xs text-slate-500">
                                                                    <?php echo htmlspecialchars($userData['username']); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                                            <?php echo htmlspecialchars($userData['role_name'] ?? $userData['role']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <?php echo htmlspecialchars($userData['department_name'] ?? 'N/A'); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $userData['status'] === 'Active' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'; ?>">
                                                            <?php echo htmlspecialchars($userData['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <?php echo $userData['last_login'] ? date('M j, Y', strtotime($userData['last_login'])) : 'Never'; ?>
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
                                                                class="p-1 text-slate-400 hover:text-orange-600 transition-colors"
                                                                title="Reset Password">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z">
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
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Role Management -->
                        <div class="grid lg:grid-cols-2 gap-4">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">User Roles</div>
                                <div class="p-4">
                                    <div class="space-y-3">
                                        <?php foreach ($roles as $role): ?>
                                            <div
                                                class="flex items-center justify-between p-3 rounded-lg border border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                <div>
                                                    <div class="font-medium text-slate-900 dark:text-slate-100">
                                                        <?php echo htmlspecialchars($role['role_name']); ?>
                                                    </div>
                                                    <div class="text-xs text-slate-500">
                                                        <?php echo htmlspecialchars($role['description'] ?? 'No description'); ?>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="px-2 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400 text-xs rounded-full">
                                                        <?php echo count(array_filter($users, function ($u) use ($role) {
                                                            return $u['role'] === $role['role_name']; })); ?>
                                                        users
                                                    </span>
                                                    <button
                                                        class="p-1 text-slate-400 hover:text-blue-600 transition-colors">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">System Configuration
                                </div>
                                <div class="p-4 space-y-4">
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="font-medium text-slate-900 dark:text-slate-100">Session
                                                    Timeout</div>
                                                <div class="text-xs text-slate-500">Auto-logout after inactivity</div>
                                            </div>
                                            <span class="text-sm text-slate-600 dark:text-slate-300">30 minutes</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="font-medium text-slate-900 dark:text-slate-100">Password
                                                    Policy</div>
                                                <div class="text-xs text-slate-500">Minimum password requirements</div>
                                            </div>
                                            <span class="text-sm text-slate-600 dark:text-slate-300">8+ chars</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="font-medium text-slate-900 dark:text-slate-100">MFA Required
                                                </div>
                                                <div class="text-xs text-slate-500">Multi-factor authentication</div>
                                            </div>
                                            <span class="text-sm text-slate-600 dark:text-slate-300">Optional</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="font-medium text-slate-900 dark:text-slate-100">Audit
                                                    Logging</div>
                                                <div class="text-xs text-slate-500">Track user activities</div>
                                            </div>
                                            <span class="text-sm text-slate-600 dark:text-slate-300">Enabled</span>
                                        </div>
                                    </div>
                                    <div class="pt-4 border-t border-[hsl(var(--border))]">
                                        <button
                                            class="w-full bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-4 py-2 rounded-md text-sm hover:opacity-95 transition-opacity">
                                            Update Configuration
                                        </button>
                                    </div>
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