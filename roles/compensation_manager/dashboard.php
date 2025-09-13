<?php
/**
 * Compensation Manager Dashboard - Frontend Only
 * HR4 Compensation & Intelligence System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../shared/components.php';

// Initialize database and auth
$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

// Check authentication
$auth->requireRole('Compensation Manager');
$userInfo = $auth->getCurrentUser();

// Build dashboard content
$content = '
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Compensation Dashboard</h1>
    <p class="text-gray-600">Welcome back, ' . htmlspecialchars($userInfo['employee_name']) . '! Manage compensation strategies and salary planning.</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid mb-8">
    ' . SharedComponents::renderStatsCard('Avg Salary', '₱45,000', 3.2, 'currency-dollar', 'blue') . '
    ' . SharedComponents::renderStatsCard('Salary Grades', '15', 0, 'table', 'green') . '
    ' . SharedComponents::renderStatsCard('Merit Increases', '23', 8.5, 'trending-up', 'purple') . '
    ' . SharedComponents::renderStatsCard('Pay Equity Score', '94%', 1.2, 'scale', 'emerald') . '
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Salary Distribution -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Salary Distribution</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Grade 1-3</span>
                    <div class="flex items-center gap-2">
                        <div class="w-32 bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 25%"></div>
                        </div>
                        <span class="text-sm font-medium">25%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Grade 4-6</span>
                    <div class="flex items-center gap-2">
                        <div class="w-32 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 45%"></div>
                        </div>
                        <span class="text-sm font-medium">45%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Grade 7-10</span>
                    <div class="flex items-center gap-2">
                        <div class="w-32 bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: 30%"></div>
                        </div>
                        <span class="text-sm font-medium">30%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Approvals -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pending Approvals</h3>
        </div>
        <div class="card-body">
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Merit Increase - John Smith</p>
                        <p class="text-xs text-gray-500">Grade 5 → Grade 6</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Salary Adjustment - Sarah Johnson</p>
                        <p class="text-xs text-gray-500">Market rate adjustment</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="compensation.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Salary Structures</p>
            </a>
            <a href="compensation.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Merit Increases</p>
            </a>
            <a href="compensation.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Pay Equity</p>
            </a>
            <a href="analytics.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Analytics</p>
            </a>
        </div>
    </div>
</div>
';

// Render the page
echo SharedComponents::renderPage('Compensation Dashboard', $content, $userInfo, 'Compensation Manager', 'dashboard');
?>