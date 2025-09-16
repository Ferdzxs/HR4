<?php
// HR Manager Bulk Operations Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../routing/rbac.php';
include_once __DIR__ . '/../../config/database.php';

$activeId = 'bulk';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'bulk_update_employees':
                try {
                    $employeeIds = $_POST['employee_ids'];
                    $updateField = $_POST['update_field'];
                    $updateValue = $_POST['update_value'];

                    if ($updateField === 'status') {
                        $stmt = $pdo->prepare("UPDATE employees SET status = ? WHERE id IN (" . implode(',', array_fill(0, count($employeeIds), '?')) . ")");
                        $params = array_merge([$updateValue], $employeeIds);
                        $stmt->execute($params);
                    } elseif ($updateField === 'department_id') {
                        $stmt = $pdo->prepare("UPDATE employees SET department_id = ? WHERE id IN (" . implode(',', array_fill(0, count($employeeIds), '?')) . ")");
                        $params = array_merge([$updateValue], $employeeIds);
                        $stmt->execute($params);
                    }

                    $success = "Bulk update completed successfully! " . count($employeeIds) . " employees updated.";
                } catch (PDOException $e) {
                    $error = "Error performing bulk update: " . $e->getMessage();
                }
                break;

            case 'bulk_upload_documents':
                try {
                    $targetDir = "uploads/bulk/";
                    if (!file_exists($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }

                    $uploadedCount = 0;
                    foreach ($_FILES['documents']['tmp_name'] as $key => $tmpName) {
                        if (!empty($tmpName)) {
                            $fileName = basename($_FILES['documents']['name'][$key]);
                            $targetFile = $targetDir . time() . "_" . $fileName;

                            if (move_uploaded_file($tmpName, $targetFile)) {
                                $stmt = $pdo->prepare("INSERT INTO employee_documents (employee_id, document_type, file_path, upload_date) VALUES (?, ?, ?, ?)");
                                $stmt->execute([
                                    $_POST['employee_id'],
                                    $_POST['document_type'],
                                    $targetFile,
                                    date('Y-m-d')
                                ]);
                                $uploadedCount++;
                            }
                        }
                    }

                    $success = "Bulk document upload completed! " . $uploadedCount . " documents uploaded.";
                } catch (PDOException $e) {
                    $error = "Error uploading documents: " . $e->getMessage();
                }
                break;
        }
    }
}

