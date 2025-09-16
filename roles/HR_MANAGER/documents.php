<?php
// HR Manager Document Center Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../shared/database_helper.php';
include_once __DIR__ . '/../../routing/rbac.php';

$activeId = 'documents';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Initialize database helper
$dbHelper = new DatabaseHelper();

// Handle CRUD operations
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_document') {
        try {
            $employeeId = intval($_POST['employee_id'] ?? 0);
            $documentType = $_POST['document_type'] ?? '';
            $documentName = $_POST['document_name'] ?? '';
            $description = $_POST['description'] ?? '';
            $expiryDate = $_POST['expiry_date'] ?? null;

            // Handle file upload
            $filePath = null;
            if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/documents/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $fileName = uniqid() . '_' . $_FILES['document_file']['name'];
                $filePath = $uploadDir . $fileName;
                move_uploaded_file($_FILES['document_file']['tmp_name'], $filePath);
            }

            $dbHelper->query("
                INSERT INTO employee_documents (employee_id, document_type, document_name, description, file_path, expiry_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'Active')
            ", [$employeeId, $documentType, $documentName, $description, $filePath, $expiryDate]);

            $message = 'Document uploaded successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error uploading document: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'update_document') {
        try {
            $documentId = intval($_POST['document_id'] ?? 0);
            $documentType = $_POST['document_type'] ?? '';
            $documentName = $_POST['document_name'] ?? '';
            $description = $_POST['description'] ?? '';
            $expiryDate = $_POST['expiry_date'] ?? null;
            $status = $_POST['status'] ?? 'Active';

            $dbHelper->query("
                UPDATE employee_documents 
                SET document_type = ?, document_name = ?, description = ?, expiry_date = ?, status = ?
                WHERE id = ?
            ", [$documentType, $documentName, $description, $expiryDate, $status, $documentId]);

            $message = 'Document updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error updating document: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete_document') {
        try {
            $documentId = intval($_POST['document_id'] ?? 0);
            $dbHelper->query("DELETE FROM employee_documents WHERE id = ?", [$documentId]);
            $message = 'Document deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error deleting document: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $docId = (int) $_GET['id'];
    if ($docId > 0) {
        $dbHelper->deleteDocumentById($docId);
        $message = 'Document deleted successfully!';
        $messageType = 'success';
    }
}

// Get document data
$documents = $dbHelper->getEmployeeDocuments();

// Get all employees for document management
$allEmployees = $dbHelper->getEmployees(1000);

// Group documents by type
$documentsByType = [];
foreach ($documents as $doc) {
    $type = $doc['document_type'];
    if (!isset($documentsByType[$type])) {
        $documentsByType[$type] = [];
    }
    $documentsByType[$type][] = $doc;
}

// Calculate stats
$totalDocuments = count($documents);
$documentTypes = array_keys($documentsByType);
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

                        <!-- Message Display -->
                        <?php if ($message): ?>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] p-4 <?php echo $messageType === 'success' ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'; ?>">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 <?php echo $messageType === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'; ?>"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?php if ($messageType === 'success'): ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        <?php else: ?>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        <?php endif; ?>
                                    </svg>
                                    <span
                                        class="text-sm font-medium <?php echo $messageType === 'success' ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'; ?>">
                                        <?php echo htmlspecialchars($message); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Document Stats -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Total Documents</div>
                                        <div class="text-2xl font-semibold"><?php echo $totalDocuments; ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Document Types</div>
                                        <div class="text-2xl font-semibold"><?php echo count($documentTypes); ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Recent Uploads</div>
                                        <div class="text-2xl font-semibold">
                                            <?php echo count(array_filter($documents, function ($doc) {
                                                return strtotime($doc['upload_date']) > strtotime('-7 days');
                                            })); ?>
                                        </div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Storage Used</div>
                                        <div class="text-2xl font-semibold">
                                            <?php echo number_format($totalDocuments * 0.1, 1); ?> GB
                                        </div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Document Library -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <h3 class="font-semibold">Document Library</h3>
                                        <span
                                            class="px-2 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400 text-xs rounded-full">
                                            <?php echo $totalDocuments; ?> files
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        <form method="get" class="flex gap-2">
                                            <input type="hidden" name="page" value="documents">
                                            <?php $selectedType = $_GET['type'] ?? ''; ?>
                                            <select name="type"
                                                class="px-3 py-1 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] text-sm"
                                                onchange="this.form.submit()">
                                                <option value="" <?php echo $selectedType === '' ? 'selected' : ''; ?>>All
                                                    Types</option>
                                                <?php foreach ($documentTypes as $type): ?>
                                                    <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $selectedType === $type ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($type); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </div>
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
                                            <th class="text-left px-3 py-2 font-semibold">Size</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($documents)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="6">
                                                    <div class="text-center py-10">
                                                        <div class="text-sm font-medium">No documents</div>
                                                        <div class="text-xs text-slate-500 mt-1">Upload documents to get
                                                            started.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php
                                            $selectedType = $_GET['type'] ?? '';
                                            $docsToRender = $selectedType ? array_filter($documents, function ($d) use ($selectedType) {
                                                return $d['document_type'] === $selectedType;
                                            }) : $documents;
                                            foreach ($docsToRender as $doc): ?>
                                                <tr
                                                    class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                    <td class="px-3 py-3">
                                                        <div class="flex items-center gap-3">
                                                            <div
                                                                class="w-8 h-8 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400"
                                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                                    </path>
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <div class="font-medium text-slate-900 dark:text-slate-100">
                                                                    <?php echo htmlspecialchars(basename($doc['file_path'])); ?>
                                                                </div>
                                                                <div class="text-xs text-slate-500">
                                                                    <?php echo htmlspecialchars($doc['file_path']); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <div>
                                                            <div class="font-medium">
                                                                <?php echo htmlspecialchars($doc['employee_name']); ?>
                                                            </div>
                                                            <div class="text-xs text-slate-500">
                                                                <?php echo htmlspecialchars($doc['employee_number']); ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                                            <?php echo htmlspecialchars($doc['document_type']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <?php echo date('M j, Y', strtotime($doc['upload_date'])); ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        —
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="flex items-center gap-1">
                                                            <a href="<?php echo htmlspecialchars($doc['file_path']); ?>"
                                                                download
                                                                class="p-1 text-slate-400 hover:text-blue-600 transition-colors"
                                                                title="Download">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                                    </path>
                                                                </svg>
                                                            </a>
                                                            <a href="<?php echo htmlspecialchars($doc['file_path']); ?>"
                                                                target="_blank"
                                                                class="p-1 text-slate-400 hover:text-green-600 transition-colors"
                                                                title="View">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                                                    </path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                                    </path>
                                                                </svg>
                                                            </a>
                                                            <a href="?page=documents&action=delete&id=<?php echo (int) $doc['id']; ?>"
                                                                class="p-1 text-slate-400 hover:text-red-600 transition-colors"
                                                                title="Delete"
                                                                onclick="return confirm('Delete this document?');">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                    </path>
                                                                </svg>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Document Types Overview -->
                        <div class="grid lg:grid-cols-2 gap-4">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Document Types</div>
                                <div class="p-4">
                                    <?php if (empty($documentTypes)): ?>
                                        <div class="text-sm text-slate-500">No document types found</div>
                                    <?php else: ?>
                                        <div class="space-y-3">
                                            <?php foreach ($documentTypes as $type): ?>
                                                <div
                                                    class="flex items-center justify-between p-3 rounded-lg border border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                    <div class="font-medium text-slate-900 dark:text-slate-100">
                                                        <?php echo htmlspecialchars($type); ?>
                                                    </div>
                                                    <div class="text-sm text-slate-600 dark:text-slate-300">
                                                        <?php echo count($documentsByType[$type]); ?> files
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Recent Activity
                                </div>
                                <div class="p-4">
                                    <?php if (empty($documents)): ?>
                                        <div class="text-center py-8 text-slate-500">
                                            <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                            <p>No documents found</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="space-y-3">
                                            <?php foreach (array_slice($documents, 0, 5) as $document): ?>
                                                <div class="flex items-start gap-3">
                                                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="text-sm text-slate-900 dark:text-slate-100">
                                                            Document uploaded:
                                                            <?php echo htmlspecialchars(basename($document['file_path'])); ?>
                                                        </div>
                                                        <div class="text-xs text-slate-500">
                                                            by <?php echo htmlspecialchars($document['employee_name']); ?> •
                                                            <?php echo date('M j, Y', strtotime($document['upload_date'])); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Document Management -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <h3 class="font-semibold">Document Management</h3>
                                        <span
                                            class="px-2 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400 text-xs rounded-full">
                                            <?php echo count($documents); ?> documents
                                        </span>
                                    </div>
                                    <button onclick="openCreateDocumentModal()"
                                        class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-3 py-1 rounded-md text-sm hover:opacity-95 transition-opacity">
                                        Upload Document
                                    </button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Document Name</th>
                                            <th class="text-left px-3 py-2 font-semibold">Type</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                            <th class="text-left px-3 py-2 font-semibold">Expiry Date</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($documents as $document): ?>
                                            <tr
                                                class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                <td class="px-3 py-3 font-medium text-slate-900 dark:text-slate-100">
                                                    <?php echo htmlspecialchars($document['employee_name']); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    <?php echo htmlspecialchars($document['document_name'] ?? basename($document['file_path'] ?? '')); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    <?php echo htmlspecialchars($document['document_type']); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php
                                                    $docStatus = $document['status'] ?? 'Active';
                                                    echo $docStatus === 'Active' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' :
                                                        ($docStatus === 'Expired' ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' :
                                                            'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400');
                                                    ?>">
                                                        <?php echo htmlspecialchars($docStatus); ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    <?php echo (!empty($document['expiry_date'])) ? date('M j, Y', strtotime($document['expiry_date'])) : 'N/A'; ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex items-center gap-1">
                                                        <?php if ($document['file_path']): ?>
                                                            <a href="<?php echo htmlspecialchars($document['file_path']); ?>"
                                                                target="_blank"
                                                                class="p-1 text-slate-400 hover:text-blue-600 transition-colors"
                                                                title="View">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                                                    </path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                                    </path>
                                                                </svg>
                                                            </a>
                                                        <?php endif; ?>
                                                        <button
                                                            onclick="openEditDocumentModal(<?php echo $document['id']; ?>, '<?php echo htmlspecialchars($document['document_name'] ?? basename($document['file_path'] ?? '')); ?>', '<?php echo htmlspecialchars($document['document_type'] ?? ''); ?>', '<?php echo htmlspecialchars($document['description'] ?? ''); ?>', '<?php echo $document['expiry_date'] ?? ''; ?>', '<?php echo $document['status'] ?? 'Active'; ?>')"
                                                            class="p-1 text-slate-400 hover:text-green-600 transition-colors"
                                                            title="Edit">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                        <button
                                                            onclick="openDeleteDocumentModal(<?php echo $document['id']; ?>, '<?php echo htmlspecialchars($document['document_name']); ?>')"
                                                            class="p-1 text-slate-400 hover:text-red-600 transition-colors"
                                                            title="Delete">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                </path>
                                                            </svg>
                                                        </button>
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

    <!-- Create Document Modal -->
    <div id="createDocumentModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Upload Document</h3>
                    <button onclick="closeCreateDocumentModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create_document">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Employee *</label>
                    <select name="employee_id" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Employee</option>
                        <?php foreach ($allEmployees as $employee): ?>
                            <option value="<?php echo $employee['id']; ?>">
                                <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Document Type
                        *</label>
                    <select name="document_type" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Type</option>
                        <option value="Contract">Contract</option>
                        <option value="ID">ID</option>
                        <option value="Certificate">Certificate</option>
                        <option value="License">License</option>
                        <option value="Medical">Medical</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Document Name
                        *</label>
                    <input type="text" name="document_name" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Expiry Date</label>
                    <input type="date" name="expiry_date"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Document File
                        *</label>
                    <input type="file" name="document_file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCreateDocumentModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Upload Document
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Document Modal -->
    <div id="editDocumentModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Edit Document</h3>
                    <button onclick="closeEditDocumentModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="update_document">
                <input type="hidden" name="document_id" id="edit_document_id">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Document Type
                        *</label>
                    <select name="document_type" id="edit_document_type" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Type</option>
                        <option value="Contract">Contract</option>
                        <option value="ID">ID</option>
                        <option value="Certificate">Certificate</option>
                        <option value="License">License</option>
                        <option value="Medical">Medical</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Document Name
                        *</label>
                    <input type="text" name="document_name" id="edit_document_name" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description</label>
                    <textarea name="description" id="edit_description" rows="3"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Expiry Date</label>
                    <input type="date" name="expiry_date" id="edit_expiry_date"
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status *</label>
                    <select name="status" id="edit_status" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="Active">Active</option>
                        <option value="Expired">Expired</option>
                        <option value="Archived">Archived</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditDocumentModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Update Document
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Document Confirmation Modal -->
    <div id="deleteDocumentModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Confirm Deletion</h3>
                        <p class="text-sm text-slate-500">This action cannot be undone.</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300 mb-6">
                    Are you sure you want to delete this document? This will permanently remove the document and its
                    file.
                </p>
                <form method="POST" id="deleteDocumentForm">
                    <input type="hidden" name="action" value="delete_document">
                    <input type="hidden" name="document_id" id="delete_document_id">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeDeleteDocumentModal()"
                            class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            Delete Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        // Document Modal functions
        function openCreateDocumentModal() {
            document.getElementById('createDocumentModal').classList.remove('hidden');
        }

        function closeCreateDocumentModal() {
            document.getElementById('createDocumentModal').classList.add('hidden');
        }

        function openEditDocumentModal(documentId, documentName, documentType, description, expiryDate, status) {
            document.getElementById('edit_document_id').value = documentId;
            document.getElementById('edit_document_name').value = documentName;
            document.getElementById('edit_document_type').value = documentType;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_expiry_date').value = expiryDate;
            document.getElementById('edit_status').value = status;
            document.getElementById('editDocumentModal').classList.remove('hidden');
        }

        function closeEditDocumentModal() {
            document.getElementById('editDocumentModal').classList.add('hidden');
        }

        function openDeleteDocumentModal(documentId, documentName) {
            document.getElementById('delete_document_id').value = documentId;
            document.getElementById('deleteDocumentModal').classList.remove('hidden');
        }

        function closeDeleteDocumentModal() {
            document.getElementById('deleteDocumentModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('fixed')) {
                closeCreateDocumentModal();
                closeEditDocumentModal();
                closeDeleteDocumentModal();
            }
        });
    </script>
</body>

</html>