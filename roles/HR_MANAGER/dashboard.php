<?php
require_once '../../config/database.php';
require_once '../../shared/header.php';
require_once '../../shared/sidebar.php';
require_once '../../shared/scripts.php';

// Check authentication and role
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'HR_MANAGER') {
    header('Location: ../../routing/login.php');
    exit;
}

$user = $_SESSION;
$sidebarCollapsed = $_COOKIE['hr4_sidebar_collapsed'] ?? 'false';
$sidebarCollapsed = $sidebarCollapsed === 'true';

// HR Manager sidebar items
$sidebarItems = [
    ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'd'],
    ['id' => 'employees', 'label' => 'Employee Records', 'icon' => 'e'],
    ['id' => 'leave', 'label' => 'Leave Administration', 'icon' => 'l'],
    ['id' => 'payroll', 'label' => 'Payroll Overview', 'icon' => 'p'],
    ['id' => 'compensation', 'label' => 'Compensation Management', 'icon' => 'c'],
    ['id' => 'benefits', 'label' => 'Benefits Administration', 'icon' => 'b'],
    ['id' => 'analytics', 'label' => 'HR Analytics', 'icon' => 'a'],
    ['id' => 'documents', 'label' => 'HR Documents', 'icon' => 'd'],
    ['id' => 'delegations', 'label' => 'Delegations', 'icon' => 'd'],
    ['id' => 'settings', 'label' => 'Settings', 'icon' => 's']
];

