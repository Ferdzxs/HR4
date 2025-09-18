<?php
// HR Manager Payroll Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../shared/database_helper.php';
include_once __DIR__ . '/../../routing/rbac.php';

$activeId = 'payroll';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Initialize database helper
$dbHelper = new DatabaseHelper();

// Handle CRUD operations
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_payroll_period') {
        try {
            $periodName = $_POST['period_name'] ?? '';
            $periodStart = $_POST['period_start'] ?? '';
            $periodEnd = $_POST['period_end'] ?? '';

            $dbHelper->query("
                INSERT INTO payroll_periods (period_name, period_start, period_end, status) 
                VALUES (?, ?, ?, 'Open')
            ", [$periodName, $periodStart, $periodEnd]);

            $message = 'Payroll period created successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error creating payroll period: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'update_payroll_period') {
        try {
            $periodId = intval($_POST['period_id'] ?? 0);
            $periodName = $_POST['period_name'] ?? '';
            $periodStart = $_POST['period_start'] ?? '';
            $periodEnd = $_POST['period_end'] ?? '';
            $status = $_POST['status'] ?? 'Open';

            $dbHelper->query("
                UPDATE payroll_periods 
                SET period_name = ?, period_start = ?, period_end = ?, status = ?
                WHERE id = ?
            ", [$periodName, $periodStart, $periodEnd, $status, $periodId]);

            $message = 'Payroll period updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error updating payroll period: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete_payroll_period') {
        try {
            $periodId = intval($_POST['period_id'] ?? 0);
            $dbHelper->query("DELETE FROM payroll_periods WHERE id = ?", [$periodId]);
            $message = 'Payroll period deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error deleting payroll period: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'create_payroll_entry') {
        try {
            $periodId = intval($_POST['period_id'] ?? 0);
            $employeeId = intval($_POST['employee_id'] ?? 0);
            $basicSalary = floatval($_POST['basic_salary'] ?? 0);
            $overtimePay = floatval($_POST['overtime_pay'] ?? 0);
            $allowances = floatval($_POST['allowances'] ?? 0);
            $deductions = floatval($_POST['deductions'] ?? 0);

            $dbHelper->query("
                INSERT INTO payroll_entries (period_id, employee_id, basic_salary, overtime_pay, allowances, deductions, net_pay) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ", [$periodId, $employeeId, $basicSalary, $overtimePay, $allowances, $deductions, ($basicSalary + $overtimePay + $allowances - $deductions)]);

            $message = 'Payroll entry created successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error creating payroll entry: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'update_payroll_entry') {
        try {
            $entryId = intval($_POST['entry_id'] ?? 0);
            $basicSalary = floatval($_POST['basic_salary'] ?? 0);
            $overtimePay = floatval($_POST['overtime_pay'] ?? 0);
            $allowances = floatval($_POST['allowances'] ?? 0);
            $deductions = floatval($_POST['deductions'] ?? 0);

            $dbHelper->query("
                UPDATE payroll_entries 
                SET basic_salary = ?, overtime_pay = ?, allowances = ?, deductions = ?, net_pay = ?
                WHERE id = ?
            ", [$basicSalary, $overtimePay, $allowances, $deductions, ($basicSalary + $overtimePay + $allowances - $deductions), $entryId]);

            $message = 'Payroll entry updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error updating payroll entry: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete_payroll_entry') {
        try {
            $entryId = intval($_POST['entry_id'] ?? 0);
            $dbHelper->query("DELETE FROM payroll_entries WHERE id = ?", [$entryId]);
            $message = 'Payroll entry deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error deleting payroll entry: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'create_salary_component') {
        try {
            $componentName = $_POST['component_name'] ?? '';
            $componentType = $_POST['component_type'] ?? '';
            $isTaxable = isset($_POST['is_taxable']) ? 1 : 0;
            $isMandatory = isset($_POST['is_mandatory']) ? 1 : 0;

            $dbHelper->query("
                INSERT INTO salary_components (component_name, component_type, is_taxable, is_mandatory) 
                VALUES (?, ?, ?, ?)
            ", [$componentName, $componentType, $isTaxable, $isMandatory]);

            $message = 'Salary component created successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error creating salary component: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'update_salary_component') {
        try {
            $componentId = intval($_POST['component_id'] ?? 0);
            $componentName = $_POST['component_name'] ?? '';
            $componentType = $_POST['component_type'] ?? '';
            $isTaxable = isset($_POST['is_taxable']) ? 1 : 0;
            $isMandatory = isset($_POST['is_mandatory']) ? 1 : 0;

            $dbHelper->query("
                UPDATE salary_components 
                SET component_name = ?, component_type = ?, is_taxable = ?, is_mandatory = ?
                WHERE id = ?
            ", [$componentName, $componentType, $isTaxable, $isMandatory, $componentId]);

            $message = 'Salary component updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error updating salary component: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete_salary_component') {
        try {
            $componentId = intval($_POST['component_id'] ?? 0);
            $dbHelper->query("DELETE FROM salary_components WHERE id = ?", [$componentId]);
            $message = 'Salary component deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error deleting salary component: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'process_period') {
        $periodId = intval($_POST['period_id'] ?? 0);
        if ($periodId > 0) {
            // Mark period as Processed and set processed_date
            $dbHelper->query("UPDATE payroll_periods SET status = 'Processed', processed_date = CURDATE() WHERE id = ?", [$periodId]);
            $message = 'Payroll period processed successfully!';
            $messageType = 'success';
        }
    }
}

// Get payroll periods
$payrollPeriods = $dbHelper->getPayrollPeriods();

// Get salary components for CRUD operations
$salaryComponents = $dbHelper->fetchAll("SELECT * FROM salary_components ORDER BY component_name");

// Get all employees for payroll entries
$allEmployees = $dbHelper->getEmployees(1000);

// Selected period via query
$selectedPeriodId = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;

// Get current period data
if ($selectedPeriodId) {
    $currentPeriod = $dbHelper->fetchOne("SELECT * FROM payroll_periods WHERE id = ?", [$selectedPeriodId]);
} else {
    $currentPeriod = $dbHelper->fetchOne("SELECT * FROM payroll_periods WHERE status = 'Open' ORDER BY period_end DESC LIMIT 1");
    if (!$currentPeriod) {
        $currentPeriod = $dbHelper->fetchOne("SELECT * FROM payroll_periods ORDER BY period_end DESC LIMIT 1");
    }
}

$payrollData = $currentPeriod ? $dbHelper->getPayrollData($currentPeriod['id']) : [];

// Get government contributions
$govContributions = $currentPeriod ? $dbHelper->getGovernmentContributions($currentPeriod['id']) : [];

// Calculate totals
$totalBasicSalary = array_sum(array_column($payrollData, 'basic_salary'));
$totalOvertime = array_sum(array_column($payrollData, 'overtime'));
$totalDeductions = array_sum(array_column($payrollData, 'deductions'));
$totalNetPay = array_sum(array_column($payrollData, 'net_pay'));

// Get recent periods
$recentPeriods = array_slice($payrollPeriods, 0, 5);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Payroll Overview</title>
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
                            <h1 class="text-lg font-semibold">Payroll Overview</h1>
                            <p class="text-xs text-slate-500 mt-1">Processing calendar, approvals and exception handling
                            </p>
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

                        <!-- Payroll Stats -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Total Basic Salary</div>
                                        <div class="text-2xl font-semibold">
                                            ₱<?php echo number_format($totalBasicSalary, 0); ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Overtime Pay</div>
                                        <div class="text-2xl font-semibold">
                                            ₱<?php echo number_format($totalOvertime, 0); ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Total Deductions</div>
                                        <div class="text-2xl font-semibold text-red-600">
                                            ₱<?php echo number_format($totalDeductions, 0); ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-red-100 dark:bg-red-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 12H4m16 0l-4-4m4 4l-4 4M4 12l4-4m-4 4l4 4"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Net Pay</div>
                                        <div class="text-2xl font-semibold text-green-600">
                                            ₱<?php echo number_format($totalNetPay, 0); ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Period Info -->
                        <?php if ($currentPeriod): ?>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h3 class="font-semibold text-slate-900 dark:text-slate-100">Current Payroll Period
                                        </h3>
                                        <p class="text-sm text-slate-500">
                                            <?php echo date('M j, Y', strtotime($currentPeriod['period_start'])); ?> -
                                            <?php echo date('M j, Y', strtotime($currentPeriod['period_end'])); ?>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium <?php
                                        echo $currentPeriod['status'] === 'Open' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' :
                                            ($currentPeriod['status'] === 'Processed' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' :
                                                'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400');
                                        ?>">
                                            <?php echo htmlspecialchars($currentPeriod['status']); ?>
                                        </span>
                                        <form method="post">
                                            <input type="hidden" name="action" value="process_period">
                                            <input type="hidden" name="period_id"
                                                value="<?php echo (int) $currentPeriod['id']; ?>">
                                            <button type="submit"
                                                class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-4 py-2 rounded-md text-sm hover:opacity-95 transition-opacity">
                                                Process Payroll
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Payroll Data Table -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <h3 class="font-semibold">Payroll Entries</h3>
                                        <span
                                            class="px-2 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400 text-xs rounded-full">
                                            <?php echo count($payrollData); ?> employees
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        <form method="get">
                                            <input type="hidden" name="page" value="payroll">
                                            <select name="period_id"
                                                class="px-3 py-1 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] text-sm"
                                                onchange="this.form.submit()">
                                                <?php foreach ($recentPeriods as $period): ?>
                                                    <option value="<?php echo $period['id']; ?>" <?php echo ($currentPeriod && $currentPeriod['id'] == $period['id']) ? 'selected' : ''; ?>>
                                                        <?php echo date('M j', strtotime($period['period_start'])); ?> -
                                                        <?php echo date('M j, Y', strtotime($period['period_end'])); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                        <button
                                            class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-3 py-1 rounded-md text-sm hover:opacity-95 transition-opacity">
                                            Export
                                        </button>
                                    </div>
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
                                        <?php if (empty($payrollData)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="7">
                                                    <div class="text-center py-10">
                                                        <div class="text-sm font-medium">No payroll data</div>
                                                        <div class="text-xs text-slate-500 mt-1">Process payroll to view
                                                            entries.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($payrollData as $entry): ?>
                                                <tr
                                                    class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                    <td class="px-3 py-3">
                                                        <div class="flex items-center gap-3">
                                                            <div
                                                                class="w-8 h-8 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center">
                                                                <span
                                                                    class="text-xs font-semibold text-blue-600 dark:text-blue-400">
                                                                    <?php echo strtoupper(substr($entry['employee_name'], 0, 2)); ?>
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <div class="font-medium text-slate-900 dark:text-slate-100">
                                                                    <?php echo htmlspecialchars($entry['employee_name']); ?>
                                                                </div>
                                                                <div class="text-xs text-slate-500">
                                                                    <?php echo htmlspecialchars($entry['employee_number']); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <?php echo htmlspecialchars($entry['department_name'] ?? 'N/A'); ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        ₱<?php echo number_format($entry['basic_salary'], 2); ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        ₱<?php echo number_format($entry['overtime'], 2); ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        ₱<?php echo number_format($entry['deductions'], 2); ?>
                                                    </td>
                                                    <td class="px-3 py-3 font-semibold text-green-600 dark:text-green-400">
                                                        ₱<?php echo number_format($entry['net_pay'], 2); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
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

                        <!-- Government Contributions -->
                        <?php if (!empty($govContributions)): ?>
                            <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                                <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                    <h3 class="font-semibold">Government Contributions</h3>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-[hsl(var(--secondary))]">
                                            <tr>
                                                <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                                <th class="text-left px-3 py-2 font-semibold">SSS</th>
                                                <th class="text-left px-3 py-2 font-semibold">PhilHealth</th>
                                                <th class="text-left px-3 py-2 font-semibold">PagIBIG</th>
                                                <th class="text-left px-3 py-2 font-semibold">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $groupedContributions = [];
                                            foreach ($govContributions as $contrib) {
                                                $empId = $contrib['employee_id'];
                                                if (!isset($groupedContributions[$empId])) {
                                                    $groupedContributions[$empId] = [
                                                        'employee_name' => $contrib['employee_name'],
                                                        'employee_number' => $contrib['employee_number'],
                                                        'SSS' => 0,
                                                        'PhilHealth' => 0,
                                                        'PagIBIG' => 0
                                                    ];
                                                }
                                                $groupedContributions[$empId][$contrib['contribution_type']] = $contrib['amount'];
                                            }
                                            ?>
                                            <?php foreach ($groupedContributions as $contrib): ?>
                                                <tr
                                                    class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                    <td class="px-3 py-3">
                                                        <div>
                                                            <div class="font-medium text-slate-900 dark:text-slate-100">
                                                                <?php echo htmlspecialchars($contrib['employee_name']); ?>
                                                            </div>
                                                            <div class="text-xs text-slate-500">
                                                                <?php echo htmlspecialchars($contrib['employee_number']); ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        ₱<?php echo number_format($contrib['SSS'], 2); ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        ₱<?php echo number_format($contrib['PhilHealth'], 2); ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        ₱<?php echo number_format($contrib['PagIBIG'], 2); ?>
                                                    </td>
                                                    <td class="px-3 py-3 font-semibold text-slate-900 dark:text-slate-100">
                                                        ₱<?php echo number_format($contrib['SSS'] + $contrib['PhilHealth'] + $contrib['PagIBIG'], 2); ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Recent Periods -->
                        <div
                            class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                            <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Recent Payroll Periods
                            </div>
                            <div class="p-4">
                                <?php if (empty($recentPeriods)): ?>
                                    <div class="text-sm text-slate-500">No payroll periods found</div>
                                <?php else: ?>
                                    <div class="space-y-3">
                                        <?php foreach ($recentPeriods as $period): ?>
                                            <div
                                                class="flex items-center justify-between p-3 rounded-lg border border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                <div>
                                                    <div class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                                        <?php echo date('M j', strtotime($period['period_start'])); ?> -
                                                        <?php echo date('M j, Y', strtotime($period['period_end'])); ?>
                                                    </div>
                                                    <div class="text-xs text-slate-500">
                                                        <?php echo $period['processed_date'] ? 'Processed on ' . date('M j, Y', strtotime($period['processed_date'])) : 'Not processed'; ?>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php
                                                    echo $period['status'] === 'Open' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' :
                                                        ($period['status'] === 'Processed' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' :
                                                            'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400');
                                                    ?>">
                                                        <?php echo htmlspecialchars($period['status']); ?>
                                                    </span>
                                                    <button class="p-1 text-slate-400 hover:text-blue-600 transition-colors"
                                                        title="View Details">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Payroll Periods Management -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <h3 class="font-semibold">Payroll Periods Management</h3>
                                        <span
                                            class="px-2 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400 text-xs rounded-full">
                                            <?php echo count($payrollPeriods); ?> periods
                                        </span>
                                    </div>
                                    <button onclick="openCreatePeriodModal()"
                                        class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-3 py-1 rounded-md text-sm hover:opacity-95 transition-opacity">
                                        Add Period
                                    </button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Period Name</th>
                                            <th class="text-left px-3 py-2 font-semibold">Start Date</th>
                                            <th class="text-left px-3 py-2 font-semibold">End Date</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payrollPeriods as $period): ?>
                                            <tr
                                                class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                <td class="px-3 py-3 font-medium text-slate-900 dark:text-slate-100">
                                                    <?php echo htmlspecialchars($period['period_name']); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    <?php echo date('M j, Y', strtotime($period['period_start'])); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    <?php echo date('M j, Y', strtotime($period['period_end'])); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php
                                                    echo $period['status'] === 'Open' ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' :
                                                        ($period['status'] === 'Processed' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' :
                                                            'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400');
                                                    ?>">
                                                        <?php echo htmlspecialchars($period['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex items-center gap-1">
                                                        <button
                                                            onclick="openEditPeriodModal(<?php echo $period['id']; ?>, '<?php echo htmlspecialchars($period['period_name']); ?>', '<?php echo $period['period_start']; ?>', '<?php echo $period['period_end']; ?>', '<?php echo $period['status']; ?>')"
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
                                                            onclick="openDeletePeriodModal(<?php echo $period['id']; ?>, '<?php echo htmlspecialchars($period['period_name']); ?>')"
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

                        <!-- Salary Components Management -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <h3 class="font-semibold">Salary Components Management</h3>
                                        <span
                                            class="px-2 py-1 bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400 text-xs rounded-full">
                                            <?php echo count($salaryComponents); ?> components
                                        </span>
                                    </div>
                                    <button onclick="openCreateComponentModal()"
                                        class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-3 py-1 rounded-md text-sm hover:opacity-95 transition-opacity">
                                        Add Component
                                    </button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Component Name</th>
                                            <th class="text-left px-3 py-2 font-semibold">Type</th>
                                            <th class="text-left px-3 py-2 font-semibold">Taxable</th>
                                            <th class="text-left px-3 py-2 font-semibold">Mandatory</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($salaryComponents as $component): ?>
                                            <tr
                                                class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                <td class="px-3 py-3 font-medium text-slate-900 dark:text-slate-100">
                                                    <?php echo htmlspecialchars($component['component_name']); ?>
                                                </td>
                                                <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                    <?php echo htmlspecialchars($component['component_type']); ?>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span
                                                        class="px-2 py-1 rounded-full text-xs font-medium <?php echo $component['is_taxable'] ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' : 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'; ?>">
                                                        <?php echo $component['is_taxable'] ? 'Yes' : 'No'; ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span
                                                        class="px-2 py-1 rounded-full text-xs font-medium <?php echo $component['is_mandatory'] ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'; ?>">
                                                        <?php echo $component['is_mandatory'] ? 'Yes' : 'No'; ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex items-center gap-1">
                                                        <button
                                                            onclick="openEditComponentModal(<?php echo $component['id']; ?>, '<?php echo htmlspecialchars($component['component_name']); ?>', '<?php echo $component['component_type']; ?>', <?php echo $component['is_taxable']; ?>, <?php echo $component['is_mandatory']; ?>)"
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
                                                            onclick="openDeleteComponentModal(<?php echo $component['id']; ?>, '<?php echo htmlspecialchars($component['component_name']); ?>')"
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

    <!-- Create Payroll Period Modal -->
    <div id="createPeriodModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Add Payroll Period</h3>
                    <button onclick="closeCreatePeriodModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create_payroll_period">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Period Name
                        *</label>
                    <input type="text" name="period_name" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Start Date
                            *</label>
                        <input type="date" name="period_start" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">End Date
                            *</label>
                        <input type="date" name="period_end" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCreatePeriodModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Create Period
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Payroll Period Modal -->
    <div id="editPeriodModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Edit Payroll Period</h3>
                    <button onclick="closeEditPeriodModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="update_payroll_period">
                <input type="hidden" name="period_id" id="edit_period_id">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Period Name
                        *</label>
                    <input type="text" name="period_name" id="edit_period_name" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Start Date
                            *</label>
                        <input type="date" name="period_start" id="edit_period_start" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">End Date
                            *</label>
                        <input type="date" name="period_end" id="edit_period_end" required
                            class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status *</label>
                    <select name="status" id="edit_period_status" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="Open">Open</option>
                        <option value="Processed">Processed</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditPeriodModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Update Period
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Period Confirmation Modal -->
    <div id="deletePeriodModal"
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
                    Are you sure you want to delete this payroll period? This will also delete all associated payroll
                    entries.
                </p>
                <form method="POST" id="deletePeriodForm">
                    <input type="hidden" name="action" value="delete_payroll_period">
                    <input type="hidden" name="period_id" id="delete_period_id">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeDeletePeriodModal()"
                            class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            Delete Period
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Salary Component Modal -->
    <div id="createComponentModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Add Salary Component</h3>
                    <button onclick="closeCreateComponentModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create_salary_component">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Component Name
                        *</label>
                    <input type="text" name="component_name" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Component Type
                        *</label>
                    <select name="component_type" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Type</option>
                        <option value="Allowance">Allowance</option>
                        <option value="Deduction">Deduction</option>
                        <option value="Bonus">Bonus</option>
                        <option value="Overtime">Overtime</option>
                        <option value="Commission">Commission</option>
                    </select>
                </div>

                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_taxable" class="rounded border-[hsl(var(--border))]">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Taxable</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_mandatory" class="rounded border-[hsl(var(--border))]">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Mandatory</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCreateComponentModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Create Component
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Salary Component Modal -->
    <div id="editComponentModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Edit Salary Component</h3>
                    <button onclick="closeEditComponentModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="update_salary_component">
                <input type="hidden" name="component_id" id="edit_component_id">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Component Name
                        *</label>
                    <input type="text" name="component_name" id="edit_component_name" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Component Type
                        *</label>
                    <select name="component_type" id="edit_component_type" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Type</option>
                        <option value="Allowance">Allowance</option>
                        <option value="Deduction">Deduction</option>
                        <option value="Bonus">Bonus</option>
                        <option value="Overtime">Overtime</option>
                        <option value="Commission">Commission</option>
                    </select>
                </div>

                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_taxable" id="edit_is_taxable"
                            class="rounded border-[hsl(var(--border))]">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Taxable</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_mandatory" id="edit_is_mandatory"
                            class="rounded border-[hsl(var(--border))]">
                        <span class="text-sm text-slate-700 dark:text-slate-300">Mandatory</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeEditComponentModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Update Component
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Component Confirmation Modal -->
    <div id="deleteComponentModal"
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
                    Are you sure you want to delete this salary component? This will affect all payroll calculations
                    using this component.
                </p>
                <form method="POST" id="deleteComponentForm">
                    <input type="hidden" name="action" value="delete_salary_component">
                    <input type="hidden" name="component_id" id="delete_component_id">
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeDeleteComponentModal()"
                            class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            Delete Component
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        // Period Modal functions
        function openCreatePeriodModal() {
            document.getElementById('createPeriodModal').classList.remove('hidden');
        }

        function closeCreatePeriodModal() {
            document.getElementById('createPeriodModal').classList.add('hidden');
        }

        function openEditPeriodModal(periodId, periodName, periodStart, periodEnd, status) {
            document.getElementById('edit_period_id').value = periodId;
            document.getElementById('edit_period_name').value = periodName;
            document.getElementById('edit_period_start').value = periodStart;
            document.getElementById('edit_period_end').value = periodEnd;
            document.getElementById('edit_period_status').value = status;
            document.getElementById('editPeriodModal').classList.remove('hidden');
        }

        function closeEditPeriodModal() {
            document.getElementById('editPeriodModal').classList.add('hidden');
        }

        function openDeletePeriodModal(periodId, periodName) {
            document.getElementById('delete_period_id').value = periodId;
            document.getElementById('deletePeriodModal').classList.remove('hidden');
        }

        function closeDeletePeriodModal() {
            document.getElementById('deletePeriodModal').classList.add('hidden');
        }

        // Component Modal functions
        function openCreateComponentModal() {
            document.getElementById('createComponentModal').classList.remove('hidden');
        }

        function closeCreateComponentModal() {
            document.getElementById('createComponentModal').classList.add('hidden');
        }

        function openEditComponentModal(componentId, componentName, componentType, isTaxable, isMandatory) {
            document.getElementById('edit_component_id').value = componentId;
            document.getElementById('edit_component_name').value = componentName;
            document.getElementById('edit_component_type').value = componentType;
            document.getElementById('edit_is_taxable').checked = isTaxable == 1;
            document.getElementById('edit_is_mandatory').checked = isMandatory == 1;
            document.getElementById('editComponentModal').classList.remove('hidden');
        }

        function closeEditComponentModal() {
            document.getElementById('editComponentModal').classList.add('hidden');
        }

        function openDeleteComponentModal(componentId, componentName) {
            document.getElementById('delete_component_id').value = componentId;
            document.getElementById('deleteComponentModal').classList.remove('hidden');
        }

        function closeDeleteComponentModal() {
            document.getElementById('deleteComponentModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('fixed')) {
                closeCreatePeriodModal();
                closeEditPeriodModal();
                closeDeletePeriodModal();
                closeCreateComponentModal();
                closeEditComponentModal();
                closeDeleteComponentModal();
            }
        });
    </script>
</body>

</html>