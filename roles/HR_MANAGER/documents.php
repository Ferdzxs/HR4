<?php
// HR Manager Document Center Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../routing/rbac.php';
include_once __DIR__ . '/../../config/database.php';

$activeId = 'documents';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'upload_document':
                try {
                    $targetDir = "uploads/documents/";
                    if (!file_exists($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }

                    $fileName = basename($_FILES["document"]["name"]);
                    $targetFile = $targetDir . time() . "_" . $fileName;

                    if (move_uploaded_file($_FILES["document"]["tmp_name"], $targetFile)) {
                        $stmt = $pdo->prepare("INSERT INTO employee_documents (employee_id, document_type, file_path, upload_date) VALUES (?, ?, ?, ?)");
                        $stmt->execute([
                            $_POST['employee_id'],
                            $_POST['document_type'],
                            $targetFile,
                            date('Y-m-d')
                        ]);
                        $success = "Document uploaded successfully!";
                    } else {
                        $error = "Error uploading document.";
                    }
                } catch (PDOException $e) {
                    $error = "Error uploading document: " . $e->getMessage();
                }
                break;

            case 'delete_document':
                try {
                    $stmt = $pdo->prepare("SELECT file_path FROM employee_documents WHERE id = ?");
                    $stmt->execute([$_POST['document_id']]);
                    $document = $stmt->fetch();

                    if ($document && file_exists($document['file_path'])) {
                        unlink($document['file_path']);
                    }

                    $stmt = $pdo->prepare("DELETE FROM employee_documents WHERE id = ?");
                    $stmt->execute([$_POST['document_id']]);
                    $success = "Document deleted successfully!";
                } catch (PDOException $e) {
                    $error = "Error deleting document: " . $e->getMessage();
                }
                break;
        }
    }
}

