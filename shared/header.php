<?php
// Shared header component
function renderHeader($user = null, $sidebarCollapsed = false)
{
    $themeToggleIcon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>';

    $collapseIcon = $sidebarCollapsed
        ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M15 19l-7-7 7-7"/></svg>'
        : '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M9 5l7 7-7 7"/></svg>';

    return '
        <header class="sticky top-0 z-20 flex items-center gap-2 px-4 py-2 border-b border-[hsl(var(--border))] bg-[hsl(var(--background))]/70 backdrop-blur">
            <button id="btnSidebar" class="lg:hidden inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-[hsl(var(--accent))] hover:text-[hsl(var(--accent-foreground))] h-9 px-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 6h18M3 12h18M3 18h18"/>
                </svg>
            </button>
            <button id="btnCollapse" class="hidden lg:inline-flex inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-[hsl(var(--accent))] hover:text-[hsl(var(--accent-foreground))] h-9 px-3">
                ' . $collapseIcon . '
            </button>
            <div class="flex-1 flex items-center gap-2">
                <div class="font-semibold">HR4</div>
                <div class="text-xs text-slate-500">Compensation & HR Intelligence</div>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <button id="themeToggle" class="text-slate-600 hover:text-slate-900 dark:text-slate-200 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:bg-[hsl(var(--accent))] hover:text-[hsl(var(--accent-foreground))] h-9 px-3">
                        <span class="sr-only">Theme</span>
                        ' . $themeToggleIcon . '
                    </button>
                </div>
                ' . ($user ? '<div class="text-sm">' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . ' · <span class="text-slate-500">' . htmlspecialchars($user['role']) . '</span></div>' : '') . '
                ' . ($user ? '<button id="btnLogout" class="border border-[hsl(var(--border))] bg-transparent hover:bg-[hsl(var(--accent))] inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">Logout</button>' : '') . '
            </div>
        </header>
    ';
}
?>