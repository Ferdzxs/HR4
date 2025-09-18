<?php
// HR Manager Delegations Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../shared/database_helper.php';
include_once __DIR__ . '/../../routing/rbac.php';

$activeId = 'delegations';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Initialize database helper
$dbHelper = new DatabaseHelper();

// Get delegation data (using audit logs as proxy for delegation activities)
$delegations = $dbHelper->getAuditLogs(20, 0, 'delegation');

// Get active users for delegation
$activeUsers = $dbHelper->fetchAll("
    SELECT u.id, u.username, r.role_name, CONCAT(e.first_name, ' ', e.last_name) as full_name
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.id
    LEFT JOIN employees e ON u.employee_id = e.id
    ORDER BY u.username
");

// Calculate stats
$totalDelegations = count($delegations);
$activeDelegations = count(array_filter($delegations, function ($d) {
    return strtotime($d['timestamp']) > strtotime('-30 days');
}));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Delegations</title>
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
                            <h1 class="text-lg font-semibold">Delegations</h1>
                            <p class="text-xs text-slate-500 mt-1">Temporary roles, approval chains, tracking</p>
                        </div>

                        <!-- Delegation Stats -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Total Delegations</div>
                                        <div class="text-2xl font-semibold"><?php echo $totalDelegations; ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Active This Month</div>
                                        <div class="text-2xl font-semibold"><?php echo $activeDelegations; ?></div>
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
                                        <div class="text-xs text-slate-500 mb-1">Pending Approvals</div>
                                        <div class="text-2xl font-semibold">
                                            <?php echo count(array_filter($delegations, function ($d) {
                                                return $d['action_type'] === 'delegation';
                                            })); ?>
                                        </div>
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
                                        <div class="text-xs text-slate-500 mb-1">Delegated Users</div>
                                        <div class="text-2xl font-semibold"><?php echo count($activeUsers); ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Create New Delegation -->
                        <div
                            class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                            <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Create New Delegation
                            </div>
                            <div class="p-4">
                                <form class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Delegate To</label>
                                        <select
                                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] text-sm">
                                            <option value="">Select User</option>
                                            <?php foreach ($activeUsers as $user): ?>
                                                <option value="<?php echo $user['id']; ?>">
                                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['role'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Delegation Type</label>
                                        <select
                                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] text-sm">
                                            <option value="">Select Type</option>
                                            <option value="approval">Approval Authority</option>
                                            <option value="review">Review Authority</option>
                                            <option value="management">Management Role</option>
                                            <option value="temporary">Temporary Assignment</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Start Date</label>
                                        <input type="date"
                                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">End Date</label>
                                        <input type="date"
                                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] text-sm">
                                    </div>
                                    <div class="sm:col-span-2 lg:col-span-4">
                                        <label class="block text-sm font-medium mb-1">Description</label>
                                        <textarea
                                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] text-sm"
                                            rows="2"
                                            placeholder="Describe the delegation scope and responsibilities..."></textarea>
                                    </div>
                                    <div class="sm:col-span-2 lg:col-span-4 flex gap-2">
                                        <button type="submit"
                                            class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-4 py-2 rounded-md text-sm hover:opacity-95 transition-opacity">
                                            Create Delegation
                                        </button>
                                        <button type="button"
                                            class="border border-[hsl(var(--border))] text-[hsl(var(--foreground))] px-4 py-2 rounded-md text-sm hover:bg-[hsl(var(--accent))] transition-colors">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Active Delegations -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <h3 class="font-semibold">Active Delegations</h3>
                                        <span
                                            class="px-2 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400 text-xs rounded-full">
                                            <?php echo $totalDelegations; ?> total
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        <select
                                            class="px-3 py-1 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] text-sm">
                                            <option>All Types</option>
                                            <option>Approval Authority</option>
                                            <option>Review Authority</option>
                                            <option>Management Role</option>
                                            <option>Temporary Assignment</option>
                                        </select>
                                        <button
                                            class="border border-[hsl(var(--border))] text-[hsl(var(--foreground))] px-3 py-1 rounded-md text-sm hover:bg-[hsl(var(--accent))] transition-colors">
                                            Export
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Delegation</th>
                                            <th class="text-left px-3 py-2 font-semibold">Delegate</th>
                                            <th class="text-left px-3 py-2 font-semibold">Type</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                            <th class="text-left px-3 py-2 font-semibold">Created</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($delegations)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="6">
                                                    <div class="text-center py-10">
                                                        <div class="text-sm font-medium">No delegations</div>
                                                        <div class="text-xs text-slate-500 mt-1">Create delegations to get
                                                            started.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($delegations as $delegation): ?>
                                                <tr
                                                    class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                    <td class="px-3 py-3">
                                                        <div>
                                                            <div class="font-medium text-slate-900 dark:text-slate-100">
                                                                <?php echo htmlspecialchars($delegation['action_type']); ?>
                                                            </div>
                                                            <div class="text-xs text-slate-500">
                                                                <?php echo htmlspecialchars($delegation['description']); ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <div>
                                                            <div class="font-medium">
                                                                <?php echo htmlspecialchars($delegation['username'] ?? 'System'); ?>
                                                            </div>
                                                            <div class="text-xs text-slate-500">
                                                                <?php echo htmlspecialchars($delegation['user_role'] ?? 'System'); ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                                            <?php echo htmlspecialchars($delegation['action_type']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                                            Active
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <?php echo date('M j, Y', strtotime($delegation['created_at'])); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="flex items-center gap-1">
                                                            <button
                                                                class="p-1 text-slate-400 hover:text-blue-600 transition-colors"
                                                                title="View Details">
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
                                                            </button>
                                                            <button
                                                                class="p-1 text-slate-400 hover:text-orange-600 transition-colors"
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
                                                                title="Revoke">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
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

                        <!-- Approval Chains -->
                        <div class="grid lg:grid-cols-2 gap-4">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Approval Chains
                                </div>
                                <div class="p-4">
                                    <div class="space-y-3">
                                        <div
                                            class="flex items-center justify-between p-3 rounded-lg border border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                            <div>
                                                <div class="font-medium text-slate-900 dark:text-slate-100">Payroll
                                                    Approval</div>
                                                <div class="text-xs text-slate-500">HR Manager → Finance Director → CEO
                                                </div>
                                            </div>
                                            <span
                                                class="px-2 py-1 bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400 text-xs rounded-full">Active</span>
                                        </div>
                                        <div
                                            class="flex items-center justify-between p-3 rounded-lg border border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                            <div>
                                                <div class="font-medium text-slate-900 dark:text-slate-100">Hiring
                                                    Approval</div>
                                                <div class="text-xs text-slate-500">Department Head → HR Manager → CEO
                                                </div>
                                            </div>
                                            <span
                                                class="px-2 py-1 bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400 text-xs rounded-full">Active</span>
                                        </div>
                                        <div
                                            class="flex items-center justify-between p-3 rounded-lg border border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                            <div>
                                                <div class="font-medium text-slate-900 dark:text-slate-100">Budget
                                                    Approval</div>
                                                <div class="text-xs text-slate-500">Department Head → Finance Director →
                                                    CEO</div>
                                            </div>
                                            <span
                                                class="px-2 py-1 bg-orange-100 dark:bg-orange-900/20 text-orange-800 dark:text-orange-400 text-xs rounded-full">Pending</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Recent Activity
                                </div>
                                <div class="p-4">
                                    <?php if (empty($delegations)): ?>
                                        <div class="text-center py-8 text-slate-500">
                                            <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                                </path>
                                            </svg>
                                            <p>No delegation activities found</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="space-y-3">
                                            <?php foreach (array_slice($delegations, 0, 5) as $delegation): ?>
                                                <div class="flex items-start gap-3">
                                                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="text-sm text-slate-900 dark:text-slate-100">
                                                            <?php echo htmlspecialchars($delegation['action_type']); ?>:
                                                            <?php echo htmlspecialchars($delegation['description'] ?? 'Delegation activity'); ?>
                                                        </div>
                                                        <div class="text-xs text-slate-500">
                                                            by
                                                            <?php echo htmlspecialchars($delegation['username'] ?? 'System'); ?>
                                                            • <?php echo date('M j, Y', strtotime($delegation['timestamp'])); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
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