<?php
// Hospital Management Executive Dashboard Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../shared/styles.php';
include_once __DIR__ . '/../../shared/scripts.php';
include_once __DIR__ . '/../../rbac.php';



$activeId = 'executive';



$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Executive Dashboard</title>
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
                <h1 class="text-lg font-semibold">Executive Dashboard</h1>
                <p class="text-xs text-slate-500 mt-1">High-level workforce, cost, and compliance KPIs</p>
                </div>
                <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                <div class="text-xs text-slate-500 mb-1">Total Headcount</div>
                <div class="text-2xl font-semibold">—</div>
                </div>
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                <div class="text-xs text-slate-500 mb-1">Total Cost</div>
                <div class="text-2xl font-semibold">—</div>
                </div>
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                <div class="text-xs text-slate-500 mb-1">Turnover</div>
                <div class="text-2xl font-semibold">—</div>
                </div>
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                <div class="text-xs text-slate-500 mb-1">Compliance</div>
                <div class="text-2xl font-semibold">—</div>
                </div>
                </div>
                <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Strategic Indicators</div>
                <div class="p-4 text-sm text-slate-600 dark:text-slate-300">KPIs and forecasts.</div>
                </div>
                </section>

                </main>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../../shared/scripts.php'; ?>
</body>
</html>
