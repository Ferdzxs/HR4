<?php
/**
 * Department Head Dashboard - Frontend Only
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
$auth->requireRole('Department Head');
$userInfo = $auth->getCurrentUser();

// Build dashboard content
$content = '
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Department Dashboard</h1>
    <p class="text-gray-600">Welcome back, ' . htmlspecialchars($userInfo['employee_name']) . '! Monitor your team and department performance.</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid mb-8">
    ' . SharedComponents::renderStatsCard('Team Members', '24', 2.1, 'users', 'blue') . '
    ' . SharedComponents::renderStatsCard('Budget Used', '78%', 5.2, 'calculator', 'orange') . '
    ' . SharedComponents::renderStatsCard('Pending Approvals', '7', -12.5, 'check', 'green') . '
    ' . SharedComponents::renderStatsCard('Team Performance', '92%', 3.1, 'chart-bar', 'purple') . '
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Team Overview -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Team Overview</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">JS</div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">John Smith</p>
                            <p class="text-xs text-gray-500">Senior Developer</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Active</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">SJ</div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Sarah Johnson</p>
                            <p class="text-xs text-gray-500">Project Manager</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Active</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">MB</div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Mike Brown</p>
                            <p class="text-xs text-gray-500">UI/UX Designer</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">On Leave</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Budget Status -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Budget Status</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Q4 Budget</span>
                    <span class="text-sm font-medium text-gray-900">₱2.4M / ₱3.0M</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-orange-500 h-2 rounded-full" style="width: 78%"></div>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-600">Remaining</p>
                        <p class="font-semibold text-green-600">₱600K</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Days Left</p>
                        <p class="font-semibold text-blue-600">15 days</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Approvals -->
<div class="card mb-8">
    <div class="card-header">
        <h3 class="card-title">Pending Approvals</h3>
    </div>
    <div class="card-body">
        <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-gray-900">Leave Request - Lisa Davis</p>
                    <p class="text-xs text-gray-500">Vacation Leave • Dec 20-25, 2024</p>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-sm btn-primary">Approve</button>
                    <button class="btn btn-sm btn-secondary">Reject</button>
                </div>
            </div>
            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-gray-900">Overtime Request - David Wilson</p>
                    <p class="text-xs text-gray-500">Weekend Overtime • 8 hours</p>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-sm btn-primary">Approve</button>
                    <button class="btn btn-sm btn-secondary">Reject</button>
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
            <a href="team.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Team Members</p>
            </a>
            <a href="approvals.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Approvals</p>
            </a>
            <a href="analytics.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Analytics</p>
            </a>
            <a href="analytics.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Reports</p>
            </a>
        </div>
    </div>
</div>
';

// Render the page
echo SharedComponents::renderPage('Department Dashboard', $content, $userInfo, 'Department Head', 'dashboard');
?>