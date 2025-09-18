<?php
// Hospital Employee - Benefits Center
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../routing/rbac.php';

$activeId = 'benefits-center';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Benefits Center</title>
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
                            <h1 class="text-lg font-semibold">Benefits Center</h1>
                            <p class="text-xs text-slate-500 mt-1">View active enrollments, contributions, and claim
                                history</p>
                        </div>
                        <div class="grid lg:grid-cols-2 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="font-semibold mb-2">Active Enrollments</div>
                                <div class="text-sm text-slate-600 dark:text-slate-300">No active enrollments.</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="font-semibold mb-2">Claim History</div>
                                <div class="text-sm text-slate-600 dark:text-slate-300">No claims filed.</div>
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