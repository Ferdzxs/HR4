<?php
/**
 * Payroll Administrator Dashboard - Frontend Only
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
$auth->requireRole('Payroll Administrator');
$userInfo = $auth->getCurrentUser();

// Build dashboard content
$content = '
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Payroll Dashboard</h1>
    <p class="text-gray-600">Welcome back, ' . htmlspecialchars($userInfo['employee_name']) . '! Manage payroll processing and compliance.</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid mb-8">
    ' . SharedComponents::renderStatsCard('Processed This Month', '1,247', 2.1, 'currency-dollar', 'blue') . '
    ' . SharedComponents::renderStatsCard('Pending Processing', '23', -15.3, 'clock', 'orange') . '
    ' . SharedComponents::renderStatsCard('Tax Compliance', '100%', 0, 'check', 'green') . '
    ' . SharedComponents::renderStatsCard('Bank Files', '12', 0, 'banknotes', 'purple') . '
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Payroll Calendar -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payroll Calendar</h3>
        </div>
        <div class="card-body">
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">December 15, 2024</p>
                        <p class="text-xs text-gray-500">Mid-month payroll - Completed</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Completed</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">December 31, 2024</p>
                        <p class="text-xs text-gray-500">End-month payroll - In Progress</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Processing</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">January 15, 2025</p>
                        <p class="text-xs text-gray-500">Next payroll cycle</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">Scheduled</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Processing Status -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Processing Status</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Salary Calculations</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                        <span class="text-sm font-medium">100%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Tax Deductions</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 100%"></div>
                        </div>
                        <span class="text-sm font-medium">100%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Bank File Generation</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 75%"></div>
                        </div>
                        <span class="text-sm font-medium">75%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Payslip Distribution</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-600 h-2 rounded-full" style="width: 45%"></div>
                        </div>
                        <span class="text-sm font-medium">45%</span>
                    </div>
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
            <a href="payroll.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Process Payroll</p>
            </a>
            <a href="payroll.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Generate Payslips</p>
            </a>
            <a href="payroll.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Bank Files</p>
            </a>
            <a href="analytics.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Reports</p>
            </a>
        </div>
    </div>
</div>
';

// Render the page
echo SharedComponents::renderPage('Payroll Dashboard', $content, $userInfo, 'Payroll Administrator', 'dashboard');
?>