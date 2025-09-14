<?php
// Shared sidebar component
function renderSidebar($sidebarItems = [], $activeId = '', $sidebarCollapsed = false) {
    $gridCols = $sidebarCollapsed ? "lg:grid-cols-[72px_1fr]" : "lg:grid-cols-[260px_1fr]";
    
    $sidebarHtml = '';
    foreach ($sidebarItems as $item) {
        $isActive = $activeId === $item['id'] ? 'bg-[hsl(var(--accent))]' : '';
        $labelDisplay = $sidebarCollapsed ? 'hidden' : '';
        
        $sidebarHtml .= '
            <a href="?page=' . $item['id'] . '" data-id="' . $item['id'] . '" class="group flex items-center gap-2 px-2 py-2 rounded-md text-sm ' . $isActive . ' hover:bg-[hsl(var(--accent))]">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-slate-200/60 dark:bg-slate-800/80 text-slate-700 dark:text-slate-200 text-[11px] font-semibold">' . strtoupper($item['icon']) . '</span>
                <span class="' . $labelDisplay . '">' . htmlspecialchars($item['label']) . '</span>
            </a>
        ';
    }
    
    return '
        <aside id="sidebar" data-collapsed="' . ($sidebarCollapsed ? 'true' : 'false') . '" class="hidden lg:block border-r border-[hsl(var(--border))] overflow-y-auto">
            <nav class="p-2">
                ' . $sidebarHtml . '
            </nav>
        </aside>
    ';
}
?>
