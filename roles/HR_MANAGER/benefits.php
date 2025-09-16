<?php
// HR Manager Benefits Administration Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../routing/rbac.php';
include_once __DIR__ . '/../../config/database.php';

$activeId = 'benefits';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_enrollment':
                try {
                    $stmt = $pdo->prepare("INSERT INTO benefit_enrollments (employee_id, plan_id, enrollment_date, status) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $_POST['employee_id'],
                        $_POST['plan_id'],
                        $_POST['enrollment_date'],
                        'Active'
                    ]);
                    $success = "Benefit enrollment added successfully!";
                } catch (PDOException $e) {
                    $error = "Error adding enrollment: " . $e->getMessage();
                }
                break;

            case 'update_claim_status':
                try {
                    $stmt = $pdo->prepare("UPDATE benefit_claims SET status = ? WHERE id = ?");
                    $stmt->execute([$_POST['status'], $_POST['claim_id']]);
                    $success = "Claim status updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating claim: " . $e->getMessage();
                }
                break;
        }
    }
}

// Fetch benefits data
try {
    // HMO Plans with providers
    $stmt = $pdo->query("SELECT hp.*, p.provider_name, p.contact_info, COUNT(be.id) as enrollment_count
                        FROM hmo_plans hp
                        JOIN providers p ON hp.provider_id = p.id
                        LEFT JOIN benefit_enrollments be ON hp.id = be.plan_id AND be.status = 'Active'
                        GROUP BY hp.id
                        ORDER BY p.provider_name, hp.plan_name");
    $hmoPlans = $stmt->fetchAll();

    // All providers with detailed information
    $stmt = $pdo->query("SELECT p.*, COUNT(hp.id) as plan_count, COUNT(be.id) as total_enrollments,
                        SUM(hp.premium_amount * COUNT(be.id)) as total_revenue
                        FROM providers p
                        LEFT JOIN hmo_plans hp ON p.id = hp.provider_id
                        LEFT JOIN benefit_enrollments be ON hp.id = be.plan_id AND be.status = 'Active'
                        GROUP BY p.id
                        ORDER BY p.provider_name");
    $providers = $stmt->fetchAll();

    // Benefit enrollments with employee details
    $stmt = $pdo->query("SELECT be.*, e.first_name, e.last_name, e.employee_number, d.department_name, hp.plan_name, p.provider_name
                        FROM benefit_enrollments be
                        JOIN employees e ON be.employee_id = e.id
                        LEFT JOIN departments d ON e.department_id = d.id
                        JOIN hmo_plans hp ON be.plan_id = hp.id
                        JOIN providers p ON hp.provider_id = p.id
                        ORDER BY be.enrollment_date DESC
                        LIMIT 20");
    $recentEnrollments = $stmt->fetchAll();

    // Detailed benefit claims with processing information
    $stmt = $pdo->query("SELECT bc.*, e.first_name, e.last_name, e.employee_number, d.department_name, 
                        hp.plan_name, p.provider_name, p.contact_info,
                        DATEDIFF(CURDATE(), bc.claim_date) as days_pending
                        FROM benefit_claims bc
                        JOIN employees e ON bc.employee_id = e.id
                        LEFT JOIN departments d ON e.department_id = d.id
                        JOIN hmo_plans hp ON bc.plan_id = hp.id
                        JOIN providers p ON hp.provider_id = p.id
                        ORDER BY bc.claim_date DESC
                        LIMIT 30");
    $recentClaims = $stmt->fetchAll();

    // Claims by status
    $claimsByStatus = $pdo->query("SELECT status, COUNT(*) as count, SUM(claim_amount) as total_amount
                                  FROM benefit_claims 
                                  GROUP BY status")->fetchAll();

    // Provider performance metrics
    $providerMetrics = $pdo->query("SELECT p.provider_name, 
                                   COUNT(bc.id) as total_claims,
                                   AVG(bc.claim_amount) as avg_claim_amount,
                                   SUM(CASE WHEN bc.status = 'Approved' THEN 1 ELSE 0 END) as approved_claims,
                                   SUM(CASE WHEN bc.status = 'Pending' THEN 1 ELSE 0 END) as pending_claims,
                                   AVG(CASE WHEN bc.status = 'Approved' THEN DATEDIFF(bc.processed_date, bc.claim_date) ELSE NULL END) as avg_processing_days
                                   FROM providers p
                                   JOIN hmo_plans hp ON p.id = hp.provider_id
                                   JOIN benefit_claims bc ON hp.id = bc.plan_id
                                   GROUP BY p.id
                                   ORDER BY total_claims DESC")->fetchAll();

    // Benefits statistics
    $totalPlans = $pdo->query("SELECT COUNT(*) FROM hmo_plans")->fetchColumn();
    $activeEnrollments = $pdo->query("SELECT COUNT(*) FROM benefit_enrollments WHERE status = 'Active'")->fetchColumn();
    $totalClaims = $pdo->query("SELECT COUNT(*) FROM benefit_claims")->fetchColumn();
    $pendingClaims = $pdo->query("SELECT COUNT(*) FROM benefit_claims WHERE status = 'Pending'")->fetchColumn();

    // Total benefits cost
    $totalBenefitsCost = $pdo->query("SELECT SUM(hp.premium_amount * COUNT(be.id)) FROM hmo_plans hp 
                                     LEFT JOIN benefit_enrollments be ON hp.id = be.plan_id AND be.status = 'Active'
                                     GROUP BY hp.id")->fetchColumn();

    // Claims processing statistics
    $totalClaimAmount = $pdo->query("SELECT SUM(claim_amount) FROM benefit_claims")->fetchColumn();
    $approvedClaimAmount = $pdo->query("SELECT SUM(claim_amount) FROM benefit_claims WHERE status = 'Approved'")->fetchColumn();
    $pendingClaimAmount = $pdo->query("SELECT SUM(claim_amount) FROM benefit_claims WHERE status = 'Pending'")->fetchColumn();

} catch (PDOException $e) {
    $hmoPlans = [];
    $providers = [];
    $recentEnrollments = [];
    $recentClaims = [];
    $claimsByStatus = [];
    $providerMetrics = [];
    $totalPlans = $activeEnrollments = $totalClaims = $pendingClaims = $totalBenefitsCost = 0;
    $totalClaimAmount = $approvedClaimAmount = $pendingClaimAmount = 0;
}
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
                            <button onclick="openEnrollmentModal()"
                                class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                New Enrollment
                            </button>
                            <button onclick="openProviderModal()"
                                class="bg-orange-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Add Provider
                            </button>
                            <button onclick="openProviderManagement()"
                                class="bg-blue-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Provider Mgmt
                            </button>
                            <button onclick="openClaimsAnalytics()"
                                class="bg-indigo-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Analytics
                            </button>
                            <button onclick="exportBenefits()"
                                class="bg-slate-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Export Data
                            </button>
                        </div>

                        <!-- Benefits Statistics -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">HMO Plans</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($totalPlans); ?></div>
                                <div class="text-xs text-blue-600 mt-1">Available plans</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Active Enrollments</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($activeEnrollments); ?>
                                </div>
                                <div class="text-xs text-green-600 mt-1">Enrolled employees</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Total Claims</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($totalClaims); ?></div>
                                <div class="text-xs text-purple-600 mt-1">All time</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Pending Claims</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($pendingClaims); ?></div>
                                <div class="text-xs text-orange-600 mt-1">Awaiting review</div>
                            </div>
                        </div>

                        <!-- Provider Management -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Provider Management</div>
                                    <div class="text-sm text-slate-500"><?php echo count($providers); ?> providers</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Provider</th>
                                            <th class="text-left px-3 py-2 font-semibold">Plans</th>
                                            <th class="text-left px-3 py-2 font-semibold">Enrollments</th>
                                            <th class="text-left px-3 py-2 font-semibold">Revenue</th>
                                            <th class="text-left px-3 py-2 font-semibold">Contact</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($providers as $provider): ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($provider['provider_name']); ?></div>
                                                    <div class="text-xs text-gray-500">
                                                        <?php echo htmlspecialchars($provider['provider_type']); ?></div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                        <?php echo $provider['plan_count']; ?> plans
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo number_format($provider['total_enrollments']); ?></div>
                                                    <div class="text-xs text-gray-500">active</div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        ₱<?php echo number_format($provider['total_revenue'] ?? 0, 0); ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">monthly</div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="text-xs">
                                                        <?php echo htmlspecialchars(substr($provider['contact_info'], 0, 40)) . '...'; ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex gap-1">
                                                        <button onclick="editProvider(<?php echo $provider['id']; ?>)"
                                                            class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                                                        <button
                                                            onclick="viewProviderDetails(<?php echo $provider['id']; ?>)"
                                                            class="text-green-600 hover:text-green-800 text-xs">Details</button>
                                                        <button
                                                            onclick="viewProviderPerformance(<?php echo $provider['id']; ?>)"
                                                            class="text-purple-600 hover:text-purple-800 text-xs">Performance</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Claims Processing Dashboard -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Claims Processing Dashboard</div>
                                    <div class="text-sm text-slate-500">Real-time processing status</div>
                                </div>
                            </div>
                            <div class="p-4">
                                <!-- Claims Status Overview -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                    <?php foreach ($claimsByStatus as $status): ?>
                                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo ucfirst($status['status']); ?></div>
                                                    <div class="text-2xl font-bold text-blue-600">
                                                        <?php echo number_format($status['count']); ?></div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-sm text-gray-500">Total Amount</div>
                                                    <div class="text-lg font-semibold">
                                                        ₱<?php echo number_format($status['total_amount'], 0); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Provider Performance Metrics -->
                                <div class="mb-6">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-3">Provider Performance</h4>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-900">Provider
                                                    </th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-900">Total
                                                        Claims</th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-900">Approved
                                                    </th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-900">Pending
                                                    </th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-900">Avg
                                                        Processing Days</th>
                                                    <th class="px-3 py-2 text-left font-medium text-gray-900">Avg Claim
                                                        Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                <?php foreach ($providerMetrics as $metric): ?>
                                                    <tr>
                                                        <td class="px-3 py-2 font-medium">
                                                            <?php echo htmlspecialchars($metric['provider_name']); ?></td>
                                                        <td class="px-3 py-2">
                                                            <?php echo number_format($metric['total_claims']); ?></td>
                                                        <td class="px-3 py-2">
                                                            <span
                                                                class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                                                <?php echo number_format($metric['approved_claims']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <span
                                                                class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">
                                                                <?php echo number_format($metric['pending_claims']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <?php echo $metric['avg_processing_days'] ? number_format($metric['avg_processing_days'], 1) . ' days' : 'N/A'; ?>
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            ₱<?php echo number_format($metric['avg_claim_amount'], 0); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- HMO Plans -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">HMO Plans</div>
                                    <div class="text-sm text-slate-500"><?php echo count($hmoPlans); ?> plans</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Plan Name</th>
                                            <th class="text-left px-3 py-2 font-semibold">Provider</th>
                                            <th class="text-left px-3 py-2 font-semibold">Premium</th>
                                            <th class="text-left px-3 py-2 font-semibold">Enrollments</th>
                                            <th class="text-left px-3 py-2 font-semibold">Coverage</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($hmoPlans as $plan): ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($plan['plan_name']); ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($plan['provider_name']); ?>
                                                    </div>
                                                    <div class="text-xs text-slate-500">
                                                        <?php echo htmlspecialchars(substr($plan['contact_info'], 0, 30)) . '...'; ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="font-semibold">
                                                        ₱<?php echo number_format($plan['premium_amount'], 2); ?></div>
                                                    <div class="text-xs text-slate-500">per month</div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                        <?php echo $plan['enrollment_count']; ?> enrolled
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="text-xs text-slate-600">
                                                        <?php echo htmlspecialchars(substr($plan['coverage_details'], 0, 50)) . '...'; ?>
                                                    </div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="flex gap-1">
                                                        <button onclick="editPlan(<?php echo $plan['id']; ?>)"
                                                            class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                                                        <button onclick="viewPlanDetails(<?php echo $plan['id']; ?>)"
                                                            class="text-green-600 hover:text-green-800 text-xs">View</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Recent Enrollments -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Recent Enrollments</div>
                                    <div class="text-sm text-slate-500">Last 20 enrollments</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Department</th>
                                            <th class="text-left px-3 py-2 font-semibold">Plan</th>
                                            <th class="text-left px-3 py-2 font-semibold">Provider</th>
                                            <th class="text-left px-3 py-2 font-semibold">Enrollment Date</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentEnrollments)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="6">
                                                    <div
                                                        class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
                                                        <div class="text-sm font-medium">No enrollments</div>
                                                        <div class="text-xs text-slate-500 mt-1">Employees can enroll in
                                                            benefit plans.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recentEnrollments as $enrollment): ?>
                                                <tr
                                                    class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                    <td class="px-3 py-3">
                                                        <div>
                                                            <div class="font-medium">
                                                                <?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?>
                                                            </div>
                                                            <div class="text-xs text-slate-500">
                                                                <?php echo htmlspecialchars($enrollment['employee_number']); ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php echo htmlspecialchars($enrollment['department_name'] ?? 'N/A'); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php echo htmlspecialchars($enrollment['plan_name']); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php echo htmlspecialchars($enrollment['provider_name']); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php echo date('M j, Y', strtotime($enrollment['enrollment_date'])); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span class="px-2 py-1 text-xs rounded-full <?php
                                                        echo $enrollment['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                                                        ?>">
                                                            <?php echo htmlspecialchars($enrollment['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Recent Claims -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Recent Claims</div>
                                    <div class="text-sm text-slate-500">Last 20 claims</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Employee</th>
                                            <th class="text-left px-3 py-2 font-semibold">Plan</th>
                                            <th class="text-left px-3 py-2 font-semibold">Provider</th>
                                            <th class="text-left px-3 py-2 font-semibold">Claim Amount</th>
                                            <th class="text-left px-3 py-2 font-semibold">Claim Date</th>
                                            <th class="text-left px-3 py-2 font-semibold">Days Pending</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentClaims)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="7">
                                                    <div
                                                        class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
                                                        <div class="text-sm font-medium">No claims</div>
                                                        <div class="text-xs text-slate-500 mt-1">Claims will appear here
                                                            when submitted.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recentClaims as $claim): ?>
                                                <tr
                                                    class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                    <td class="px-3 py-3">
                                                        <div>
                                                            <div class="font-medium">
                                                                <?php echo htmlspecialchars($claim['first_name'] . ' ' . $claim['last_name']); ?>
                                                            </div>
                                                            <div class="text-xs text-slate-500">
                                                                <?php echo htmlspecialchars($claim['employee_number']); ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3"><?php echo htmlspecialchars($claim['plan_name']); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php echo htmlspecialchars($claim['provider_name']); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="font-semibold">
                                                            ₱<?php echo number_format($claim['claim_amount'], 2); ?></div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php echo date('M j, Y', strtotime($claim['claim_date'])); ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php if ($claim['status'] === 'Pending'): ?>
                                                            <span
                                                                class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded-full">
                                                                <?php echo $claim['days_pending']; ?> days
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-xs text-gray-500">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span class="px-2 py-1 text-xs rounded-full <?php
                                                        echo $claim['status'] === 'Paid' ? 'bg-green-100 text-green-800' :
                                                            ($claim['status'] === 'Approved' ? 'bg-blue-100 text-blue-800' :
                                                                ($claim['status'] === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'));
                                                        ?>">
                                                            <?php echo htmlspecialchars($claim['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="flex gap-1">
                                                            <?php if ($claim['status'] === 'Pending'): ?>
                                                                <button onclick="approveClaim(<?php echo $claim['id']; ?>)"
                                                                    class="text-green-600 hover:text-green-800 text-xs">Approve</button>
                                                                <button onclick="rejectClaim(<?php echo $claim['id']; ?>)"
                                                                    class="text-red-600 hover:text-red-800 text-xs">Reject</button>
                                                            <?php endif; ?>
                                                            <button onclick="viewClaim(<?php echo $claim['id']; ?>)"
                                                                class="text-blue-600 hover:text-blue-800 text-xs">View</button>
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

    <!-- New Enrollment Modal -->
    <div id="enrollmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">New Benefit Enrollment</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add_enrollment">
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
                            <label class="block text-sm font-medium mb-1">HMO Plan</label>
                            <select name="plan_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Plan</option>
                                <?php foreach ($hmoPlans as $plan): ?>
                                    <option value="<?php echo $plan['id']; ?>">
                                        <?php echo htmlspecialchars($plan['plan_name'] . ' - ' . $plan['provider_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Enrollment Date</label>
                            <input type="date" name="enrollment_date" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Enroll
                            Employee</button>
                        <button type="button" onclick="closeEnrollmentModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        function openEnrollmentModal() {
            document.getElementById('enrollmentModal').classList.remove('hidden');
        }

        function closeEnrollmentModal() {
            document.getElementById('enrollmentModal').classList.add('hidden');
        }

        function openProviderModal() {
            alert('Add provider functionality coming soon');
        }

        function editPlan(id) {
            alert('Edit plan ' + id);
        }

        function viewPlanDetails(id) {
            alert('View plan details ' + id);
        }

        function approveClaim(id) {
            if (confirm('Are you sure you want to approve this claim?')) {
                // Submit form to approve claim
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_claim_status">
                    <input type="hidden" name="claim_id" value="${id}">
                    <input type="hidden" name="status" value="Approved">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function rejectClaim(id) {
            if (confirm('Are you sure you want to reject this claim?')) {
                // Submit form to reject claim
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_claim_status">
                    <input type="hidden" name="claim_id" value="${id}">
                    <input type="hidden" name="status" value="Rejected">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function viewClaim(id) {
            alert('View claim ' + id);
        }

        function exportBenefits() {
            alert('Export benefits functionality coming soon');
        }

        // Provider Management Functions
        function editProvider(id) {
            alert('Edit provider ' + id + ' - This would open a form to edit provider details');
        }

        function viewProviderDetails(id) {
            alert('Provider details ' + id + ' - This would show detailed provider information and contact details');
        }

        function viewProviderPerformance(id) {
            alert('Provider performance ' + id + ' - This would show performance metrics and claim processing statistics');
        }

        // Enhanced Claims Processing Functions
        function processClaim(claimId) {
            if (confirm('Process this claim?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_claim_status">
                    <input type="hidden" name="claim_id" value="${claimId}">
                    <input type="hidden" name="status" value="Approved">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function bulkProcessClaims() {
            alert('Bulk claims processing - Select multiple claims to process at once');
        }

        function generateClaimsReport() {
            alert('Generate claims report - Create detailed reports on claims processing and provider performance');
        }

        function openProviderManagement() {
            alert('Provider management - Add new providers, manage contracts, and track performance');
        }

        function openClaimsAnalytics() {
            alert('Claims analytics - Analyze claims trends, processing times, and provider performance');
        }
    </script>
</body>

</html>