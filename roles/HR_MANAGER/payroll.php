<?php
// HR Manager Payroll Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../routing/rbac.php';
include_once __DIR__ . '/../../config/database.php';

$activeId = 'payroll';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'process_payroll':
                try {
                    $periodId = $_POST['period_id'];
                    $stmt = $pdo->prepare("UPDATE payroll_periods SET status = 'Processed', processed_date = CURDATE() WHERE id = ?");
                    $stmt->execute([$periodId]);
                    $success = "Payroll processed successfully!";
                } catch (PDOException $e) {
                    $error = "Error processing payroll: " . $e->getMessage();
                }
                break;

            case 'close_payroll':
                try {
                    $periodId = $_POST['period_id'];
                    $stmt = $pdo->prepare("UPDATE payroll_periods SET status = 'Closed' WHERE id = ?");
                    $stmt->execute([$periodId]);
                    $success = "Payroll period closed successfully!";
                } catch (PDOException $e) {
                    $error = "Error closing payroll: " . $e->getMessage();
                }
                break;
        }
    }
}

// Fetch payroll data
try {
    // Payroll periods with more details
    $stmt = $pdo->query("SELECT pp.*, 
                        COUNT(pe.id) as entry_count,
                        SUM(pe.net_pay) as total_amount,
                        AVG(pe.net_pay) as avg_pay
                        FROM payroll_periods pp
                        LEFT JOIN payroll_entries pe ON pp.id = pe.period_id
                        GROUP BY pp.id
                        ORDER BY pp.period_start DESC LIMIT 12");
    $payrollPeriods = $stmt->fetchAll();

    // Current period payroll entries
    $stmt = $pdo->query("SELECT pe.*, e.first_name, e.last_name, e.employee_number, d.department_name,
                        p.position_title, sg.grade_level
                        FROM payroll_entries pe
                        JOIN employees e ON pe.employee_id = e.id
                        LEFT JOIN departments d ON e.department_id = d.id
                        LEFT JOIN positions p ON e.position_id = p.id
                        LEFT JOIN salary_grades sg ON p.salary_grade_id = sg.id
                        JOIN payroll_periods pp ON pe.period_id = pp.id
                        WHERE pp.status = 'Open' OR pp.status = 'Processed'
                        ORDER BY e.last_name, e.first_name");
    $currentPayrollEntries = $stmt->fetchAll();

    // Payroll statistics
    $totalPayrollAmount = $pdo->query("SELECT SUM(net_pay) FROM payroll_entries pe 
                                      JOIN payroll_periods pp ON pe.period_id = pp.id 
                                      WHERE pp.status = 'Processed'")->fetchColumn();

    $pendingApprovals = $pdo->query("SELECT COUNT(*) FROM payroll_entries pe 
                                    JOIN payroll_periods pp ON pe.period_id = pp.id 
                                    WHERE pp.status = 'Open'")->fetchColumn();

    $processedPeriods = $pdo->query("SELECT COUNT(*) FROM payroll_periods WHERE status = 'Processed'")->fetchColumn();

    $totalEmployees = $pdo->query("SELECT COUNT(*) FROM employees WHERE status = 'Active'")->fetchColumn();

    // Monthly payroll trends
    $monthlyTrends = $pdo->query("SELECT 
                                 DATE_FORMAT(pp.period_start, '%Y-%m') as month,
                                 COUNT(DISTINCT pe.employee_id) as employee_count,
                                 SUM(pe.net_pay) as total_payroll,
                                 AVG(pe.net_pay) as avg_payroll
                                 FROM payroll_periods pp
                                 LEFT JOIN payroll_entries pe ON pp.id = pe.period_id
                                 WHERE pp.period_start >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                                 GROUP BY DATE_FORMAT(pp.period_start, '%Y-%m')
                                 ORDER BY month DESC")->fetchAll();

    // Exception handling - employees with missing payroll entries
    $exceptions = $pdo->query("SELECT e.id, e.first_name, e.last_name, e.employee_number, d.department_name
                              FROM employees e
                              LEFT JOIN departments d ON e.department_id = d.id
                              LEFT JOIN payroll_entries pe ON e.id = pe.employee_id
                              LEFT JOIN payroll_periods pp ON pe.period_id = pp.id AND pp.status = 'Open'
                              WHERE e.status = 'Active' AND pe.id IS NULL
                              ORDER BY e.last_name, e.first_name")->fetchAll();

} catch (PDOException $e) {
    $payrollPeriods = [];
    $currentPayrollEntries = [];
    $totalPayrollAmount = $pendingApprovals = $processedPeriods = $totalEmployees = 0;
    $monthlyTrends = [];
    $exceptions = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Payroll Management</title>
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
                            <h1 class="text-lg font-semibold">Payroll Management</h1>
                            <p class="text-xs text-slate-500 mt-1">Processing calendar, approvals and exception handling
                            </p>
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
                            <button onclick="openNewPeriodModal()"
                                class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                New Payroll Period
                            </button>
                            <button onclick="exportPayroll()"
                                class="bg-slate-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Export Payroll
                            </button>
                            <button onclick="generateReports()"
                                class="bg-orange-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Generate Reports
                            </button>
                        </div>

                        <!-- Payroll Statistics -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Total Payroll</div>
                                <div class="text-2xl font-semibold">
                                    ₱<?php echo number_format($totalPayrollAmount, 0); ?></div>
                                <div class="text-xs text-green-600 mt-1">Processed</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Pending Approvals</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($pendingApprovals); ?>
                                </div>
                                <div class="text-xs text-orange-600 mt-1">Awaiting review</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Processed Periods</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($processedPeriods); ?>
                                </div>
                                <div class="text-xs text-blue-600 mt-1">This year</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Active Employees</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($totalEmployees); ?></div>
                                <div class="text-xs text-purple-600 mt-1">In payroll</div>
                            </div>
                        </div>

                        <!-- Monthly Payroll Trends -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Monthly Payroll Trends</div>
                                    <div class="text-sm text-slate-500">Last 6 months</div>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <?php foreach ($monthlyTrends as $trend): ?>
                                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo date('M Y', strtotime($trend['month'] . '-01')); ?>
                                            </div>
                                            <div class="mt-2 space-y-1">
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-gray-500">Employees:</span>
                                                    <span
                                                        class="font-medium"><?php echo number_format($trend['employee_count']); ?></span>
                                                </div>
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-gray-500">Total Payroll:</span>
                                                    <span
                                                        class="font-medium">₱<?php echo number_format($trend['total_payroll'], 0); ?></span>
                                                </div>
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-gray-500">Avg Pay:</span>
                                                    <span
                                                        class="font-medium">₱<?php echo number_format($trend['avg_payroll'], 0); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Payroll Exceptions -->
                        <?php if (!empty($exceptions)): ?>
                            <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                                <div class="p-3 border-b border-[hsl(var(--border))] bg-red-50">
                                    <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                        <div class="font-semibold text-red-800">Payroll Exceptions</div>
                                        <div class="text-sm text-red-600"><?php echo count($exceptions); ?> employees
                                            missing payroll entries</div>
                                    </div>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left font-medium text-gray-900">Employee</th>
                                                <th class="px-4 py-2 text-left font-medium text-gray-900">Department</th>
                                                <th class="px-4 py-2 text-left font-medium text-gray-900">Status</th>
                                                <th class="px-4 py-2 text-left font-medium text-gray-900">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <?php foreach ($exceptions as $exception): ?>
                                                <tr>
                                                    <td class="px-4 py-2">
                                                        <div class="font-medium">
                                                            <?php echo htmlspecialchars($exception['first_name'] . ' ' . $exception['last_name']); ?>
                                                        </div>
                                                        <div class="text-xs text-gray-500">
                                                            <?php echo htmlspecialchars($exception['employee_number']); ?></div>
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <?php echo htmlspecialchars($exception['department_name']); ?></td>
                                                    <td class="px-4 py-2">
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            Missing Entry
                                                        </span>
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <button onclick="addPayrollEntry(<?php echo $exception['id']; ?>)"
                                                            class="text-blue-600 hover:text-blue-800 text-xs px-2 py-1 rounded hover:bg-blue-50">
                                                            Add Entry
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Payroll Calendar -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Payroll Calendar</div>
                                    <div class="text-sm text-slate-500">Last 12 periods</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Period</th>
                                            <th class="text-left px-3 py-2 font-semibold">Start Date</th>
                                            <th class="text-left px-3 py-2 font-semibold">End Date</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                            <th class="text-left px-3 py-2 font-semibold">Entries</th>
                                            <th class="text-left px-3 py-2 font-semibold">Total Amount</th>
                                            <th class="text-left px-3 py-2 font-semibold">Avg Pay</th>
                                            <th class="text-left px-3 py-2 font-semibold">Processed</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payrollPeriods as $period): ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo date('M Y', strtotime($period['period_start'])); ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <?php echo date('M j, Y', strtotime($period['period_start'])); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <?php echo date('M j, Y', strtotime($period['period_end'])); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 text-xs rounded-full <?php
                                                    echo $period['status'] === 'Open' ? 'bg-green-100 text-green-800' :
                                                        ($period['status'] === 'Processed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800');
                                                    ?>">
                                                        <?php echo htmlspecialchars($period['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="text-sm font-medium">
                                                        <?php echo number_format($period['entry_count']); ?></div>
                                                    <div class="text-xs text-gray-500">employees</div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="text-sm font-medium">
                                                        ₱<?php echo number_format($period['total_amount'] ?? 0, 0); ?></div>
                                                    <div class="text-xs text-gray-500">total</div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="text-sm font-medium">
                                                        ₱<?php echo number_format($period['avg_pay'] ?? 0, 0); ?></div>
                                                    <div class="text-xs text-gray-500">average</div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <?php echo $period['processed_date'] ? date('M j, Y', strtotime($period['processed_date'])) : 'Not processed'; ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex gap-1">
                                                        <?php if ($period['status'] === 'Open'): ?>
                                                            <button onclick="processPayroll(<?php echo $period['id']; ?>)"
                                                                class="text-blue-600 hover:text-blue-800 text-xs">Process</button>
                                                        <?php elseif ($period['status'] === 'Processed'): ?>
                                                            <button onclick="closePayroll(<?php echo $period['id']; ?>)"
                                                                class="text-orange-600 hover:text-orange-800 text-xs">Close</button>
                                                        <?php endif; ?>
                                                        <button onclick="viewPayroll(<?php echo $period['id']; ?>)"
                                                            class="text-green-600 hover:text-green-800 text-xs">View</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Current Payroll Entries -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Current Payroll Entries</div>
                                    <div class="text-sm text-slate-500"><?php echo count($currentPayrollEntries); ?>
                                        entries</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Department</th>
                                            <th class="text-left px-3 py-2 font-semibold">Basic Salary</th>
                                            <th class="text-left px-3 py-2 font-semibold">Overtime</th>
                                            <th class="text-left px-3 py-2 font-semibold">Deductions</th>
                                            <th class="text-left px-3 py-2 font-semibold">Net Pay</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($currentPayrollEntries)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="7">
                                                    <div
                                                        class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
                                                        <div class="text-sm font-medium">No payroll entries</div>
                                                        <div class="text-xs text-slate-500 mt-1">Process payroll to generate
                                                            entries.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($currentPayrollEntries as $entry): ?>
                                                <tr
                                                    class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                    <td class="px-3 py-3">
                                                        <div>
                                                            <div class="font-medium">
                                                                <?php echo htmlspecialchars($entry['first_name'] . ' ' . $entry['last_name']); ?>
                                                            </div>
                                                            <div class="text-xs text-slate-500">
                                                                <?php echo htmlspecialchars($entry['employee_number']); ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php echo htmlspecialchars($entry['department_name'] ?? 'N/A'); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        ₱<?php echo number_format($entry['basic_salary'], 2); ?></td>
                                                    <td class="px-3 py-3">₱<?php echo number_format($entry['overtime'], 2); ?>
                                                    </td>
                                                    <td class="px-3 py-3">₱<?php echo number_format($entry['deductions'], 2); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="font-semibold">
                                                            ₱<?php echo number_format($entry['net_pay'], 2); ?></div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span
                                                            class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                                            Processed
                                                        </span>
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

    <!-- Process Payroll Modal -->
    <div id="processModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Process Payroll</h3>
                <p class="text-sm text-slate-600 mb-4">Are you sure you want to process this payroll period?</p>
                <form method="POST" id="processForm">
                    <input type="hidden" name="action" value="process_payroll">
                    <input type="hidden" name="period_id" id="processPeriodId">
                    <div class="flex gap-2">
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Process
                            Payroll</button>
                        <button type="button" onclick="closeProcessModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Close Payroll Modal -->
    <div id="closeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Close Payroll Period</h3>
                <p class="text-sm text-slate-600 mb-4">Are you sure you want to close this payroll period? This action
                    cannot be undone.</p>
                <form method="POST" id="closeForm">
                    <input type="hidden" name="action" value="close_payroll">
                    <input type="hidden" name="period_id" id="closePeriodId">
                    <div class="flex gap-2">
                        <button type="submit"
                            class="flex-1 bg-orange-600 text-white py-2 px-4 rounded-md hover:bg-orange-700">Close
                            Period</button>
                        <button type="button" onclick="closeCloseModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        function processPayroll(periodId) {
            document.getElementById('processPeriodId').value = periodId;
            document.getElementById('processModal').classList.remove('hidden');
        }

        function closeProcessModal() {
            document.getElementById('processModal').classList.add('hidden');
        }

        function closePayroll(periodId) {
            document.getElementById('closePeriodId').value = periodId;
            document.getElementById('closeModal').classList.remove('hidden');
        }

        function closeCloseModal() {
            document.getElementById('closeModal').classList.add('hidden');
        }

        function openNewPeriodModal() {
            alert('New payroll period functionality coming soon');
        }

        function viewPayroll(id) {
            alert('View payroll ' + id);
        }

        function exportPayroll() {
            alert('Export payroll functionality coming soon');
        }

        function generateReports() {
            alert('Generate reports functionality coming soon');
        }

        function viewPayrollCalendar() {
            // Scroll to payroll calendar section
            document.querySelector('.rounded-lg.border.border-\\[hsl\\(var\\(--border\\)\\)\\]').scrollIntoView({ behavior: 'smooth' });
        }

        function handleExceptions() {
            // Scroll to exceptions section
            const exceptionsSection = document.querySelector('.bg-red-50');
            if (exceptionsSection) {
                exceptionsSection.scrollIntoView({ behavior: 'smooth' });
            } else {
                alert('No payroll exceptions found');
            }
        }

        function addPayrollEntry(employeeId) {
            if (confirm('Add payroll entry for this employee?')) {
                // This would open a modal to add payroll entry
                alert('Add payroll entry for employee ' + employeeId + ' - This would open a form to add payroll details');
            }
        }

        function viewPayrollPeriod(periodId) {
            // This would show detailed payroll period information
            alert('View payroll period ' + periodId + ' - This would show detailed period information');
        }

        function processPayrollPeriod(periodId) {
            if (confirm('Process this payroll period?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="process_payroll">
                    <input type="hidden" name="period_id" value="${periodId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function closePayrollPeriod(periodId) {
            if (confirm('Close this payroll period?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="close_payroll">
                    <input type="hidden" name="period_id" value="${periodId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>