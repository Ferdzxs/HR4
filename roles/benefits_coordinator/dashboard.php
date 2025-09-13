<?php
/**
 * Benefits Coordinator Dashboard - Frontend Only
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
$auth->requireRole('Benefits Coordinator');
$userInfo = $auth->getCurrentUser();

// Build dashboard content
$content = '
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Benefits Dashboard</h1>
    <p class="text-gray-600">Welcome back, ' . htmlspecialchars($userInfo['employee_name']) . '! Manage benefits administration and claims processing.</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid mb-8">
    ' . SharedComponents::renderStatsCard('Active Enrollments', '1,156', 4.2, 'user-add', 'blue') . '
    ' . SharedComponents::renderStatsCard('Pending Claims', '23', -12.5, 'document-text', 'orange') . '
    ' . SharedComponents::renderStatsCard('HMO Providers', '8', 0, 'building-office', 'green') . '
    ' . SharedComponents::renderStatsCard('Benefits Cost', '₱185K', 6.8, 'currency-dollar', 'purple') . '
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Claims Queue -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Claims Queue</h3>
        </div>
        <div class="card-body">
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">John Smith - Medical</p>
                        <p class="text-xs text-gray-500">Maxicare Gold • ₱15,000</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">New</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Sarah Johnson - Dental</p>
                        <p class="text-xs text-gray-500">PhilCare Premium • ₱8,500</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Review</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Mike Brown - Emergency</p>
                        <p class="text-xs text-gray-500">MediCard Plus • ₱25,000</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Approved</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Enrollment Status -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Enrollment Status</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Maxicare Gold</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 65%"></div>
                        </div>
                        <span class="text-sm font-medium">65%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">PhilCare Premium</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: 45%"></div>
                        </div>
                        <span class="text-sm font-medium">45%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">MediCard Plus</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: 30%"></div>
                        </div>
                        <span class="text-sm font-medium">30%</span>
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
            <a href="benefits.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Manage Benefits</p>
            </a>
            <a href="benefits.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Process Claims</p>
            </a>
            <a href="benefits.php" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <p class="text-sm font-medium">Provider Network</p>
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
echo SharedComponents::renderPage('Benefits Dashboard', $content, $userInfo, 'Benefits Coordinator', 'dashboard');
?>