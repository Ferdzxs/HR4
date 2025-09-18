<?php
// Compensation Manager Budgeting Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../routing/rbac.php';

$activeId = 'budget';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Compensation Budgeting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/HR4_COMPEN&INTELLI/shared/styles.css">
</head>

<body>
    <div id="app" class="h-screen">
        <div class="h-full flex flex-col">
            <?php echo renderHeader($user, $sidebarCollapsed); ?>
            <div
                class="flex-1 grid <?php echo $sidebarCollapsed ? 'lg:grid-cols-[72px_1fr]' : 'lg:grid-cols-[260px_1fr]'; ?>">
                <?php echo renderSidebar($sidebarItems, $activeId, $sidebarCollapsed); ?>
                <main class="overflow-y-auto">
                    <section class="p-4 lg:p-6 space-y-4">
                        <div>
                            <h1 class="text-lg font-semibold">Compensation Budgeting</h1>
                            <p class="text-xs text-slate-500 mt-1">Allocate budgets to departments, track utilization
                                and variances</p>
                        </div>

                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <button
                                            class="border border-[hsl(var(--border))] bg-transparent hover:bg-[hsl(var(--accent))] inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium h-9 px-3">Allocate</button>
                                    </div>
                                    <div>
                                        <button
                                            class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium h-9 px-3">New
                                            Budget</button>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Department</th>
                                            <th class="text-left px-3 py-2 font-semibold">Allocated</th>
                                            <th class="text-left px-3 py-2 font-semibold">Used</th>
                                            <th class="text-left px-3 py-2 font-semibold">Variance</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="px-3 py-6 text-center text-slate-500" colspan="5">
                                                <div
                                                    class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
                                                    <div class="text-sm font-medium">No budgets</div>
                                                    <div class="text-xs text-slate-500 mt-1">Set department budgets to
                                                        manage spend.</div>
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
    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
</body>

</html>