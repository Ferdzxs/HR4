<?php
// Hospital Employee Payslips Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../shared/styles.php';
include_once __DIR__ . '/../../shared/scripts.php';
include_once __DIR__ . '/../../rbac.php';



$activeId = 'payslips';



$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Payslips</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php include __DIR__ . '/../../shared/styles.php'; ?>
</head>
<body>
    <div id="app" class="h-screen">
        <div class="h-full flex flex-col">
            <?php echo renderHeader($user, $sidebarCollapsed); ?>
            <?php echo renderSidebar($sidebarItems, $activeId, $sidebarCollapsed); ?>
            <main class="flex-1 overflow-y-auto">
                
                <section class="p-4 lg:p-6 space-y-4">
                <div>
                <h1 class="text-lg font-semibold">Payslips</h1>
                <p class="text-xs text-slate-500 mt-1">Latest payslips with detailed breakdowns</p>
                </div>
                <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                <div class="flex gap-2">
                <select id="year" class="flex h-10 w-full rounded-md border border-[hsl(var(--input))] bg-transparent px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2">
                <option value="">All Years</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
                </select>
                </div>
                <div></div>
                </div>
                </div>
                <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                <thead class="bg-[hsl(var(--secondary))]">
                <tr>
                <th class="text-left px-3 py-2 font-semibold">Period</th>
                <th class="text-left px-3 py-2 font-semibold">Net Pay</th>
                <th class="text-left px-3 py-2 font-semibold">Status</th>
                <th class="text-left px-3 py-2 font-semibold">Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                <td class="px-3 py-6 text-center text-slate-500" colspan="4">
                <div class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
                <div class="text-sm font-medium">No payslips available</div>
                <div class="text-xs text-slate-500 mt-1">Your payslips will appear after processing.</div>
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
    <?php include __DIR__ . '/../../shared/scripts.php'; ?>
</body>
</html>
