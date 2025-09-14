<?php
// Payroll Administrator Tax Management Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../shared/styles.php';
include_once __DIR__ . '/../../shared/scripts.php';
include_once __DIR__ . '/../../rbac.php';



$activeId = 'tax';



$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Tax Management</title>
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
                <h1 class="text-lg font-semibold">Tax Management</h1>
                <p class="text-xs text-slate-500 mt-1">Withholding, filings, and regulatory updates</p>
                </div>
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Regulatory Updates</div>
                <div class="p-4 text-sm text-slate-600 dark:text-slate-300">Latest updates placeholder.</div>
                </div>
                <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                <div class="flex gap-2">
                <select id="taxPeriod" class="flex h-10 w-full rounded-md border border-[hsl(var(--input))] bg-transparent px-3 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2">
                <option value="">All Periods</option>
                <option value="q1">Q1</option>
                <option value="q2">Q2</option>
                <option value="q3">Q3</option>
                <option value="q4">Q4</option>
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
                <th class="text-left px-3 py-2 font-semibold">Withholding</th>
                <th class="text-left px-3 py-2 font-semibold">Filings</th>
                <th class="text-left px-3 py-2 font-semibold">Status</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                <td class="px-3 py-6 text-center text-slate-500" colspan="4">
                <div class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
                <div class="text-sm font-medium">No tax records</div>
                <div class="text-xs text-slate-500 mt-1">Configure tax rules and process filings.</div>
                <div class="mt-3">
                <button class="border border-[hsl(var(--border))] bg-transparent hover:bg-[hsl(var(--accent))] inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">Configure</button>
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
    <?php include __DIR__ . '/../../shared/scripts.php'; ?>
</body>
</html>