// Fetch document data
try {
    // Employee documents with employee details
    $stmt = $pdo->query("SELECT ed.*, e.first_name, e.last_name, e.employee_number, d.department_name
                        FROM employee_documents ed
                        JOIN employees e ON ed.employee_id = e.id
                        LEFT JOIN departments d ON e.department_id = d.id
                        ORDER BY ed.upload_date DESC, ed.created_at DESC");
    $documents = $stmt->fetchAll();

    // Document access logs (simulated data)
    $stmt = $pdo->query("SELECT al.*, e.first_name, e.last_name, ed.document_type, ed.file_path
                        FROM audit_logs al
                        JOIN employees e ON al.employee_id = e.id
                        LEFT JOIN employee_documents ed ON al.table_name = 'employee_documents' AND al.record_id = ed.id
                        WHERE al.table_name = 'employee_documents'
                        ORDER BY al.created_at DESC
                        LIMIT 50");
    $accessLogs = $stmt->fetchAll();

    // Document versions (simulated data)
    $stmt = $pdo->query("SELECT ed.*, e.first_name, e.last_name, e.employee_number, d.department_name,
                        (SELECT COUNT(*) FROM employee_documents ed2 WHERE ed2.employee_id = ed.employee_id AND ed2.document_type = ed.document_type) as version_count
                        FROM employee_documents ed
                        JOIN employees e ON ed.employee_id = e.id
                        LEFT JOIN departments d ON e.department_id = d.id
                        ORDER BY ed.employee_id, ed.document_type, ed.upload_date DESC");
    $documentVersions = $stmt->fetchAll();

    // Document statistics
    $totalDocuments = $pdo->query("SELECT COUNT(*) FROM employee_documents")->fetchColumn();
    $documentsThisMonth = $pdo->query("SELECT COUNT(*) FROM employee_documents WHERE upload_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();

    // Document types
    $documentTypes = $pdo->query("SELECT document_type, COUNT(*) as count FROM employee_documents GROUP BY document_type ORDER BY count DESC")->fetchAll();

    // Recent uploads
    $recentUploads = $pdo->query("SELECT ed.*, e.first_name, e.last_name, e.employee_number
                                 FROM employee_documents ed
                                 JOIN employees e ON ed.employee_id = e.id
                                 ORDER BY ed.upload_date DESC, ed.created_at DESC
                                 LIMIT 10")->fetchAll();

    // Document access statistics
    $totalAccesses = $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE table_name = 'employee_documents'")->fetchColumn();
    $accessesThisWeek = $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE table_name = 'employee_documents' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();

    // Most accessed documents
    $mostAccessed = $pdo->query("SELECT al.record_id, ed.document_type, e.first_name, e.last_name, COUNT(*) as access_count
                                FROM audit_logs al
                                JOIN employee_documents ed ON al.record_id = ed.id
                                JOIN employees e ON ed.employee_id = e.id
                                WHERE al.table_name = 'employee_documents'
                                GROUP BY al.record_id
                                ORDER BY access_count DESC
                                LIMIT 10")->fetchAll();

} catch (PDOException $e) {
    $documents = [];
    $accessLogs = [];
    $documentVersions = [];
    $totalDocuments = $documentsThisMonth = 0;
    $documentTypes = [];
    $recentUploads = [];
    $totalAccesses = $accessesThisWeek = 0;
    $mostAccessed = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Document Center</title>
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
                            <h1 class="text-lg font-semibold">Document Center</h1>
                            <p class="text-xs text-slate-500 mt-1">Library with access logs and versioning</p>
                        </div>

                        <?php if (isset($success)): ?>
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Quick Actions -->
                        <div class="flex flex-wrap gap-2">
                            <button onclick="openUploadModal()"
                                class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Upload Document
                            </button>
                            <button onclick="openVersionControl()"
                                class="bg-purple-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Version Control
                            </button>
                            <button onclick="openDocumentAnalytics()"
                                class="bg-blue-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Analytics
                            </button>
                            <button onclick="bulkDownload()"
                                class="bg-green-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Bulk Download
                            </button>
                            <button onclick="archiveDocuments()"
                                class="bg-orange-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Archive
                            </button>
                            <button onclick="exportDocuments()"
                                class="bg-slate-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Export List
                            </button>
                        </div>

                        <!-- Document Statistics -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Total Documents</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($totalDocuments); ?></div>
                                <div class="text-xs text-blue-600 mt-1">In library</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">This Month</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($documentsThisMonth); ?>
                                </div>
                                <div class="text-xs text-green-600 mt-1">Uploaded</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Document Types</div>
                                <div class="text-2xl font-semibold"><?php echo count($documentTypes); ?></div>
                                <div class="text-xs text-purple-600 mt-1">Categories</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Storage Used</div>
                                <div class="text-2xl font-semibold">—</div>
                                <div class="text-xs text-orange-600 mt-1">Calculating...</div>
                            </div>
                        </div>

                        <!-- Document Types Overview -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="font-semibold">Document Types</div>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <?php foreach ($documentTypes as $type): ?>
                                        <div class="text-center p-3 bg-slate-50 rounded-lg">
                                            <div class="text-lg font-semibold"><?php echo $type['count']; ?></div>
                                            <div class="text-xs text-slate-600">
                                                <?php echo htmlspecialchars($type['document_type']); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Document Access Logs -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Document Access Logs</div>
                                    <div class="text-sm text-slate-500"><?php echo $totalAccesses; ?> total accesses
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">User</th>
                                            <th class="text-left px-3 py-2 font-semibold">Action</th>
                                            <th class="text-left px-3 py-2 font-semibold">Document</th>
                                            <th class="text-left px-3 py-2 font-semibold">IP Address</th>
                                            <th class="text-left px-3 py-2 font-semibold">Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($accessLogs as $log): ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        <?php echo htmlspecialchars($log['username']); ?></div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 text-xs rounded-full <?php
                                                    echo $log['action'] === 'CREATE' ? 'bg-green-100 text-green-800' :
                                                        ($log['action'] === 'UPDATE' ? 'bg-blue-100 text-blue-800' :
                                                            ($log['action'] === 'DELETE' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'));
                                                    ?>">
                                                        <?php echo htmlspecialchars($log['action']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($log['document_type']); ?></div>
                                                    <div class="text-xs text-gray-500">
                                                        <?php echo htmlspecialchars(basename($log['file_path'])); ?></div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span
                                                        class="text-xs text-gray-500"><?php echo htmlspecialchars($log['ip_address']); ?></span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="text-xs">
                                                        <?php echo date('M j, Y H:i', strtotime($log['created_at'])); ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Document Versioning -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Document Versioning</div>
                                    <div class="text-sm text-slate-500">Track document changes and versions</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Document Type</th>
                                            <th class="text-left px-3 py-2 font-semibold">Versions</th>
                                            <th class="text-left px-3 py-2 font-semibold">Latest Upload</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $groupedVersions = [];
                                        foreach ($documentVersions as $version) {
                                            $key = $version['employee_id'] . '_' . $version['document_type'];
                                            if (!isset($groupedVersions[$key])) {
                                                $groupedVersions[$key] = $version;
                                            }
                                        }
                                        ?>
                                        <?php foreach ($groupedVersions as $version): ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($version['first_name'] . ' ' . $version['last_name']); ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        <?php echo htmlspecialchars($version['employee_number']); ?></div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($version['document_type']); ?></div>
                                                    <div class="text-xs text-gray-500">
                                                        <?php echo htmlspecialchars($version['department_name']); ?></div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                        v<?php echo $version['version_count']; ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="text-xs">
                                                        <?php echo date('M j, Y', strtotime($version['upload_date'])); ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex gap-1">
                                                        <button
                                                            onclick="viewVersions(<?php echo $version['employee_id']; ?>, '<?php echo $version['document_type']; ?>')"
                                                            class="text-blue-600 hover:text-blue-800 text-xs">View
                                                            All</button>
                                                        <button
                                                            onclick="compareVersions(<?php echo $version['employee_id']; ?>, '<?php echo $version['document_type']; ?>')"
                                                            class="text-green-600 hover:text-green-800 text-xs">Compare</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Most Accessed Documents -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Most Accessed Documents</div>
                                    <div class="text-sm text-slate-500">Top 10 by access count</div>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="space-y-3">
                                    <?php foreach ($mostAccessed as $index => $doc): ?>
                                        <div
                                            class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg">
                                            <div class="flex items-center space-x-3">
                                                <div
                                                    class="w-8 h-8 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-sm font-medium">
                                                    <?php echo $index + 1; ?>
                                                </div>
                                                <div>
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        <?php echo htmlspecialchars($doc['document_type']); ?></div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm font-medium"><?php echo $doc['access_count']; ?>
                                                    accesses</div>
                                                <div class="text-xs text-gray-500">Most popular</div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Document Library -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <input type="text" placeholder="Search documents..."
                                            class="px-3 py-1 text-sm border border-[hsl(var(--border))] rounded-md focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"
                                            id="searchInput">
                                        <select
                                            class="px-3 py-1 text-sm border border-[hsl(var(--border))] rounded-md focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"
                                            id="typeFilter">
                                            <option value="">All Types</option>
                                            <?php foreach ($documentTypes as $type): ?>
                                                <option value="<?php echo htmlspecialchars($type['document_type']); ?>">
                                                    <?php echo htmlspecialchars($type['document_type']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="text-sm text-slate-500">
                                        <?php echo count($documents); ?> documents
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Document</th>
                                            <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Department</th>
                                            <th class="text-left px-3 py-2 font-semibold">Type</th>
                                            <th class="text-left px-3 py-2 font-semibold">Upload Date</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($documents)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="6">
                                                    <div
                                                        class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
                                                        <div class="text-sm font-medium">No documents</div>
                                                        <div class="text-xs text-slate-500 mt-1">Upload documents to build
                                                            your library.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($documents as $doc): ?>
                                                <tr
                                                    class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                    <td class="px-3 py-3">
                                                        <div class="flex items-center space-x-2">
                                                            <div
                                                                class="w-8 h-8 bg-blue-100 rounded flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-blue-600" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                                    </path>
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <div class="font-medium">
                                                                    <?php echo htmlspecialchars(basename($doc['file_path'])); ?>
                                                                </div>
                                                                <div class="text-xs text-slate-500">
                                                                    <?php echo htmlspecialchars($doc['document_type']); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div>
                                                            <div class="font-medium">
                                                                <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?>
                                                            </div>
                                                            <div class="text-xs text-slate-500">
                                                                <?php echo htmlspecialchars($doc['employee_number']); ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php echo htmlspecialchars($doc['department_name'] ?? 'N/A'); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                            <?php echo htmlspecialchars($doc['document_type']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php echo date('M j, Y', strtotime($doc['upload_date'])); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="flex gap-1">
                                                            <button
                                                                onclick="downloadDocument('<?php echo htmlspecialchars($doc['file_path']); ?>')"
                                                                class="text-blue-600 hover:text-blue-800 text-xs">Download</button>
                                                            <button
                                                                onclick="viewDocument('<?php echo htmlspecialchars($doc['file_path']); ?>')"
                                                                class="text-green-600 hover:text-green-800 text-xs">View</button>
                                                            <button onclick="deleteDocument(<?php echo $doc['id']; ?>)"
                                                                class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Recent Uploads -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Recent Uploads</div>
                                    <div class="text-sm text-slate-500">Last 10 uploads</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Document</th>
                                            <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Type</th>
                                            <th class="text-left px-3 py-2 font-semibold">Upload Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentUploads as $upload): ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div class="flex items-center space-x-2">
                                                        <div
                                                            class="w-6 h-6 bg-green-100 rounded flex items-center justify-center">
                                                            <svg class="w-3 h-3 text-green-600" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                                </path>
                                                            </svg>
                                                        </div>
                                                        <div class="text-sm">
                                                            <?php echo htmlspecialchars(basename($upload['file_path'])); ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="text-sm">
                                                        <?php echo htmlspecialchars($upload['first_name'] . ' ' . $upload['last_name']); ?>
                                                    </div>
                                                    <div class="text-xs text-slate-500">
                                                        <?php echo htmlspecialchars($upload['employee_number']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                        <?php echo htmlspecialchars($upload['document_type']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="text-sm">
                                                        <?php echo date('M j, Y', strtotime($upload['upload_date'])); ?>
                                                    </div>
                                                    <div class="text-xs text-slate-500">
                                                        <?php echo date('H:i', strtotime($upload['created_at'])); ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>

    <!-- Upload Document Modal -->
    <div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Upload Document</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_document">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Employee</label>
                            <select name="employee_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Employee</option>
                                <?php
                                $stmt = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as full_name, employee_number FROM employees WHERE status = 'Active' ORDER BY first_name, last_name");
                                $employees = $stmt->fetchAll();
                                foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>">
                                        <?php echo htmlspecialchars($emp['full_name'] . ' (' . $emp['employee_number'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Document Type</label>
                            <select name="document_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Type</option>
                                <option value="Resume">Resume</option>
                                <option value="ID Picture">ID Picture</option>
                                <option value="Medical Certificate">Medical Certificate</option>
                                <option value="Contract">Contract</option>
                                <option value="Certificate">Certificate</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Document File</label>
                            <input type="file" name="document" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="text-xs text-slate-500 mt-1">Accepted formats: PDF, DOC, DOCX, JPG, PNG</div>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Upload
                            Document</button>
                        <button type="button" onclick="closeUploadModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Document Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Delete Document</h3>
                <p class="text-sm text-slate-600 mb-4">Are you sure you want to delete this document? This action cannot
                    be undone.</p>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="action" value="delete_document">
                    <input type="hidden" name="document_id" id="deleteDocumentId">
                    <div class="flex gap-2">
                        <button type="submit"
                            class="flex-1 bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700">Delete
                            Document</button>
                        <button type="button" onclick="closeDeleteModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        function openUploadModal() {
            document.getElementById('uploadModal').classList.remove('hidden');
        }

        function closeUploadModal() {
            document.getElementById('uploadModal').classList.add('hidden');
        }

        function deleteDocument(documentId) {
            document.getElementById('deleteDocumentId').value = documentId;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function downloadDocument(filePath) {
            // Create a temporary link to download the file
            const link = document.createElement('a');
            link.href = filePath;
            link.download = filePath.split('/').pop();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function viewDocument(filePath) {
            // Open document in new tab
            window.open(filePath, '_blank');
        }

        function bulkUpload() {
            alert('Bulk upload functionality coming soon');
        }

        function exportDocuments() {
            alert('Export documents functionality coming soon');
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Type filter functionality
        document.getElementById('typeFilter').addEventListener('change', function () {
            const filterValue = this.value;
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                if (filterValue === '') {
                    row.style.display = '';
                } else {
                    const typeCell = row.querySelector('td:nth-child(4)');
                    const type = typeCell.textContent.trim();
                    row.style.display = type === filterValue ? '' : 'none';
                }
            });
        });

        // Document Versioning Functions
        function viewVersions(employeeId, documentType) {
            alert(`View all versions for employee ${employeeId}, document type: ${documentType} - This would show a timeline of all document versions`);
        }

        function compareVersions(employeeId, documentType) {
            alert(`Compare versions for employee ${employeeId}, document type: ${documentType} - This would show a side-by-side comparison of different versions`);
        }

        function restoreVersion(versionId) {
            if (confirm('Restore this version as the current version?')) {
                alert(`Restoring version ${versionId} - This would make the selected version the current one`);
            }
        }

        // Document Access Management Functions
        function viewAccessLogs(documentId) {
            alert(`View access logs for document ${documentId} - This would show detailed access history`);
        }

        function setDocumentPermissions(documentId) {
            alert(`Set permissions for document ${documentId} - This would open a modal to configure who can access the document`);
        }

        function generateAccessReport() {
            alert('Generate access report - Create detailed reports on document access patterns and user activity');
        }

        function openDocumentAnalytics() {
            alert('Document analytics - Analyze document usage, access patterns, and storage statistics');
        }

        function openVersionControl() {
            alert('Version control - Manage document versions, track changes, and handle conflicts');
        }

        // Enhanced Document Management
        function bulkDownload() {
            alert('Bulk download - Select multiple documents to download as a zip file');
        }

        function archiveDocuments() {
            alert('Archive documents - Move old documents to archive storage');
        }
    </script>
</body>

</html>