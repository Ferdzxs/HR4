<?php
// Compensation Manager Compensation Planning Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../shared/styles.php';
include_once __DIR__ . '/../../shared/scripts.php';
include_once __DIR__ . '/../../rbac.php';



$activeId = 'compensation';



$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Compensation Planning</title>
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
                <h1 class="text-lg font-semibold">Compensation Planning</h1>
                <p class="text-xs text-slate-500 mt-1">Budgets, increases, equity, and approvals</p>
                </div>
                <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                <div class="text-xs text-slate-500 mb-1">Budget</div>
                <div class="text-2xl font-semibold">—</div>
                </div>
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                <div class="text-xs text-slate-500 mb-1">Utilization</div>
                <div class="text-2xl font-semibold">—</div>
                </div>
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                <div class="text-xs text-slate-500 mb-1">Pending Approvals</div>
                <div class="text-2xl font-semibold">—</div>
                </div>
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                <div class="text-xs text-slate-500 mb-1">Market Index</div>
                <div class="text-2xl font-semibold">—</div>
                </div>
                </div>
                <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                <div class="flex gap-2">
                <select id="cycle" class="flex h-10 w-full rounded-md border border-[hsl(var(--input))] bg-transparent px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2">
                <option value="">Current Cycle</option>
                <option value="prev">Previous</option>
                </select>
                <select id="approval" class="flex h-10 w-full rounded-md border border-[hsl(var(--input))] bg-transparent px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2">
                <option value="">All Approvals</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                </select>
                </div>
                <div>
                <button class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">New Plan</button>
                </div>
                </div>
                </div>
                <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                <thead class="bg-[hsl(var(--secondary))]">
                <tr>
                <th class="text-left px-3 py-2 font-semibold">Department</th>
                <th class="text-left px-3 py-2 font-semibold">Budget</th>
                <th class="text-left px-3 py-2 font-semibold">Proposed</th>
                <th class="text-left px-3 py-2 font-semibold">Variance</th>
                <th class="text-left px-3 py-2 font-semibold">Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                <td class="px-3 py-6 text-center text-slate-500" colspan="5">
                <div class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
                <div class="text-sm font-medium">No planning cycles</div>
                <div class="text-xs text-slate-500 mt-1">Create a cycle to start planning.</div>
                <div class="mt-3">
                <button class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">New Plan</button>
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
