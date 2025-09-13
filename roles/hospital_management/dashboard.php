<?php
/**
 * Hospital Management Dashboard - Frontend Only
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
$auth->requireRole('Hospital Management');
$userInfo = $auth->getCurrentUser();

// Build dashboard content
$content = '
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Executive Dashboard</h1>
    <p class="text-gray-600">Welcome back, ' . htmlspecialchars($userInfo['employee_name']) . '! Strategic insights and workforce intelligence.</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid mb-8">
    ' . SharedComponents::renderStatsCard('Total Workforce', '1,247', 4.2, 'users', 'blue') . '
    ' . SharedComponents::renderStatsCard('Monthly Payroll', '₱12.4M', 6.8, 'currency-dollar', 'green') . '
    ' . SharedComponents::renderStatsCard('Turnover Rate', '3.2%', -1.5, 'trending-up', 'purple') . '
    ' . SharedComponents::renderStatsCard('Compliance Score', '98.5%', 0.8, 'check', 'emerald') . '
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Workforce Overview -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Workforce Overview</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Medical Staff</span>
                    <div class="flex items-center gap-2">
                        <div class="w-32 bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 65%"></div>
                        </div>
                        <span class="text-sm font-medium">65%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Administrative</span>
                    <div class="flex items-center gap-2">
                        <div class="w-32 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 25%"></div>
                        </div>
                        <span class="text-sm font-medium">25%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Support Staff</span>
                    <div class="flex items-center gap-2">
                        <div class="w-32 bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: 10%"></div>
                        </div>
                        <span class="text-sm font-medium">10%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cost Analysis -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Cost Analysis</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Salaries & Wages</span>
                    <span class="text-sm font-medium text-gray-900">₱8.2M</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Benefits & Insurance</span>
                    <span class="text-sm font-medium text-gray-900">₱2.1M</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Training & Development</span>
                    <span class="text-sm font-medium text-gray-900">₱450K</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Other HR Costs</span>
                    <span class="text-sm font-medium text-gray-900">₱650K</span>
                </div>
                <div class="border-t pt-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-900">Total HR Costs</span>
                        <span class="text-lg font-bold text-blue-600">₱11.4M</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Strategic Metrics -->
<div class="card mb-8">
    <div class="card-header">
        <h3 class="card-title">Strategic Metrics</h3>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-900">94.2%</p>
                <p class="text-sm text-gray-600">Employee Satisfaction</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-900">98.5%</p>
                <p class="text-sm text-gray-600">Compliance Rate</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-900">₱45K</p>
                <p class="text-sm text-gray-600">Avg Salary</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-gray-900">3.2%</p>
                <p class="text-sm text-gray-600">Turnover Rate</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Executive Actions</h3>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <a href="analytics.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Workforce Analytics</p>
            </a>
            <a href="reports.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Executive Reports</p>
            </a>
            <a href="analytics.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Strategic Planning</p>
            </a>
        </div>
    </div>
</div>
';

// Render the page
echo SharedComponents::renderPage('Executive Dashboard', $content, $userInfo, 'Hospital Management', 'dashboard');
?>