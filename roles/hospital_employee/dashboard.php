<?php
/**
 * Hospital Employee Dashboard - Frontend Only
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
$auth->requireRole('Hospital Employee');
$userInfo = $auth->getCurrentUser();

// Build dashboard content
$content = '
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">My Dashboard</h1>
    <p class="text-gray-600">Welcome back, ' . htmlspecialchars($userInfo['employee_name']) . '! Here\'s your personal information and quick access.</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid mb-8">
    ' . SharedComponents::renderStatsCard('Current Salary', '₱45,000', 3.2, 'currency-dollar', 'blue') . '
    ' . SharedComponents::renderStatsCard('Leave Balance', '12 days', -2.1, 'calendar', 'green') . '
    ' . SharedComponents::renderStatsCard('Benefits Active', '3 plans', 0, 'heart', 'purple') . '
    ' . SharedComponents::renderStatsCard('Documents', '8 files', 1.5, 'folder', 'orange') . '
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Recent Payslip -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Payslip</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">December 15, 2024</p>
                        <p class="text-xs text-gray-500">Mid-month payroll</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-blue-600">₱42,500.00</p>
                        <p class="text-xs text-gray-500">Net Pay</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-600">Basic Salary</p>
                        <p class="font-semibold">₱45,000.00</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Deductions</p>
                        <p class="font-semibold text-red-600">₱2,500.00</p>
                    </div>
                </div>
                <div class="pt-2">
                    <a href="payslips.php" class="btn btn-primary btn-sm w-full">View All Payslips</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Benefits Status -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Benefits Status</h3>
        </div>
        <div class="card-body">
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Maxicare Gold</p>
                        <p class="text-xs text-gray-500">Health Insurance</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Active</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">SSS</p>
                        <p class="text-xs text-gray-500">Social Security</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Active</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Pag-IBIG</p>
                        <p class="text-xs text-gray-500">Housing Fund</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">Active</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leave Balance -->
<div class="card mb-8">
    <div class="card-header">
        <h3 class="card-title">Leave Balance</h3>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-2xl font-bold text-blue-600">12</p>
                <p class="text-sm text-gray-600">Vacation Leave</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-2xl font-bold text-green-600">5</p>
                <p class="text-sm text-gray-600">Sick Leave</p>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-2xl font-bold text-purple-600">3</p>
                <p class="text-sm text-gray-600">Personal Leave</p>
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
            <a href="profile.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">My Profile</p>
            </a>
            <a href="payslips.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Payslips</p>
            </a>
            <a href="benefits.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Benefits</p>
            </a>
            <a href="policies.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Policies</p>
            </a>
        </div>
    </div>
</div>
';

// Render the page
echo SharedComponents::renderPage('My Dashboard', $content, $userInfo, 'Hospital Employee', 'dashboard');
?>