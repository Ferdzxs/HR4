<?php
/**
 * Shared Components
 * HR4 Compensation & Intelligence System
 */

require_once 'header.php';
require_once 'sidebar.php';

class SharedComponents {
    
    public static function renderPage($title, $content, $userInfo, $role, $activePage = 'dashboard') {
        $header = renderHeader($userInfo);
        $sidebar = renderSidebar($role, $activePage);
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($title); ?> - HR4 Intelligence</title>
            <link rel="stylesheet" href="../shared/styles.css">
            <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E🏥%3C/text%3E%3C/svg%3E">
        </head>
        <body>
            <div class="app-container">
                <?php echo $sidebar; ?>
                
                <div class="main-content" id="mainContent">
                    <?php echo $header; ?>
                    
                    <main class="content">
                        <?php echo $content; ?>
                    </main>
                </div>
            </div>
            
            <script src="../shared/scripts.js"></script>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    public static function renderStatsCard($title, $value, $change = null, $icon = 'chart-bar', $color = 'blue') {
        $changeClass = '';
        $changeIcon = '';
        
        if ($change !== null) {
            if ($change > 0) {
                $changeClass = 'positive';
                $changeIcon = '↗';
            } elseif ($change < 0) {
                $changeClass = 'negative';
                $changeIcon = '↘';
            }
        }
        
        ob_start();
        ?>
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-<?php echo $color; ?>-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-<?php echo $color; ?>-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <?php echo getIcon($icon); ?>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-600"><?php echo htmlspecialchars($title); ?></h3>
                </div>
                <?php if ($change !== null): ?>
                    <span class="stat-change <?php echo $changeClass; ?>">
                        <?php echo $changeIcon; ?> <?php echo abs($change); ?>%
                    </span>
                <?php endif; ?>
            </div>
            <div class="stat-value text-<?php echo $color; ?>-600">
                <?php echo htmlspecialchars($value); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public static function renderDataTable($headers, $data, $actions = []) {
        ob_start();
        ?>
        <div class="card">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <?php foreach ($headers as $header): ?>
                                <th data-sortable><?php echo htmlspecialchars($header); ?></th>
                            <?php endforeach; ?>
                            <?php if (!empty($actions)): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <?php foreach ($row as $key => $cell): ?>
                                    <?php if ($key !== 'id'): ?>
                                        <td><?php echo htmlspecialchars($cell); ?></td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if (!empty($actions)): ?>
                                    <td>
                                        <div class="flex gap-2">
                                            <?php foreach ($actions as $action): ?>
                                                <a href="<?php echo $action['url'] . ($row['id'] ?? ''); ?>" 
                                                   class="btn btn-sm <?php echo $action['class']; ?>"
                                                   data-tooltip="<?php echo $action['tooltip']; ?>">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <?php echo getIcon($action['icon']); ?>
                                                    </svg>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public static function renderModal($id, $title, $content, $actions = []) {
        ob_start();
        ?>
        <div id="<?php echo $id; ?>" class="modal hidden fixed inset-0 z-50 overflow-y-auto">
            <div class="modal-backdrop fixed inset-0 bg-black bg-opacity-50"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($title); ?></h3>
                    </div>
                    <div class="px-6 py-4">
                        <?php echo $content; ?>
                    </div>
                    <?php if (!empty($actions)): ?>
                        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                            <?php foreach ($actions as $action): ?>
                                <button type="button" 
                                        class="btn <?php echo $action['class']; ?>"
                                        data-modal-close>
                                    <?php echo htmlspecialchars($action['label']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public static function renderBreadcrumb($items) {
        ob_start();
        ?>
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <?php foreach ($items as $index => $item): ?>
                    <li class="inline-flex items-center">
                        <?php if ($index > 0): ?>
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        <?php endif; ?>
                        <?php if (isset($item['url'])): ?>
                            <a href="<?php echo $item['url']; ?>" class="text-gray-700 hover:text-gray-900">
                                <?php echo htmlspecialchars($item['label']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-gray-500"><?php echo htmlspecialchars($item['label']); ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php
        return ob_get_clean();
    }
    
    public static function renderAlert($message, $type = 'info', $dismissible = true) {
        $icons = [
            'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'error' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>',
            'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        ];
        
        ob_start();
        ?>
        <div class="alert alert-<?php echo $type; ?>">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <?php echo $icons[$type] ?? $icons['info']; ?>
                </svg>
                <span><?php echo htmlspecialchars($message); ?></span>
                <?php if ($dismissible): ?>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-lg font-bold">&times;</button>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Helper function for icons (reused from sidebar.php)
function getIcon($iconName) {
    $icons = [
        'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"/>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>',
        'chart-bar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'edit' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
        'trash' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>',
        'eye' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
        'plus' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>',
        'search' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
        'filter' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>',
        'download' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
        'upload' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>',
        'refresh' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>',
        'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        'clock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
        'x' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>'
    ];
    
    return $icons[$iconName] ?? $icons['chart-bar'];
}
?>
