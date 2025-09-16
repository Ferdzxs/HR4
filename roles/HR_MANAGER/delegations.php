<?php
// HR Manager Delegations Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../routing/rbac.php';
include_once __DIR__ . '/../../config/database.php';

$activeId = 'delegations';
$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_delegation':
                try {
                    // Create delegation record (we'll simulate this since we don't have a delegations table)
                    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action_type, table_affected, new_values) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $user['id'],
                        'DELEGATION_CREATED',
                        'delegations',
                        json_encode([
                            'delegate_from' => $_POST['delegate_from'],
                            'delegate_to' => $_POST['delegate_to'],
                            'delegation_type' => $_POST['delegation_type'],
                            'start_date' => $_POST['start_date'],
                            'end_date' => $_POST['end_date'],
                            'reason' => $_POST['reason'],
                            'status' => 'Active'
                        ])
                    ]);
                    $success = "Delegation created successfully!";
                } catch (PDOException $e) {
                    $error = "Error creating delegation: " . $e->getMessage();
                }
                break;

            case 'update_delegation_status':
                try {
                    $stmt = $pdo->prepare("UPDATE audit_logs SET new_values = JSON_SET(new_values, '$.status', ?) WHERE id = ?");
                    $stmt->execute([$_POST['status'], $_POST['delegation_id']]);
                    $success = "Delegation status updated successfully!";
                } catch (PDOException $e) {
                    $error = "Error updating delegation: " . $e->getMessage();
                }
                break;
        }
    }
}

