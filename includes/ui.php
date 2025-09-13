<?php
// UI Helper class for generating HTML components

class UI
{
    public static function button($text, $options = [])
    {
        $variant = $options['variant'] ?? 'default';
        $size = $options['size'] ?? 'md';
        $id = isset($options['id']) ? 'id="' . htmlspecialchars($options['id']) . '"' : '';
        $extra = $options['extra'] ?? '';
        $type = $options['type'] ?? 'button';
        $disabled = isset($options['disabled']) && $options['disabled'] ? 'disabled' : '';

        $baseClasses = 'inline-flex items-center justify-center rounded-md font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none';

        $variantClasses = [
            'default' => 'bg-primary text-primary-foreground hover:bg-primary/90',
            'destructive' => 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
            'outline' => 'border border-input hover:bg-accent hover:text-accent-foreground',
            'secondary' => 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
            'ghost' => 'hover:bg-accent hover:text-accent-foreground',
            'link' => 'text-primary underline-offset-4 hover:underline',
        ];

        $sizeClasses = [
            'sm' => 'h-9 px-3 text-sm',
            'md' => 'h-10 px-4 py-2',
            'lg' => 'h-11 px-8',
            'icon' => 'h-10 w-10',
        ];

        $classes = $baseClasses . ' ' . ($variantClasses[$variant] ?? $variantClasses['default']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);

        return sprintf(
            '<button type="%s" %s class="%s %s" %s>%s</button>',
            $type,
            $id,
            $classes,
            $extra,
            $disabled,
            $text
        );
    }

    public static function input($options = [])
    {
        $type = $options['type'] ?? 'text';
        $id = isset($options['id']) ? 'id="' . htmlspecialchars($options['id']) . '"' : '';
        $name = isset($options['name']) ? 'name="' . htmlspecialchars($options['name']) . '"' : '';
        $placeholder = isset($options['placeholder']) ? 'placeholder="' . htmlspecialchars($options['placeholder']) . '"' : '';
        $value = isset($options['value']) ? 'value="' . htmlspecialchars($options['value']) . '"' : '';
        $extra = $options['extra'] ?? '';
        $required = isset($options['required']) && $options['required'] ? 'required' : '';

        $classes = 'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

        return sprintf(
            '<input type="%s" %s %s %s %s class="%s %s" %s />',
            $type,
            $id,
            $name,
            $placeholder,
            $value,
            $classes,
            $extra,
            $required
        );
    }

    public static function select($options = [])
    {
        $id = isset($options['id']) ? 'id="' . htmlspecialchars($options['id']) . '"' : '';
        $name = isset($options['name']) ? 'name="' . htmlspecialchars($options['name']) . '"' : '';
        $extra = $options['extra'] ?? '';
        $selectOptions = $options['options'] ?? [];

        $classes = 'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

        $optionHtml = '';
        foreach ($selectOptions as $option) {
            $value = htmlspecialchars($option['value']);
            $label = htmlspecialchars($option['label']);
            $selected = isset($option['selected']) && $option['selected'] ? 'selected' : '';
            $optionHtml .= sprintf('<option value="%s" %s>%s</option>', $value, $selected, $label);
        }

        return sprintf(
            '<select %s %s class="%s %s">%s</select>',
            $id,
            $name,
            $classes,
            $extra,
            $optionHtml
        );
    }

    public static function card($options = [])
    {
        $title = $options['title'] ?? '';
        $content = $options['content'] ?? '';
        $footer = $options['footer'] ?? '';
        $extra = $options['extra'] ?? '';

        $titleHtml = $title ? '<div class="flex flex-col space-y-1.5 p-6"><h3 class="text-2xl font-semibold leading-none tracking-tight">' . htmlspecialchars($title) . '</h3></div>' : '';
        $contentHtml = $content ? '<div class="p-6 pt-0">' . $content . '</div>' : '';
        $footerHtml = $footer ? '<div class="flex items-center p-6 pt-0">' . $footer . '</div>' : '';

        return sprintf(
            '<div class="rounded-lg border bg-card text-card-foreground shadow-sm %s">%s%s%s</div>',
            $extra,
            $titleHtml,
            $contentHtml,
            $footerHtml
        );
    }

