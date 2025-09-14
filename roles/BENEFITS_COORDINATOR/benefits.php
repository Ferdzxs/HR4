<?php
// Benefits Coordinator HMO Management Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../shared/styles.php';
include_once __DIR__ . '/../../shared/scripts.php';
include_once __DIR__ . '/../../rbac.php';



$activeId = 'benefits';

$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - HMO Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php include __DIR__ . '/../../shared/styles.php'; ?>
</head>
<body>
    <div id="app" class="h-screen">
        <div class="h-full flex flex-col">
            <?php echo renderHeader($user, $sidebarCollapsed); ?>
            <div class="flex-1 grid <?php echo $sidebarCollapsed ? 'lg:grid-cols-[72px_1fr]' : 'lg:grid-cols-[260px_1fr]'; ?>">
                <?php echo renderSidebar($sidebarItems, $activeId, $sidebarCollapsed); ?>
                <main class="overflow-y-auto">
                <section class="p-4 lg:p-6 space-y-4">
                <div>
                <h1 class="text-lg font-semibold">HMO Management</h1>
                <p class="text-xs text-slate-500 mt-1">Plans, enrollments, claims and providers</p>
                </div>
                <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                <div class="text-xs text-slate-500 mb-1">Active Enrollments</div>
                <div class="text-2xl font-semibold">0</div>
                </div>
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                <div class="text-xs text-slate-500 mb-1">Claims Pending</div>
                <div class="text-2xl font-semibold">0</div>
                </div>
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                <div class="text-xs text-slate-500 mb-1">Monthly Premium</div>
                <div class="text-2xl font-semibold">₱0</div>
                </div>
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                <div class="text-xs text-slate-500 mb-1">Providers</div>
                <div class="text-2xl font-semibold">0</div>
                </div>
                </div>
                <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                <div class="flex gap-2">
                <select id="planFilter" class="flex h-10 w-full rounded-md border border-[hsl(var(--input))] bg-transparent px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2">
                <option value="">All Plans</option>
                </select>
                <select id="enrollStatus" class="flex h-10 w-full rounded-md border border-[hsl(var(--input))] bg-transparent px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
                </select>
                </div>
                <div>
                <button class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">Add Enrollment</button>
                </div>
                </div>
                </div>
                <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                <thead class="bg-[hsl(var(--secondary))]">
                <tr>
                <th class="text-left px-3 py-2 font-semibold">Employee</th>
                <th class="text-left px-3 py-2 font-semibold">Plan</th>
                <th class="text-left px-3 py-2 font-semibold">Status</th>
                <th class="text-left px-3 py-2 font-semibold">Since</th>
                <th class="text-left px-3 py-2 font-semibold">Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                <td class="px-3 py-6 text-center text-slate-500" colspan="5">
                <div class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
                <div class="text-sm font-medium">No enrollments found</div>
                <div class="text-xs text-slate-500 mt-1">Add an employee to an HMO plan to get started.</div>
                <div class="mt-3">
                <button class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">Add Enrollment</button>
                </div>
                </div>
                </td>
                </tr>
                </tbody>
                </table>
                </div>
                </div>
                </section>
                </main>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../../shared/scripts.php'; ?>
</body>
</html>