// Get dashboard data
try {
    $pdo = getConnection();
    
    // Workforce summary
    $workforceQuery = "SELECT 
        COUNT(*) as total_employees,
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_employees,
        COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_employees,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_hires_30d
        FROM employees";
    $workforce = $pdo->query($workforceQuery)->fetch(PDO::FETCH_ASSOC);
    
    // Pending tasks
    $pendingTasksQuery = "SELECT 
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_leave_requests,
        COUNT(CASE WHEN status = 'pending' AND type = 'compensation' THEN 1 END) as pending_compensation,
        COUNT(CASE WHEN status = 'pending' AND type = 'benefits' THEN 1 END) as pending_benefits
        FROM hr_requests";
    $pendingTasks = $pdo->query($pendingTasksQuery)->fetch(PDO::FETCH_ASSOC);
    
    // Recent activities
    $recentActivitiesQuery = "SELECT 
        r.type, r.description, r.created_at, e.first_name, e.last_name
        FROM hr_requests r
        LEFT JOIN employees e ON r.employee_id = e.id
        ORDER BY r.created_at DESC
        LIMIT 5";
    $recentActivities = $pdo->query($recentActivitiesQuery)->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $workforce = ['total_employees' => 0, 'active_employees' => 0, 'inactive_employees' => 0, 'new_hires_30d' => 0];
    $pendingTasks = ['pending_leave_requests' => 0, 'pending_compensation' => 0, 'pending_benefits' => 0];
    $recentActivities = [];
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Manager Dashboard - HR4</title>
    <link rel="stylesheet" href="../../shared/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="h-full bg-[hsl(var(--background))] text-[hsl(var(--foreground))]">
    <div class="flex h-full">
        <?php echo renderSidebar($sidebarItems, 'dashboard', $sidebarCollapsed); ?>
        
        <div class="flex-1 grid <?php echo $sidebarCollapsed ? 'lg:grid-cols-[72px_1fr]' : 'lg:grid-cols-[260px_1fr]'; ?>">
            <main class="overflow-auto">
                <?php echo renderHeader($user, $sidebarCollapsed); ?>
                
                <div class="p-6 space-y-6">
                    <!-- Page Header -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-[hsl(var(--foreground))]">HR Manager Dashboard</h1>
                            <p class="text-[hsl(var(--muted-foreground))] mt-1">Overview of workforce, payroll cycles, and pending tasks</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="btn btn-outline btn-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Export Report
                            </button>
                            <button class="btn btn-primary btn-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Quick Action
                            </button>
                        </div>
                    </div>

                    <!-- Key Metrics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="card hover-lift">
                            <div class="card-body">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-[hsl(var(--muted-foreground))]">Total Employees</p>
                                        <p class="text-3xl font-bold text-[hsl(var(--foreground))]"><?php echo $workforce['total_employees']; ?></p>
                                    </div>
                                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <span class="text-sm text-green-600 dark:text-green-400">
                                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <?php echo $workforce['new_hires_30d']; ?> new hires (30d)
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card hover-lift">
                            <div class="card-body">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-[hsl(var(--muted-foreground))]">Active Employees</p>
                                        <p class="text-3xl font-bold text-[hsl(var(--foreground))]"><?php echo $workforce['active_employees']; ?></p>
                                    </div>
                                    <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <span class="text-sm text-[hsl(var(--muted-foreground))]">
                                        <?php echo round(($workforce['active_employees'] / max($workforce['total_employees'], 1)) * 100, 1); ?>% of total workforce
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card hover-lift">
                            <div class="card-body">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-[hsl(var(--muted-foreground))]">Pending Leave</p>
                                        <p class="text-3xl font-bold text-[hsl(var(--foreground))]"><?php echo $pendingTasks['pending_leave_requests']; ?></p>
                                    </div>
                                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <span class="text-sm text-yellow-600 dark:text-yellow-400">Requires attention</span>
                                </div>
                            </div>
                        </div>

                        <div class="card hover-lift">
                            <div class="card-body">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-[hsl(var(--muted-foreground))]">Pending Approvals</p>
                                        <p class="text-3xl font-bold text-[hsl(var(--foreground))]"><?php echo $pendingTasks['pending_compensation'] + $pendingTasks['pending_benefits']; ?></p>
                                    </div>
                                    <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full">
                                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <span class="text-sm text-red-600 dark:text-red-400">Compensation & Benefits</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts and Analytics -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Workforce Trends Chart -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="text-lg font-semibold">Workforce Trends</h3>
                                <p class="text-sm text-[hsl(var(--muted-foreground))]">Employee count over the last 6 months</p>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="workforceChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Department Distribution -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="text-lg font-semibold">Department Distribution</h3>
                                <p class="text-sm text-[hsl(var(--muted-foreground))]">Current workforce by department</p>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="departmentChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities and Quick Actions -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Recent Activities -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="text-lg font-semibold">Recent Activities</h3>
                                <p class="text-sm text-[hsl(var(--muted-foreground))]">Latest HR requests and updates</p>
                            </div>
                            <div class="card-body">
                                <div class="space-y-4">
                                    <?php if (empty($recentActivities)): ?>
                                        <div class="text-center py-8 text-[hsl(var(--muted-foreground))]">
                                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                            </svg>
                                            <p>No recent activities</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($recentActivities as $activity): ?>
                                            <div class="flex items-start space-x-3 p-3 bg-[hsl(var(--muted))] rounded-lg">
                                                <div class="flex-shrink-0">
                                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-[hsl(var(--foreground))]">
                                                        <?php echo htmlspecialchars($activity['type']); ?>
                                                    </p>
                                                    <p class="text-sm text-[hsl(var(--muted-foreground))]">
                                                        <?php echo htmlspecialchars($activity['description']); ?>
                                                    </p>
                                                    <p class="text-xs text-[hsl(var(--muted-foreground))] mt-1">
                                                        <?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?> • 
                                                        <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="text-lg font-semibold">Quick Actions</h3>
                                <p class="text-sm text-[hsl(var(--muted-foreground))]">Common HR tasks and shortcuts</p>
                            </div>
                            <div class="card-body">
                                <div class="grid grid-cols-2 gap-4">
                                    <button class="btn btn-outline p-4 h-auto flex flex-col items-center space-y-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        <span class="text-sm">Add Employee</span>
                                    </button>
                                    
                                    <button class="btn btn-outline p-4 h-auto flex flex-col items-center space-y-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        <span class="text-sm">Process Leave</span>
                                    </button>
                                    
                                    <button class="btn btn-outline p-4 h-auto flex flex-col items-center space-y-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                        <span class="text-sm">Run Payroll</span>
                                    </button>
                                    
                                    <button class="btn btn-outline p-4 h-auto flex flex-col items-center space-y-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <span class="text-sm">View Reports</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
            // Workforce Trends Chart
            const workforceCtx = document.getElementById('workforceChart').getContext('2d');
            new Chart(workforceCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Total Employees',
                        data: [<?php echo $workforce['total_employees'] - 5; ?>, <?php echo $workforce['total_employees'] - 3; ?>, <?php echo $workforce['total_employees'] - 2; ?>, <?php echo $workforce['total_employees'] - 1; ?>, <?php echo $workforce['total_employees']; ?>, <?php echo $workforce['total_employees']; ?>],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Department Distribution Chart
            const departmentCtx = document.getElementById('departmentChart').getContext('2d');
            new Chart(departmentCtx, {
                type: 'doughnut',
                data: {
                    labels: ['HR', 'IT', 'Finance', 'Operations', 'Sales', 'Marketing'],
                    datasets: [{
                        data: [15, 25, 20, 30, 18, 12],
                        backgroundColor: [
                            'rgb(59, 130, 246)',
                            'rgb(16, 185, 129)',
                            'rgb(245, 158, 11)',
                            'rgb(239, 68, 68)',
                            'rgb(139, 92, 246)',
                            'rgb(236, 72, 153)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
    <script src="../../shared/scripts.js"></script>
</body>
</html>