    public static function table($options = [])
    {
        $headers = $options['headers'] ?? [];
        $rows = $options['rows'] ?? [];
        $empty = $options['empty'] ?? '';
        $controls = $options['controls'] ?? '';

        $headerHtml = '';
        if (!empty($headers)) {
            $headerHtml = '<thead><tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">';
            foreach ($headers as $header) {
                $headerHtml .= '<th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">' . htmlspecialchars($header) . '</th>';
            }
            $headerHtml .= '</tr></thead>';
        }

        $bodyHtml = '';
        if (!empty($rows)) {
            $bodyHtml = '<tbody>';
            foreach ($rows as $row) {
                $bodyHtml .= '<tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">';
                foreach ($row as $cell) {
                    $bodyHtml .= '<td class="p-4 align-middle">' . $cell . '</td>';
                }
                $bodyHtml .= '</tr>';
            }
            $bodyHtml .= '</tbody>';
        } else {
            $bodyHtml = '<tbody><tr><td colspan="' . count($headers) . '" class="p-8 text-center text-muted-foreground">' . $empty . '</td></tr></tbody>';
        }

        $controlsHtml = $controls ? '<div class="flex items-center justify-between p-4">' . $controls . '</div>' : '';

        return sprintf(
            '<div class="rounded-md border">%s<div class="relative overflow-auto"><table class="w-full caption-bottom text-sm">%s%s</table></div></div>',
            $controlsHtml,
            $headerHtml,
            $bodyHtml
        );
    }

    public static function kpi($options = [])
    {
        $label = $options['label'] ?? '';
        $value = $options['value'] ?? '';
        $sub = $options['sub'] ?? '';

        $subHtml = $sub ? '<p class="text-xs text-muted-foreground">' . htmlspecialchars($sub) . '</p>' : '';

        return sprintf(
            '<div class="rounded-lg border bg-card text-card-foreground shadow-sm p-6"><div class="flex flex-col space-y-1.5"><h3 class="text-2xl font-semibold leading-none tracking-tight">%s</h3><p class="text-sm text-muted-foreground">%s</p>%s</div></div>',
            htmlspecialchars($value),
            htmlspecialchars($label),
            $subHtml
        );
    }

    public static function empty($options = [])
    {
        $title = $options['title'] ?? 'No data';
        $description = $options['description'] ?? '';
        $action = $options['action'] ?? '';

        $actionHtml = $action ? '<div class="mt-4">' . $action . '</div>' : '';

        return sprintf(
            '<div class="flex flex-col items-center justify-center py-8 text-center"><h3 class="text-lg font-semibold">%s</h3><p class="text-sm text-muted-foreground">%s</p>%s</div>',
            htmlspecialchars($title),
            htmlspecialchars($description),
            $actionHtml
        );
    }

    public static function sheet($options = [])
    {
        $open = $options['open'] ?? false;
        $content = $options['content'] ?? '';

        $openClass = $open ? 'fixed inset-0 z-50' : 'hidden';
        $overlayClass = 'fixed inset-0 z-50 bg-background/80 backdrop-blur-sm';
        $contentClass = 'fixed inset-y-0 left-0 z-50 h-full w-3/4 border-r bg-background p-6 sm:max-w-sm';

        return sprintf(
            '<div class="%s" data-sheet><div class="%s" data-sheet-overlay></div><div class="%s">%s</div></div>',
            $openClass,
            $overlayClass,
            $contentClass,
            $content
        );
    }

    public static function iconGlyph($letter)
    {
        return sprintf(
            '<span class="inline-flex items-center justify-center w-5 h-5 rounded bg-slate-200/60 dark:bg-slate-800/80 text-slate-700 dark:text-slate-200 text-[11px] font-semibold">%s</span>',
            strtoupper($letter)
        );
    }

