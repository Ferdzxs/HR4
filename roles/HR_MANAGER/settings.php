<?php
// HR Manager Settings Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../routing/rbac.php';



$activeId = 'settings';



$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Settings</title>
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
                            <h1 class="text-lg font-semibold">Settings</h1>
                            <p class="text-xs text-slate-500 mt-1">Users, roles, permissions, and system configuration
                            </p>
                        </div>
                        <div class="space-y-4">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">User Management
                                </div>
                                <div class="p-4 text-sm text-slate-600 dark:text-slate-300">Manage users, roles,
                                    permissions.</div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">System Configuration
                                </div>
                                <div class="p-4 text-sm text-slate-600 dark:text-slate-300">Global settings and
                                    preferences.</div>
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