// Fetch data for bulk operations
try {
    // Get employees for bulk operations
    $stmt = $pdo->query("SELECT e.*, d.department_name FROM employees e 
                        LEFT JOIN departments d ON e.department_id = d.id 
                        WHERE e.status = 'Active' ORDER BY e.last_name, e.first_name");
    $employees = $stmt->fetchAll();

    // Get departments for bulk assignment
    $stmt = $pdo->query("SELECT * FROM departments ORDER BY department_name");
    $departments = $stmt->fetchAll();

    // Simulate bulk job history (in real implementation, this would be from a jobs table)
    $bulkJobs = [
        [
            'id' => 1,
            'job_name' => 'Employee Status Update',
            'job_type' => 'Employee Update',
            'submitted_date' => '2024-12-15',
            'status' => 'Completed',
            'records_processed' => 25
        ],
        [
            'id' => 2,
            'job_name' => 'Department Transfer',
            'job_type' => 'Employee Update',
            'submitted_date' => '2024-12-14',
            'status' => 'Processing',
            'records_processed' => 15
        ],
        [
            'id' => 3,
            'job_name' => 'Document Upload',
            'job_type' => 'Document Processing',
            'submitted_date' => '2024-12-13',
            'status' => 'Completed',
            'records_processed' => 50
        ]
    ];

} catch (PDOException $e) {
    $employees = [];
    $departments = [];
    $bulkJobs = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Bulk Operations</title>
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
                            <h1 class="text-lg font-semibold">Bulk Operations</h1>
                            <p class="text-xs text-slate-500 mt-1">Mass updates, document processing, validation</p>
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
                            <button onclick="openBulkUpdateModal()"
                                class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Bulk Employee Update
                            </button>
                            <button onclick="openBulkUploadModal()"
                                class="bg-orange-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Bulk Document Upload
                            </button>
                            <button onclick="exportBulkData()"
                                class="bg-slate-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Export Data
                            </button>
                        </div>

                        <!-- Bulk Operations Statistics -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Total Jobs</div>
                                <div class="text-2xl font-semibold"><?php echo count($bulkJobs); ?></div>
                                <div class="text-xs text-blue-600 mt-1">All time</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Completed</div>
                                <div class="text-2xl font-semibold">
                                    <?php echo count(array_filter($bulkJobs, function ($job) {
                                        return $job['status'] === 'Completed';
                                    })); ?>
                                </div>
                                <div class="text-xs text-green-600 mt-1">Successful</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Processing</div>
                                <div class="text-2xl font-semibold">
                                    <?php echo count(array_filter($bulkJobs, function ($job) {
                                        return $job['status'] === 'Processing';
                                    })); ?>
                                </div>
                                <div class="text-xs text-orange-600 mt-1">In progress</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Records Processed</div>
                                <div class="text-2xl font-semibold">
                                    <?php echo array_sum(array_column($bulkJobs, 'records_processed')); ?>
                                </div>
                                <div class="text-xs text-purple-600 mt-1">Total records</div>
                            </div>
                        </div>

                        <!-- Validation Dashboard -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Data Validation Dashboard</div>
                                    <div class="text-sm text-slate-500">Validate data before bulk operations</div>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                        <div class="text-xs text-green-600 mb-1">Valid Records</div>
                                        <div class="text-lg font-semibold text-green-800">
                                            <?php echo count($employees); ?></div>
                                        <div class="text-xs text-green-600">Ready for processing</div>
                                    </div>
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                        <div class="text-xs text-yellow-600 mb-1">Needs Review</div>
                                        <div class="text-lg font-semibold text-yellow-800">0</div>
                                        <div class="text-xs text-yellow-600">Requires attention</div>
                                    </div>
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                        <div class="text-xs text-red-600 mb-1">Invalid Records</div>
                                        <div class="text-lg font-semibold text-red-800">0</div>
                                        <div class="text-xs text-red-600">Cannot process</div>
                                    </div>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                        <div class="text-xs text-blue-600 mb-1">Validation Score</div>
                                        <div class="text-lg font-semibold text-blue-800">100%</div>
                                        <div class="text-xs text-blue-600">Data quality</div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick="runValidation()"
                                        class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                                        Run Validation
                                    </button>
                                    <button onclick="viewValidationReport()"
                                        class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm hover:bg-gray-700">
                                        View Report
                                    </button>
                                    <button onclick="exportValidationResults()"
                                        class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700">
                                        Export Results
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Tracking -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Active Job Progress</div>
                                    <div class="text-sm text-slate-500">Real-time progress tracking</div>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="space-y-4">
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="font-medium">Employee Status Update</div>
                                            <div class="text-sm text-blue-600">Processing</div>
                                        </div>
                                        <div class="w-full bg-blue-200 rounded-full h-2 mb-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: 75%"></div>
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-600">
                                            <span>15 of 20 records processed</span>
                                            <span>75% complete</span>
                                        </div>
                                    </div>
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="font-medium">Document Upload</div>
                                            <div class="text-sm text-green-600">Completed</div>
                                        </div>
                                        <div class="w-full bg-green-200 rounded-full h-2 mb-2">
                                            <div class="bg-green-600 h-2 rounded-full" style="width: 100%"></div>
                                        </div>
                                        <div class="flex justify-between text-xs text-gray-600">
                                            <span>50 of 50 records processed</span>
                                            <span>100% complete</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bulk Jobs History -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Bulk Jobs History</div>
                                    <div class="text-sm text-slate-500"><?php echo count($bulkJobs); ?> jobs</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Job</th>
                                            <th class="text-left px-3 py-2 font-semibold">Type</th>
                                            <th class="text-left px-3 py-2 font-semibold">Submitted</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                            <th class="text-left px-3 py-2 font-semibold">Records</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($bulkJobs)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="6">
                                                    <div
                                                        class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
                                                        <div class="text-sm font-medium">No bulk jobs</div>
                                                        <div class="text-xs text-slate-500 mt-1">Start a bulk operation to
                                                            see job history.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($bulkJobs as $job): ?>
                                                <tr
                                                    class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                    <td class="px-3 py-3">
                                                        <div class="font-medium">
                                                            <?php echo htmlspecialchars($job['job_name']); ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                            <?php echo htmlspecialchars($job['job_type']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php echo date('M j, Y', strtotime($job['submitted_date'])); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span class="px-2 py-1 text-xs rounded-full <?php
                                                        echo $job['status'] === 'Completed' ? 'bg-green-100 text-green-800' :
                                                            ($job['status'] === 'Processing' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                                        ?>">
                                                            <?php echo htmlspecialchars($job['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php echo number_format($job['records_processed']); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="flex gap-1">
                                                            <button onclick="viewJobDetails(<?php echo $job['id']; ?>)"
                                                                class="text-blue-600 hover:text-blue-800 text-xs">View</button>
                                                            <?php if ($job['status'] === 'Completed'): ?>
                                                                <button onclick="downloadJobReport(<?php echo $job['id']; ?>)"
                                                                    class="text-green-600 hover:text-green-800 text-xs">Report</button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                </main>
            </div>
        </div>
    </div>

    <!-- Bulk Update Modal -->
    <div id="bulkUpdateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
                <h3 class="text-lg font-semibold mb-4">Bulk Employee Update</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="bulk_update_employees">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Select Employees</label>
                            <div class="max-h-60 overflow-y-auto border border-gray-300 rounded-md p-2">
                                <?php foreach ($employees as $emp): ?>
                                    <label class="flex items-center space-x-2 py-1">
                                        <input type="checkbox" name="employee_ids[]" value="<?php echo $emp['id']; ?>"
                                            class="employee-checkbox">
                                        <span
                                            class="text-sm"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_number'] . ')'); ?></span>
                                        <span class="text-xs text-slate-500">-
                                            <?php echo htmlspecialchars($emp['department_name'] ?? 'No Department'); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-2">
                                <button type="button" onclick="selectAllEmployees()"
                                    class="text-xs text-blue-600 hover:text-blue-800">Select All</button>
                                <button type="button" onclick="deselectAllEmployees()"
                                    class="text-xs text-blue-600 hover:text-blue-800 ml-4">Deselect All</button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Update Field</label>
                            <select name="update_field" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Field</option>
                                <option value="status">Status</option>
                                <option value="department_id">Department</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">New Value</label>
                            <select name="update_value" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Value</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Resigned">Resigned</option>
                                <option value="Terminated">Terminated</option>
                                <option value="Retired">Retired</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Update Selected
                            Employees</button>
                        <button type="button" onclick="closeBulkUpdateModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Upload Modal -->
    <div id="bulkUploadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Bulk Document Upload</h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="bulk_upload_documents">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Employee</label>
                            <select name="employee_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>">
                                        <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_number'] . ')'); ?>
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
                            <label class="block text-sm font-medium mb-1">Documents</label>
                            <input type="file" name="documents[]" multiple required
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="text-xs text-slate-500 mt-1">Select multiple files. Accepted formats: PDF, DOC,
                                DOCX, JPG, PNG</div>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Upload
                            Documents</button>
                        <button type="button" onclick="closeBulkUploadModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        function openBulkUpdateModal() {
            document.getElementById('bulkUpdateModal').classList.remove('hidden');
        }

        function closeBulkUpdateModal() {
            document.getElementById('bulkUpdateModal').classList.add('hidden');
        }

        function openBulkUploadModal() {
            document.getElementById('bulkUploadModal').classList.remove('hidden');
        }

        function closeBulkUploadModal() {
            document.getElementById('bulkUploadModal').classList.add('hidden');
        }

        function selectAllEmployees() {
            const checkboxes = document.querySelectorAll('.employee-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = true);
        }

        function deselectAllEmployees() {
            const checkboxes = document.querySelectorAll('.employee-checkbox');
            checkboxes.forEach(checkbox => checkbox.checked = false);
        }

        function viewJobDetails(id) {
            alert('View job details ' + id);
        }

        function downloadJobReport(id) {
            alert('Download job report ' + id);
        }

        function exportBulkData() {
            // Export bulk data to CSV
            const table = document.getElementById('bulkJobsTable');
            const rows = Array.from(table.querySelectorAll('tr'));
            const csvContent = rows.map(row => {
                const cells = Array.from(row.querySelectorAll('td, th'));
                return cells.map(cell => cell.textContent.trim()).join(',');
            }).join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'bulk_jobs.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Validation Functions
        function runValidation() {
            // Simulate validation process
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = 'Validating...';
            button.disabled = true;

            // Simulate validation delay
            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
                alert('Validation completed! All records are valid.');
            }, 2000);
        }

        function viewValidationReport() {
            // Open modal to view validation report
            alert('Opening validation report...');
        }

        function exportValidationResults() {
            // Export validation results
            alert('Exporting validation results...');
        }

        // Progress Tracking Functions
        function updateProgress(jobId, progress) {
            // Update progress bar for specific job
            const progressBar = document.querySelector(`[data-job-id="${jobId}"] .progress-bar`);
            if (progressBar) {
                progressBar.style.width = progress + '%';
            }
        }

        function refreshProgress() {
            // Refresh all progress bars
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach(bar => {
                const currentWidth = parseInt(bar.style.width) || 0;
                if (currentWidth < 100) {
                    const newWidth = Math.min(currentWidth + Math.random() * 10, 100);
                    bar.style.width = newWidth + '%';
                }
            });
        }

        function startProgressTracking() {
            // Start auto-refresh of progress bars
            window.progressInterval = setInterval(refreshProgress, 1000);
        }

        function stopProgressTracking() {
            // Stop auto-refresh of progress bars
            if (window.progressInterval) {
                clearInterval(window.progressInterval);
            }
        }

        // Job Management Functions
        function pauseJob(jobId) {
            if (confirm('Are you sure you want to pause this job?')) {
                alert('Job ' + jobId + ' paused');
            }
        }

        function resumeJob(jobId) {
            alert('Job ' + jobId + ' resumed');
        }

        function cancelJob(jobId) {
            if (confirm('Are you sure you want to cancel this job? This action cannot be undone.')) {
                alert('Job ' + jobId + ' cancelled');
            }
        }

        function retryJob(jobId) {
            if (confirm('Are you sure you want to retry this job?')) {
                alert('Job ' + jobId + ' retried');
            }
        }

        // Data Quality Functions
        function checkDataQuality() {
            // Simulate data quality check
            alert('Running data quality check...');
        }

        function fixDataIssues() {
            // Simulate fixing data issues
            alert('Fixing data issues...');
        }

        function generateDataReport() {
            // Generate comprehensive data report
            alert('Generating data report...');
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function () {
            // Start progress tracking
            startProgressTracking();

            // Add hover effects to metric cards
            const metricCards = document.querySelectorAll('.metric-card');
            metricCards.forEach(card => {
                card.addEventListener('mouseenter', function () {
                    this.classList.add('shadow-lg', 'scale-105');
                });
                card.addEventListener('mouseleave', function () {
                    this.classList.remove('shadow-lg', 'scale-105');
                });
            });
        });

        // Update field change handler
        document.querySelector('select[name="update_field"]').addEventListener('change', function () {
            const updateValueSelect = document.querySelector('select[name="update_value"]');
            const field = this.value;

            // Clear existing options
            updateValueSelect.innerHTML = '<option value="">Select Value</option>';

            if (field === 'status') {
                updateValueSelect.innerHTML += `
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Resigned">Resigned</option>
                    <option value="Terminated">Terminated</option>
                    <option value="Retired">Retired</option>
                `;
            } else if (field === 'department_id') {
                <?php foreach ($departments as $dept): ?>
                    updateValueSelect.innerHTML += '<option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>';
                <?php endforeach; ?>
            }
        });
    </script>
</body>

</html>