<?php
// HR Manager Dashboard Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../shared/database_helper.php';
include_once __DIR__ . '/../../routing/rbac.php';

$activeId = 'dashboard';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Initialize database helper
$dbHelper = new DatabaseHelper();

// Get dashboard statistics
$stats = $dbHelper->getDashboardStats();

// Get recent activities
$recentActivities = $dbHelper->getRecentActivities(5);

// Get department overview
$departments = $dbHelper->getDepartments();

// Get recent payroll data
$recentPayroll = $dbHelper->getPayrollData();

// Get pending benefit claims
$pendingClaims = $dbHelper->getBenefitClaims('Pending');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - HR Manager Dashboard</title>
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
                            <h1 class="text-lg font-semibold">HR Manager</h1>
                            <p class="text-xs text-slate-500 mt-1">Role-based overview with quick insights</p>
                        </div>
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Total Employees</div>
                                        <div class="text-2xl font-semibold">
                                            <?php echo number_format($stats['total_employees']); ?>
                                        </div>
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
                                        <div class="text-2xl font-semibold">
                                            <?php echo number_format($stats['total_departments']); ?>
                                        </div>
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
                                        <div class="text-xs text-slate-500 mb-1">Payroll Entries</div>
                                        <div class="text-2xl font-semibold">
                                            <?php echo number_format($stats['payroll_entries']); ?>
                                        </div>
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
                                        <div class="text-xs text-slate-500 mb-1">Active Benefits</div>
                                        <div class="text-2xl font-semibold">
                                            <?php echo number_format($stats['active_benefits']); ?>
                                        </div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid lg:grid-cols-2 gap-4">
                            <!-- Recent Activities -->
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Recent Activities
                                </div>
                                <div class="p-4">
                                    <?php if (empty($recentActivities)): ?>
                                        <div class="text-sm text-slate-500">No recent activities</div>
                                    <?php else: ?>
                                        <div class="space-y-3">
                                            <?php foreach ($recentActivities as $activity): ?>
                                                <div class="flex items-start gap-3">
                                                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="text-sm text-slate-900 dark:text-slate-100">
                                                            <?php echo htmlspecialchars($activity['action_type']); ?> on
                                                            <?php echo htmlspecialchars($activity['table_affected']); ?>
                                                        </div>
                                                        <div class="text-xs text-slate-500">
                                                            by
                                                            <?php echo htmlspecialchars($activity['username'] ?? 'System'); ?> •
                                                            <?php echo date('M j, Y g:i A', strtotime($activity['timestamp'])); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Department Overview -->
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Department Overview
                                </div>
                                <div class="p-4">
                                    <?php if (empty($departments)): ?>
                                        <div class="text-sm text-slate-500">No departments found</div>
                                    <?php else: ?>
                                        <div class="space-y-3">
                                            <?php foreach (array_slice($departments, 0, 5) as $dept): ?>
                                                <div class="flex items-center justify-between">
                                                    <div>
                                                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                                            <?php echo htmlspecialchars($dept['department_name']); ?>
                                                        </div>
                                                        <div class="text-xs text-slate-500">
                                                            Head:
                                                            <?php echo htmlspecialchars($dept['head_name'] ?? 'Not assigned'); ?>
                                                        </div>
                                                    </div>
                                                    <div class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                                        <?php echo $dept['employee_count']; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div
                            class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                            <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Quick Actions</div>
                            <div class="p-4">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <a href="?page=employees"
                                        class="flex items-center gap-2 p-3 rounded-lg border border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                            </path>
                                        </svg>
                                        <span class="text-sm">Add Employee</span>
                                    </a>
                                    <a href="?page=payroll"
                                        class="flex items-center gap-2 p-3 rounded-lg border border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                            </path>
                                        </svg>
                                        <span class="text-sm">Process Payroll</span>
                                    </a>
                                    <a href="?page=benefits"
                                        class="flex items-center gap-2 p-3 rounded-lg border border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                        <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                            </path>
                                        </svg>
                                        <span class="text-sm">Manage Benefits</span>
                                    </a>
                                    <a href="?page=analytics"
                                        class="flex items-center gap-2 p-3 rounded-lg border border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                        <span class="text-sm">View Analytics</span>
                                    </a>
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