    public static function renderShell($contentHtml = '', $activeId = '', $options = [])
    {
        global $auth, $rbac;

        $hideChrome = $options['hideChrome'] ?? false;
        $user = $auth ? $auth->getUserInfo() : null;
        $role = $user ? $user['role_name'] : null;
        $sidebar = $role ? $rbac::getSidebarItems($role) : [];
        $sidebarCollapsed = $_SESSION['sidebar_collapsed'] ?? false;

        if ($hideChrome) {
            return sprintf(
                '<div class="h-full flex items-center justify-center p-6 bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-950">
                    <div class="w-full max-w-md">%s</div>
                </div>',
                $contentHtml
            );
        }

        $gridCols = $sidebarCollapsed ? 'lg:grid-cols-[72px_1fr]' : 'lg:grid-cols-[260px_1fr]';

        $sidebarHtml = '';
        foreach ($sidebar as $item) {
            $activeClass = $activeId === $item['id'] ? 'bg-[hsl(var(--accent))]' : '';
            $hiddenClass = $sidebarCollapsed ? 'hidden' : '';
            $sidebarHtml .= sprintf(
                '<a href="?page=%s" class="group flex items-center gap-2 px-2 py-2 rounded-md text-sm %s hover:bg-[hsl(var(--accent))]">
                    %s
                    <span class="%s">%s</span>
                </a>',
                $item['id'],
                $activeClass,
                self::iconGlyph($item['icon']),
                $hiddenClass,
                htmlspecialchars($item['label'])
            );
        }

        $userInfo = $user ? sprintf(
            '<div class="text-sm">%s · <span class="text-slate-500">%s</span></div>',
            htmlspecialchars($user['username']),
            htmlspecialchars($user['role_name'])
        ) : '';

        $logoutButton = $user ? self::button('Logout', [
            'variant' => 'outline',
            'size' => 'sm',
            'id' => 'btnLogout',
            'onclick' => 'logout()'
        ]) : '';

        return sprintf(
            '<div class="h-full flex flex-col">
                <header class="sticky top-0 z-20 flex items-center gap-2 px-4 py-2 border-b border-[hsl(var(--border))] bg-[hsl(var(--background))]/70 backdrop-blur">
                    %s
                    %s
                    <div class="flex-1 flex items-center gap-2">
                        <div class="font-semibold">HR4</div>
                        <div class="text-xs text-slate-500">Compensation & HR Intelligence</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <button type="button" id="themeToggle" class="text-slate-600 hover:text-slate-900 dark:text-slate-200">
                                <span class="sr-only">Theme</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden dark:block" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M21 12a9 9 0 11-9-9 7 7 0 009 9z"/>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="4"/>
                                    <path d="M12 2v2m0 16v2M2 12h2m16 0h2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M4.93 19.07l1.41-1.41m11.32-11.32l1.41-1.41"/>
                                </svg>
                            </button>
                        </div>
                        %s
                        %s
                    </div>
                </header>
                <div class="flex-1 grid %s">
                    <aside id="sidebar" data-collapsed="%s" class="hidden lg:block border-r border-[hsl(var(--border))] overflow-y-auto">
                        <nav class="p-2">%s</nav>
                    </aside>
                    <main class="overflow-y-auto">%s</main>
                </div>
            </div>',
            self::button('<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h18M3 12h18M3 18h18"/></svg>', [
                'variant' => 'ghost',
                'size' => 'sm',
                'id' => 'btnSidebar',
                'extra' => 'lg:hidden'
            ]),
            self::button($sidebarCollapsed ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M15 19l-7-7 7-7"/></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M9 5l7 7-7 7"/></svg>', [
                'variant' => 'ghost',
                'size' => 'sm',
                'id' => 'btnCollapse',
                'extra' => 'hidden lg:inline-flex',
                'onclick' => 'toggleSidebar()'
            ]),
            $userInfo,
            $logoutButton,
            $gridCols,
            $sidebarCollapsed ? 'true' : 'false',
            $sidebarHtml,
            $contentHtml
        );
    }
}
?>