// Fetch delegation data (simulated from audit logs)
try {
    // Get delegations from audit logs
    $stmt = $pdo->query("SELECT al.*, u1.username as from_user, u2.username as to_user
                        FROM audit_logs al
                        LEFT JOIN users u1 ON JSON_EXTRACT(al.new_values, '$.delegate_from') = u1.id
                        LEFT JOIN users u2 ON JSON_EXTRACT(al.new_values, '$.delegate_to') = u2.id
                        WHERE al.action_type = 'DELEGATION_CREATED'
                        ORDER BY al.timestamp DESC");
    $delegations = $stmt->fetchAll();

    // Get all employees for delegation selection
    $stmt = $pdo->query("SELECT e.id, CONCAT(e.first_name, ' ', e.last_name) as full_name, e.employee_number, d.department_name
                        FROM employees e
                        LEFT JOIN departments d ON e.department_id = d.id
                        WHERE e.status = 'Active'
                        ORDER BY e.first_name, e.last_name");
    $employees = $stmt->fetchAll();

    // Delegation statistics
    $activeDelegations = 0;
    $expiredDelegations = 0;
    $pendingDelegations = 0;

    foreach ($delegations as $delegation) {
        $data = json_decode($delegation['new_values'], true);
        $status = $data['status'] ?? 'Active';
        $endDate = $data['end_date'] ?? null;

        if ($status === 'Active') {
            if ($endDate && strtotime($endDate) < time()) {
                $expiredDelegations++;
            } else {
                $activeDelegations++;
            }
        } else {
            $pendingDelegations++;
        }
    }

    // Approval chain data (simulated)
    $approvalChains = $pdo->query("SELECT 
                                   al.id, al.action_type, al.table_affected, al.new_values, al.timestamp,
                                   u.username, e.first_name, e.last_name, e.employee_number, d.department_name
                                   FROM audit_logs al
                                   LEFT JOIN users u ON al.user_id = u.id
                                   LEFT JOIN employees e ON u.employee_id = e.id
                                   LEFT JOIN departments d ON e.department_id = d.id
                                   WHERE al.action_type IN ('APPROVAL_REQUEST', 'APPROVAL_GRANTED', 'APPROVAL_DENIED', 'DELEGATION_CREATED')
                                   ORDER BY al.timestamp DESC
                                   LIMIT 50")->fetchAll();

    // Delegation workflow steps (simulated)
    $workflowSteps = [
        ['step' => 1, 'name' => 'Request Created', 'description' => 'Delegation request submitted', 'status' => 'completed'],
        ['step' => 2, 'name' => 'Manager Review', 'description' => 'Direct manager reviews request', 'status' => 'in_progress'],
        ['step' => 3, 'name' => 'HR Approval', 'description' => 'HR department approves delegation', 'status' => 'pending'],
        ['step' => 4, 'name' => 'System Activation', 'description' => 'Delegation activated in system', 'status' => 'pending'],
        ['step' => 5, 'name' => 'Notification Sent', 'description' => 'All parties notified of delegation', 'status' => 'pending']
    ];

    // Department hierarchy for approval chains
    $departmentHierarchy = $pdo->query("SELECT d.id, d.department_name, d.parent_department_id, 
                                       CONCAT(e.first_name, ' ', e.last_name) as head_name,
                                       e.employee_number as head_employee_number
                                       FROM departments d
                                       LEFT JOIN employees e ON d.department_head_id = e.id
                                       ORDER BY d.department_name")->fetchAll();

    // Recent delegation activities
    $recentActivities = $pdo->query("SELECT al.*, u.username, e.first_name, e.last_name
                                    FROM audit_logs al
                                    LEFT JOIN users u ON al.user_id = u.id
                                    LEFT JOIN employees e ON u.employee_id = e.id
                                    WHERE al.action_type LIKE '%DELEGATION%' OR al.action_type LIKE '%APPROVAL%'
                                    ORDER BY al.timestamp DESC
                                    LIMIT 20")->fetchAll();

} catch (PDOException $e) {
    $delegations = [];
    $employees = [];
    $activeDelegations = $expiredDelegations = $pendingDelegations = 0;
    $approvalChains = [];
    $workflowSteps = [];
    $departmentHierarchy = [];
    $recentActivities = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Delegations</title>
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
                            <h1 class="text-lg font-semibold">Delegations</h1>
                            <p class="text-xs text-slate-500 mt-1">Temporary roles, approval chains, tracking</p>
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
                            <button onclick="openCreateModal()"
                                class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Create Delegation
                            </button>
                            <button onclick="bulkDelegation()"
                                class="bg-orange-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Bulk Delegation
                            </button>
                            <button onclick="exportDelegations()"
                                class="bg-slate-600 text-white shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">
                                Export Data
                            </button>
                        </div>

                        <!-- Delegation Statistics -->
                        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Active Delegations</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($activeDelegations); ?>
                                </div>
                                <div class="text-xs text-green-600 mt-1">Currently active</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Expired</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($expiredDelegations); ?>
                                </div>
                                <div class="text-xs text-orange-600 mt-1">Past due</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Pending</div>
                                <div class="text-2xl font-semibold"><?php echo number_format($pendingDelegations); ?>
                                </div>
                                <div class="text-xs text-blue-600 mt-1">Awaiting approval</div>
                            </div>
                            <div class="rounded-lg border border-[hsl(var(--border))] bg-[hsl(var(--card))] p-4">
                                <div class="text-xs text-slate-500 mb-1">Total Delegations</div>
                                <div class="text-2xl font-semibold"><?php echo number_format(count($delegations)); ?>
                                </div>
                                <div class="text-xs text-purple-600 mt-1">All time</div>
                            </div>
                        </div>

                        <!-- Approval Chain Visualization -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Approval Chain Visualization</div>
                                    <div class="text-sm text-slate-500">Department hierarchy and approval flow</div>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="space-y-4">
                                    <?php foreach ($departmentHierarchy as $dept): ?>
                                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <div
                                                        class="w-10 h-10 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-sm font-medium">
                                                        <?php echo substr($dept['department_name'], 0, 2); ?>
                                                    </div>
                                                    <div>
                                                        <div class="font-medium">
                                                            <?php echo htmlspecialchars($dept['department_name']); ?></div>
                                                        <div class="text-xs text-gray-500">
                                                            <?php echo $dept['head_name'] ? 'Head: ' . htmlspecialchars($dept['head_name']) : 'No head assigned'; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex space-x-2">
                                                    <button onclick="viewApprovalChain(<?php echo $dept['id']; ?>)"
                                                        class="text-blue-600 hover:text-blue-800 text-xs px-2 py-1 rounded hover:bg-blue-50">
                                                        View Chain
                                                    </button>
                                                    <button onclick="editApprovalChain(<?php echo $dept['id']; ?>)"
                                                        class="text-green-600 hover:text-green-800 text-xs px-2 py-1 rounded hover:bg-green-50">
                                                        Edit
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Delegation Workflow Steps -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Delegation Workflow Steps</div>
                                    <div class="text-sm text-slate-500">Current delegation process status</div>
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="space-y-4">
                                    <?php foreach ($workflowSteps as $step): ?>
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                <?php if ($step['status'] === 'completed'): ?>
                                                    <div
                                                        class="w-8 h-8 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-sm font-medium">
                                                        ✓
                                                    </div>
                                                <?php elseif ($step['status'] === 'in_progress'): ?>
                                                    <div
                                                        class="w-8 h-8 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-sm font-medium">
                                                        <div
                                                            class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-800">
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div
                                                        class="w-8 h-8 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center text-sm font-medium">
                                                        <?php echo $step['step']; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-1">
                                                <div class="font-medium"><?php echo htmlspecialchars($step['name']); ?>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    <?php echo htmlspecialchars($step['description']); ?></div>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <span class="px-2 py-1 text-xs rounded-full <?php
                                                echo $step['status'] === 'completed' ? 'bg-green-100 text-green-800' :
                                                    ($step['status'] === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800');
                                                ?>">
                                                    <?php echo ucfirst($step['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Delegation Activities -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="font-semibold">Recent Delegation Activities</div>
                                    <div class="text-sm text-slate-500">Last 20 activities</div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">User</th>
                                            <th class="text-left px-3 py-2 font-semibold">Action</th>
                                            <th class="text-left px-3 py-2 font-semibold">Details</th>
                                            <th class="text-left px-3 py-2 font-semibold">Timestamp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentActivities as $activity): ?>
                                            <tr
                                                class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                <td class="px-3 py-3">
                                                    <div class="font-medium">
                                                        <?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        <?php echo htmlspecialchars($activity['username']); ?></div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <span class="px-2 py-1 text-xs rounded-full <?php
                                                    echo $activity['action_type'] === 'DELEGATION_CREATED' ? 'bg-green-100 text-green-800' :
                                                        ($activity['action_type'] === 'APPROVAL_GRANTED' ? 'bg-blue-100 text-blue-800' :
                                                            ($activity['action_type'] === 'APPROVAL_DENIED' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'));
                                                    ?>">
                                                        <?php echo htmlspecialchars($activity['action_type']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="text-xs">
                                                        <?php echo htmlspecialchars($activity['table_affected']); ?></div>
                                                </td>
                                                <td class="px-3 py-3">
                                                    <div class="text-xs">
                                                        <?php echo date('M j, Y H:i', strtotime($activity['timestamp'])); ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Delegations List -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                        <select
                                            class="px-3 py-1 text-sm border border-[hsl(var(--border))] rounded-md focus:outline-none focus:ring-2 focus:ring-[hsl(var(--ring))]"
                                            id="statusFilter">
                                            <option value="">All Status</option>
                                            <option value="Active">Active</option>
                                            <option value="Expired">Expired</option>
                                            <option value="Pending">Pending</option>
                                        </select>
                                    </div>
                                    <div class="text-sm text-slate-500">
                                        <?php echo count($delegations); ?> delegations
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Delegation</th>
                                            <th class="text-left px-3 py-2 font-semibold">From</th>
                                            <th class="text-left px-3 py-2 font-semibold">To</th>
                                            <th class="text-left px-3 py-2 font-semibold">Type</th>
                                            <th class="text-left px-3 py-2 font-semibold">Period</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($delegations)): ?>
                                            <tr>
                                                <td class="px-3 py-6 text-center text-slate-500" colspan="7">
                                                    <div
                                                        class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
                                                        <div class="text-sm font-medium">No delegations</div>
                                                        <div class="text-xs text-slate-500 mt-1">Create delegations to
                                                            assign temporary roles.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($delegations as $delegation): ?>
                                                <?php
                                                $data = json_decode($delegation['new_values'], true);
                                                $status = $data['status'] ?? 'Active';
                                                $endDate = $data['end_date'] ?? null;
                                                $isExpired = $endDate && strtotime($endDate) < time();
                                                $displayStatus = $isExpired ? 'Expired' : $status;
                                                ?>
                                                <tr
                                                    class="border-b border-[hsl(var(--border))] hover:bg-[hsl(var(--secondary))]">
                                                    <td class="px-3 py-3">
                                                        <div>
                                                            <div class="font-medium">
                                                                <?php echo htmlspecialchars($data['delegation_type'] ?? 'N/A'); ?>
                                                            </div>
                                                            <div class="text-xs text-slate-500">
                                                                <?php echo htmlspecialchars($data['reason'] ?? 'No reason provided'); ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php
                                                        $fromEmployee = array_filter($employees, function ($emp) use ($data) {
                                                            return $emp['id'] == $data['delegate_from'];
                                                        });
                                                        $fromEmployee = reset($fromEmployee);
                                                        echo $fromEmployee ? htmlspecialchars($fromEmployee['full_name']) : 'Unknown';
                                                        ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <?php
                                                        $toEmployee = array_filter($employees, function ($emp) use ($data) {
                                                            return $emp['id'] == $data['delegate_to'];
                                                        });
                                                        $toEmployee = reset($toEmployee);
                                                        echo $toEmployee ? htmlspecialchars($toEmployee['full_name']) : 'Unknown';
                                                        ?>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                            <?php echo htmlspecialchars($data['delegation_type'] ?? 'N/A'); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="text-sm">
                                                            <?php echo date('M j', strtotime($data['start_date'] ?? '')); ?> -
                                                            <?php echo date('M j, Y', strtotime($data['end_date'] ?? '')); ?>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <span class="px-2 py-1 text-xs rounded-full <?php
                                                        echo $displayStatus === 'Active' ? 'bg-green-100 text-green-800' :
                                                            ($displayStatus === 'Expired' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
                                                        ?>">
                                                            <?php echo htmlspecialchars($displayStatus); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="flex gap-1">
                                                            <button onclick="viewDelegation(<?php echo $delegation['id']; ?>)"
                                                                class="text-blue-600 hover:text-blue-800 text-xs">View</button>
                                                            <?php if ($displayStatus === 'Active'): ?>
                                                                <button onclick="endDelegation(<?php echo $delegation['id']; ?>)"
                                                                    class="text-red-600 hover:text-red-800 text-xs">End</button>
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

                        <!-- Approval Chains -->
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="font-semibold">Approval Chains</div>
                            </div>
                            <div class="p-4">
                                <div
                                    class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
                                    <div class="text-sm font-medium">Approval chains visualization</div>
                                    <div class="text-xs text-slate-500 mt-1">Visual representation of delegation
                                        hierarchies coming soon.</div>
                                </div>
                            </div>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>

    <!-- Create Delegation Modal -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Create Delegation</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="create_delegation">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Delegate From</label>
                            <select name="delegate_from" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>">
                                        <?php echo htmlspecialchars($emp['full_name'] . ' (' . $emp['employee_number'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Delegate To</label>
                            <select name="delegate_to" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Employee</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp['id']; ?>">
                                        <?php echo htmlspecialchars($emp['full_name'] . ' (' . $emp['employee_number'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Delegation Type</label>
                            <select name="delegation_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Type</option>
                                <option value="Approval Authority">Approval Authority</option>
                                <option value="Department Head">Department Head</option>
                                <option value="Payroll Processing">Payroll Processing</option>
                                <option value="Benefits Administration">Benefits Administration</option>
                                <option value="Employee Management">Employee Management</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Start Date</label>
                            <input type="date" name="start_date" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">End Date</label>
                            <input type="date" name="end_date" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Reason</label>
                            <textarea name="reason" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Reason for delegation..."></textarea>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-6">
                        <button type="submit"
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Create
                            Delegation</button>
                        <button type="button" onclick="closeCreateModal()"
                            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
    <script>
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        function viewDelegation(id) {
            alert('View delegation ' + id);
        }

        function endDelegation(id) {
            if (confirm('Are you sure you want to end this delegation?')) {
                // Submit form to end delegation
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_delegation_status">
                    <input type="hidden" name="delegation_id" value="${id}">
                    <input type="hidden" name="status" value="Ended">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function bulkDelegation() {
            alert('Bulk delegation functionality coming soon');
        }

        function exportDelegations() {
            // Export delegations data to CSV
            const table = document.getElementById('delegationsTable');
            const rows = Array.from(table.querySelectorAll('tr'));
            const csvContent = rows.map(row => {
                const cells = Array.from(row.querySelectorAll('td, th'));
                return cells.map(cell => cell.textContent.trim()).join(',');
            }).join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'delegations.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Approval Chain Management
        function viewApprovalChain(departmentId) {
            // Open modal to view approval chain for department
            alert('Viewing approval chain for department ID: ' + departmentId);
        }

        function editApprovalChain(departmentId) {
            // Open modal to edit approval chain for department
            alert('Editing approval chain for department ID: ' + departmentId);
        }

        // Workflow Management
        function updateWorkflowStep(stepId, status) {
            // Update workflow step status
            alert('Updating workflow step ' + stepId + ' to ' + status);
        }

        function viewWorkflowHistory() {
            // Open modal to view workflow history
            alert('Viewing workflow history');
        }

        // Activity Management
        function viewActivityDetails(activityId) {
            // Open modal to view activity details
            alert('Viewing activity details for ID: ' + activityId);
        }

        function filterActivities(status) {
            // Filter activities by status
            alert('Filtering activities by status: ' + status);
        }

        // Delegation Management
        function createDelegation() {
            // Open modal to create new delegation
            alert('Creating new delegation');
        }

        function editDelegation(delegationId) {
            // Open modal to edit delegation
            alert('Editing delegation ID: ' + delegationId);
        }

        function deleteDelegation(delegationId) {
            // Confirm and delete delegation
            if (confirm('Are you sure you want to delete this delegation?')) {
                alert('Deleting delegation ID: ' + delegationId);
            }
        }

        function viewDelegationDetails(delegationId) {
            // Open modal to view delegation details
            alert('Viewing delegation details for ID: ' + delegationId);
        }

        // Auto-refresh functionality
        function toggleAutoRefresh() {
            const button = document.getElementById('autoRefreshBtn');
            if (button.textContent.includes('Enable')) {
                button.textContent = 'Disable Auto Refresh';
                button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                button.classList.add('bg-red-600', 'hover:bg-red-700');
                // Start auto-refresh
                window.autoRefreshInterval = setInterval(() => {
                    location.reload();
                }, 30000); // Refresh every 30 seconds
            } else {
                button.textContent = 'Enable Auto Refresh';
                button.classList.remove('bg-red-600', 'hover:bg-red-700');
                button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                // Stop auto-refresh
                if (window.autoRefreshInterval) {
                    clearInterval(window.autoRefreshInterval);
                }
            }
        }

        // Status filter functionality
        document.getElementById('statusFilter').addEventListener('change', function () {
            const filterValue = this.value;
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                if (filterValue === '') {
                    row.style.display = '';
                } else {
                    const statusCell = row.querySelector('td:nth-child(6)');
                    const status = statusCell.textContent.trim();
                    row.style.display = status === filterValue ? '' : 'none';
                }
            });
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', function () {
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
    </script>
</body>

</html>