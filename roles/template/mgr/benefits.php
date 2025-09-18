<?php
// HR Manager Benefits Administration Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../shared/database_helper.php';
include_once __DIR__ . '/../../routing/rbac.php';

$activeId = 'benefits';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Initialize database helper
$dbHelper = new DatabaseHelper();

// Handle CRUD operations
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_hmo_plan') {
        try {
            $planName = $_POST['plan_name'] ?? '';
            $providerId = intval($_POST['provider_id'] ?? 0);
            $premiumAmount = floatval($_POST['premium_amount'] ?? 0);
            $coverageDetails = $_POST['coverage_details'] ?? '';

            $dbHelper->query("
                INSERT INTO hmo_plans (plan_name, provider_id, premium_amount, coverage_details) 
                VALUES (?, ?, ?, ?)
            ", [$planName, $providerId, $premiumAmount, $coverageDetails]);

            $message = 'HMO plan created successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error creating HMO plan: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'update_hmo_plan') {
        try {
            $planId = intval($_POST['plan_id'] ?? 0);
            $planName = $_POST['plan_name'] ?? '';
            $providerId = intval($_POST['provider_id'] ?? 0);
            $premiumAmount = floatval($_POST['premium_amount'] ?? 0);
            $coverageDetails = $_POST['coverage_details'] ?? '';

            $dbHelper->query("
                UPDATE hmo_plans 
                SET plan_name = ?, provider_id = ?, premium_amount = ?, coverage_details = ?
                WHERE id = ?
            ", [$planName, $providerId, $premiumAmount, $coverageDetails, $planId]);

            $message = 'HMO plan updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error updating HMO plan: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete_hmo_plan') {
        try {
            $planId = intval($_POST['plan_id'] ?? 0);
            $dbHelper->query("DELETE FROM hmo_plans WHERE id = ?", [$planId]);
            $message = 'HMO plan deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error deleting HMO plan: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'create_enrollment') {
        try {
            $employeeId = intval($_POST['employee_id'] ?? 0);
            $planId = intval($_POST['plan_id'] ?? 0);
            $enrollmentDate = $_POST['enrollment_date'] ?? date('Y-m-d');

            $dbHelper->query("
                INSERT INTO benefit_enrollments (employee_id, plan_id, enrollment_date, status) 
                VALUES (?, ?, ?, 'Active')
            ", [$employeeId, $planId, $enrollmentDate]);

            $message = 'Employee enrolled successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error enrolling employee: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'update_enrollment') {
        try {
            $enrollmentId = intval($_POST['enrollment_id'] ?? 0);
            $planId = intval($_POST['plan_id'] ?? 0);
            $status = $_POST['status'] ?? 'Active';

            $dbHelper->query("
                UPDATE benefit_enrollments 
                SET plan_id = ?, status = ?
                WHERE id = ?
            ", [$planId, $status, $enrollmentId]);

            $message = 'Enrollment updated successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error updating enrollment: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($action === 'delete_enrollment') {
        try {
            $enrollmentId = intval($_POST['enrollment_id'] ?? 0);
            $dbHelper->query("DELETE FROM benefit_enrollments WHERE id = ?", [$enrollmentId]);
            $message = 'Enrollment deleted successfully!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error deleting enrollment: ' . $e->getMessage();
            $messageType = 'error';
        }
    } elseif (in_array($action, ['approve_claim', 'reject_claim'])) {
        $claimId = intval($_POST['claim_id'] ?? 0);
        if ($claimId > 0) {
            $newStatus = $action === 'approve_claim' ? 'Approved' : 'Rejected';
            $dbHelper->query("UPDATE benefit_claims SET status = ? WHERE id = ?", [$newStatus, $claimId]);
            $message = 'Claim ' . strtolower($newStatus) . ' successfully!';
            $messageType = 'success';
        }
    }
}

// Get benefits data
$statusFilter = $_GET['status'] ?? '';

$benefitsData = $dbHelper->getBenefitsData();
$hmoPlans = $dbHelper->getHMOPlans();
$benefitClaims = $statusFilter && in_array($statusFilter, ['Pending', 'Approved', 'Rejected', 'Paid'])
    ? $dbHelper->getBenefitClaims($statusFilter)
    : $dbHelper->getBenefitClaims();

// Get providers
$providers = $dbHelper->fetchAll("SELECT * FROM providers ORDER BY provider_name");

// Calculate totals
$totalEnrollments = count($benefitsData);
$totalPlans = count($hmoPlans);
$totalClaims = count($benefitClaims);
$pendingClaims = count(array_filter($benefitClaims, function ($claim) {
    return $claim['status'] === 'Pending';
}));

// Get claim statistics
$totalClaimAmount = array_sum(array_column($benefitClaims, 'claim_amount'));
$paidClaims = array_filter($benefitClaims, function ($claim) {
    return $claim['status'] === 'Paid';
});
$totalPaidAmount = array_sum(array_column($paidClaims, 'claim_amount'));

// Get recent claims for better display
$recentClaims = array_slice($benefitClaims, 0, 10);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Benefits Administration</title>
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
                            <h1 class="text-lg font-semibold">Benefits Administration</h1>
                            <p class="text-xs text-slate-500 mt-1">Plans, enrollments, claims and providers</p>
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

                        <!-- Benefits Stats -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Active Enrollments</div>
                                        <div class="text-2xl font-semibold"><?php echo $totalEnrollments; ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">HMO Plans</div>
                                        <div class="text-2xl font-semibold"><?php echo $totalPlans; ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
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
                                        <div class="text-xs text-slate-500 mb-1">Total Claims</div>
                                        <div class="text-2xl font-semibold"><?php echo $totalClaims; ?></div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Pending Claims</div>
                                        <div class="text-2xl font-semibold text-orange-600">
                                            <?php echo $pendingClaims; ?>
                                        </div>
                                    </div>
                                    <div
                                        class="w-10 h-10 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- HMO Plans Overview -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <h3 class="font-semibold">HMO Plans</h3>
                                        <span
                                            class="px-2 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400 text-xs rounded-full">
                                            <?php echo $totalPlans; ?> plans
                                        </span>
                                    </div>
                                    <button onclick="openCreatePlanModal()"
                                        class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-3 py-1 rounded-md text-sm hover:opacity-95 transition-opacity">
                                        Add Plan
                                    </button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Plan Name</th>
                                            <th class="text-left px-3 py-2 font-semibold">Provider</th>
                                            <th class="text-left px-3 py-2 font-semibold">Premium</th>
                                            <th class="text-left px-3 py-2 font-semibold">Coverage</th>
                                            <th class="text-left px-3 py-2 font-semibold">Enrollments</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($hmoPlans)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="6">
                                                    <div class="text-center py-10">
                                                        <div class="text-sm font-medium">No HMO plans</div>
                                                        <div class="text-xs text-slate-500 mt-1">Add HMO plans to get
                                                            started.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($hmoPlans as $plan): ?>
                                                <?php
                                                $enrollmentCount = count(array_filter($benefitsData, function ($benefit) use ($plan) {
                                                    return $benefit['plan_id'] == $plan['id'];
                                                }));
                                                ?>
                                                <tr
                                                    class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                    <td class="px-3 py-3">
                                                        <div class="font-medium text-slate-900 dark:text-slate-100">
                                                            <?php echo htmlspecialchars($plan['plan_name']); ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <?php echo htmlspecialchars($plan['provider_name']); ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        ₱<?php echo number_format($plan['premium_amount'], 2); ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <div class="max-w-xs truncate"
                                                            title="<?php echo htmlspecialchars($plan['coverage_details']); ?>">
                                                            <?php echo htmlspecialchars(substr($plan['coverage_details'], 0, 50)) . '...'; ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span
                                                            class="px-2 py-1 bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400 text-xs rounded-full">
                                                            <?php echo $enrollmentCount; ?> enrolled
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="flex items-center gap-1">
                                                            <button
                                                                class="p-1 text-slate-400 hover:text-blue-600 transition-colors"
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
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Benefits Enrollments -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <h3 class="font-semibold">Employee Enrollments</h3>
                                        <span
                                            class="px-2 py-1 bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400 text-xs rounded-full">
                                            <?php echo $totalEnrollments; ?> active
                                        </span>
                                    </div>
                                    <button onclick="openCreateEnrollmentModal()"
                                        class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] px-3 py-1 rounded-md text-sm hover:opacity-95 transition-opacity">
                                        Enroll Employee
                                    </button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Plan</th>
                                            <th class="text-left px-3 py-2 font-semibold">Provider</th>
                                            <th class="text-left px-3 py-2 font-semibold">Premium</th>
                                            <th class="text-left px-3 py-2 font-semibold">Enrollment Date</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($benefitsData)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="6">
                                                    <div class="text-center py-10">
                                                        <div class="text-sm font-medium">No enrollments</div>
                                                        <div class="text-xs text-slate-500 mt-1">Enroll employees in benefit
                                                            plans.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach (array_slice($benefitsData, 0, 10) as $benefit): ?>
                                                <tr
                                                    class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                    <td class="px-3 py-3">
                                                        <div class="flex items-center gap-3">
                                                            <div
                                                                class="w-8 h-8 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center">
                                                                <span
                                                                    class="text-xs font-semibold text-blue-600 dark:text-blue-400">
                                                                    <?php echo strtoupper(substr($benefit['employee_name'], 0, 2)); ?>
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <div class="font-medium text-slate-900 dark:text-slate-100">
                                                                    <?php echo htmlspecialchars($benefit['employee_name']); ?>
                                                                </div>
                                                                <div class="text-xs text-slate-500">
                                                                    <?php echo htmlspecialchars($benefit['employee_number']); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <?php echo htmlspecialchars($benefit['plan_name']); ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <?php echo htmlspecialchars($benefit['provider_name']); ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        ₱<?php echo number_format($benefit['premium_amount'], 2); ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <?php echo date('M j, Y', strtotime($benefit['enrollment_date'])); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                                            <?php echo htmlspecialchars($benefit['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Benefit Claims -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-4 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <h3 class="font-semibold">Benefit Claims</h3>
                                        <span
                                            class="px-2 py-1 bg-purple-100 dark:bg-purple-900/20 text-purple-800 dark:text-purple-400 text-xs rounded-full">
                                            <?php echo $totalClaims; ?> total
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        <form method="get" class="flex gap-2">
                                            <input type="hidden" name="page" value="benefits">
                                            <select name="status"
                                                class="px-3 py-1 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] text-sm"
                                                onchange="this.form.submit()">
                                                <option value="" <?php echo $statusFilter === '' ? 'selected' : ''; ?>>All
                                                    Status</option>
                                                <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="Approved" <?php echo $statusFilter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                                <option value="Paid" <?php echo $statusFilter === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                                                <option value="Rejected" <?php echo $statusFilter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            </select>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Plan</th>
                                            <th class="text-left px-3 py-2 font-semibold">Claim Amount</th>
                                            <th class="text-left px-3 py-2 font-semibold">Claim Date</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($benefitClaims)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="6">
                                                    <div class="text-center py-10">
                                                        <div class="text-sm font-medium">No claims</div>
                                                        <div class="text-xs text-slate-500 mt-1">Claims will appear here
                                                            when submitted.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recentClaims as $claim): ?>
                                                <tr
                                                    class="border-t border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                    <td class="px-3 py-3">
                                                        <div class="flex items-center gap-3">
                                                            <div
                                                                class="w-8 h-8 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center">
                                                                <span
                                                                    class="text-xs font-semibold text-blue-600 dark:text-blue-400">
                                                                    <?php echo strtoupper(substr($claim['employee_name'], 0, 2)); ?>
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <div class="font-medium text-slate-900 dark:text-slate-100">
                                                                    <?php echo htmlspecialchars($claim['employee_name']); ?>
                                                                </div>
                                                                <div class="text-xs text-slate-500">
                                                                    <?php echo htmlspecialchars($claim['employee_number']); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <?php echo htmlspecialchars($claim['plan_name']); ?>
                                                    </td>
                                                    <td class="px-3 py-3 font-semibold text-slate-900 dark:text-slate-100">
                                                        ₱<?php echo number_format($claim['claim_amount'], 2); ?>
                                                    </td>
                                                    <td class="px-3 py-3 text-slate-600 dark:text-slate-300">
                                                        <?php echo date('M j, Y', strtotime($claim['claim_date'])); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php
                                                        $statusColors = [
                                                            'Pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
                                                            'Approved' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
                                                            'Paid' => 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
                                                            'Rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
                                                        ];
                                                        $statusColor = $statusColors[$claim['status']] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
                                                        ?>
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                                                            <?php echo htmlspecialchars($claim['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="flex items-center gap-1">
                                                            <button
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
                                                            </button>
                                                            <?php if ($claim['status'] === 'Pending'): ?>
                                                                <form method="post" class="inline-block">
                                                                    <input type="hidden" name="action" value="approve_claim">
                                                                    <input type="hidden" name="claim_id"
                                                                        value="<?php echo (int) $claim['id']; ?>">
                                                                    <button type="submit"
                                                                        class="p-1 text-slate-400 hover:text-green-600 transition-colors"
                                                                        title="Approve">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                            viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                                stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                                        </svg>
                                                                    </button>
                                                                </form>
                                                                <form method="post" class="inline-block">
                                                                    <input type="hidden" name="action" value="reject_claim">
                                                                    <input type="hidden" name="claim_id"
                                                                        value="<?php echo (int) $claim['id']; ?>">
                                                                    <button type="submit"
                                                                        class="p-1 text-slate-400 hover:text-red-600 transition-colors"
                                                                        title="Reject">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                            viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                        </svg>
                                                                    </button>
                                                                </form>
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

                        <!-- Claim Statistics -->
                        <div class="grid lg:grid-cols-2 gap-4">
                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Claim Summary</div>
                                <div class="p-4">
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-slate-600 dark:text-slate-300">Total Claim
                                                Amount</span>
                                            <span
                                                class="font-semibold">₱<?php echo number_format($totalClaimAmount, 2); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-slate-600 dark:text-slate-300">Paid Amount</span>
                                            <span
                                                class="font-semibold text-green-600">₱<?php echo number_format($totalPaidAmount, 2); ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-slate-600 dark:text-slate-300">Pending
                                                Amount</span>
                                            <span
                                                class="font-semibold text-orange-600">₱<?php echo number_format($totalClaimAmount - $totalPaidAmount, 2); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] text-[hsl(var(--card-foreground))] shadow-sm">
                                <div class="p-4 border-b border-[hsl(var(--border))] font-semibold">Provider Network
                                </div>
                                <div class="p-4">
                                    <?php if (empty($providers)): ?>
                                        <div class="text-sm text-slate-500">No providers found</div>
                                    <?php else: ?>
                                        <div class="space-y-2">
                                            <?php foreach (array_slice($providers, 0, 5) as $provider): ?>
                                                <div
                                                    class="flex items-center justify-between p-2 rounded border border-[hsl(var(--border))] hover:bg-[hsl(var(--accent))] transition-colors">
                                                    <div class="font-medium text-slate-900 dark:text-slate-100">
                                                        <?php echo htmlspecialchars($provider['provider_name']); ?>
                                                    </div>
                                                    <div class="text-xs text-slate-500">
                                                        <?php echo count(array_filter($hmoPlans, function ($plan) use ($provider) {
                                                            return $plan['provider_id'] == $provider['id'];
                                                        })); ?>
                                                        plans
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>

    <!-- Create HMO Plan Modal -->
    <div id="createPlanModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-lg w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Add New HMO Plan</h3>
                    <button onclick="closeCreatePlanModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create_hmo_plan">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Plan Name *</label>
                    <input type="text" name="plan_name" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Provider *</label>
                    <select name="provider_id" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Provider</option>
                        <?php foreach ($providers as $provider): ?>
                            <option value="<?php echo $provider['id']; ?>">
                                <?php echo htmlspecialchars($provider['provider_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Premium Amount
                        *</label>
                    <input type="number" name="premium_amount" step="0.01" min="0" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Coverage Details
                        *</label>
                    <textarea name="coverage_details" rows="4" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCreatePlanModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Create Plan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Enrollment Modal -->
    <div id="createEnrollmentModal"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-[hsl(var(--card))] rounded-lg shadow-xl max-w-lg w-full">
            <div class="p-6 border-b border-[hsl(var(--border))]">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">Enroll Employee</h3>
                    <button onclick="closeCreateEnrollmentModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="create_enrollment">

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Employee *</label>
                    <select name="employee_id" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Employee</option>
                        <?php
                        $allEmployees = $dbHelper->getEmployees(1000);
                        foreach ($allEmployees as $emp):
                            ?>
                            <option value="<?php echo $emp['id']; ?>">
                                <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_number'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">HMO Plan *</label>
                    <select name="plan_id" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                        <option value="">Select Plan</option>
                        <?php foreach ($hmoPlans as $plan): ?>
                            <option value="<?php echo $plan['id']; ?>">
                                <?php echo htmlspecialchars($plan['plan_name'] . ' - ' . $plan['provider_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Enrollment Date
                        *</label>
                    <input type="date" name="enrollment_date" value="<?php echo date('Y-m-d'); ?>" required
                        class="w-full px-3 py-2 border border-[hsl(var(--border))] rounded-md bg-[hsl(var(--background))] text-[hsl(var(--foreground))] focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]">
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="closeCreateEnrollmentModal()"
                        class="px-4 py-2 border border-[hsl(var(--border))] text-[hsl(var(--foreground))] rounded-md hover:bg-[hsl(var(--accent))] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] rounded-md hover:opacity-95 transition-opacity">
                        Enroll Employee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        // Modal functions
        function openCreatePlanModal() {
            document.getElementById('createPlanModal').classList.remove('hidden');
        }

        function closeCreatePlanModal() {
            document.getElementById('createPlanModal').classList.add('hidden');
        }

        function openCreateEnrollmentModal() {
            document.getElementById('createEnrollmentModal').classList.remove('hidden');
        }

        function closeCreateEnrollmentModal() {
            document.getElementById('createEnrollmentModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('fixed')) {
                closeCreatePlanModal();
                closeCreateEnrollmentModal();
            }
        });
    </script>
</body>

</html>