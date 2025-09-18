<?php
// Compensation Manager Merit Increases Page
include_once __DIR__ . '/../../shared/header.php';
include_once __DIR__ . '/../../shared/sidebar.php';
include_once __DIR__ . '/../../routing/rbac.php';



$activeId = 'merit';



$sidebarItems = $SIDEBAR_ITEMS[$user['role']] ?? [];

// Direct PDO helpers
function cm_get_db()
{
    static $pdo = null;
    if ($pdo)
        return $pdo;
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=hr4_compensation_intelli;charset=utf8', 'root', '54321', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (Throwable $e) {
        $pdo = null;
    }
    return $pdo;
}

function cm_fetch_all($sql, $params = [])
{
    $db = cm_get_db();
    if (!$db)
        return [];
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function cm_fetch_one($sql, $params = [])
{
    $db = cm_get_db();
    if (!$db)
        return null;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

// Filters: department, search, compa-ratio band, sorting, pagination
$deptId = isset($_GET['dept']) ? (int) $_GET['dept'] : 0;
$search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$minCR = isset($_GET['mincr']) ? max(0.0, (float) $_GET['mincr']) : 0.0;
$maxCR = isset($_GET['maxcr']) ? min(1000.0, (float) $_GET['maxcr']) : 1000.0;
if ($maxCR < $minCR) { $tmp = $minCR; $minCR = $maxCR; $maxCR = $tmp; }
$sort = isset($_GET['sort']) ? (string) $_GET['sort'] : 'employee';
$dir = strtoupper((string) ($_GET['dir'] ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = (int) ($_GET['per'] ?? 10);
if ($perPage <= 0 || $perPage > 100) $perPage = 10;
$offset = ($page - 1) * $perPage;

// Dropdown data
$departments = cm_fetch_all("SELECT id, department_name FROM departments ORDER BY department_name");

// Latest payroll per employee
$whereParts = ["e.status = 'Active'"];
$params = [];
if ($deptId > 0) { $whereParts[] = 'e.department_id = ?'; $params[] = $deptId; }
if ($search !== '') {
    $whereParts[] = '(e.employee_number LIKE ? OR e.first_name LIKE ? OR e.last_name LIKE ?)';
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
$whereSql = 'WHERE ' . implode(' AND ', $whereParts);

// Count total for pagination (compa-ratio filter applied after compute using HAVING)
$countSql = "SELECT COUNT(*) AS c
               FROM (
                 SELECT e.id,
                        ( (sg.min_salary + sg.max_salary)/2 ) AS midpoint,
                        COALESCE(pe_latest.net_pay, 0) AS net_pay,
                        (CASE WHEN ( (sg.min_salary + sg.max_salary)/2 ) > 0
                              THEN (COALESCE(pe_latest.net_pay,0) / ((sg.min_salary + sg.max_salary)/2))*100
                              ELSE 0 END) AS compa_ratio
                   FROM employees e
              LEFT JOIN positions p ON p.id = e.position_id
              LEFT JOIN salary_grades sg ON sg.id = p.salary_grade_id
              LEFT JOIN (
                    SELECT pe1.* FROM payroll_entries pe1
                    INNER JOIN (
                        SELECT employee_id, MAX(id) AS max_id FROM payroll_entries GROUP BY employee_id
                    ) t ON t.max_id = pe1.id
              ) pe_latest ON pe_latest.employee_id = e.id
                   $whereSql
               ) x
              WHERE x.compa_ratio BETWEEN ? AND ?";
$countRow = cm_fetch_one($countSql, array_merge($params, [$minCR, $maxCR]));
$totalCount = (int) ($countRow['c'] ?? 0);
$totalPages = max(1, (int) ceil($totalCount / $perPage));
if ($page > $totalPages) { $page = $totalPages; $offset = ($page-1)*$perPage; }

$sortable = [
    'employee' => 'e.first_name, e.last_name',
    'department' => 'd.department_name',
    'position' => 'p.position_title',
    'midpoint' => 'midpoint',
    'netpay' => 'net_pay',
    'cr' => 'compa_ratio'
];
$orderCol = $sortable[$sort] ?? 'e.first_name, e.last_name';

// Data query
$sql = "SELECT e.employee_number,
               CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
               d.department_name,
               p.position_title,
               sg.min_salary,
               sg.max_salary,
               ((sg.min_salary + sg.max_salary)/2) AS midpoint,
               COALESCE(pe_latest.net_pay, 0) AS net_pay,
               (CASE WHEN ((sg.min_salary + sg.max_salary)/2) > 0
                     THEN (COALESCE(pe_latest.net_pay,0) / ((sg.min_salary + sg.max_salary)/2))*100
                     ELSE 0 END) AS compa_ratio
          FROM employees e
     LEFT JOIN departments d ON d.id = e.department_id
     LEFT JOIN positions p ON p.id = e.position_id
     LEFT JOIN salary_grades sg ON sg.id = p.salary_grade_id
     LEFT JOIN (
            SELECT pe1.* FROM payroll_entries pe1
            INNER JOIN (
                SELECT employee_id, MAX(id) AS max_id FROM payroll_entries GROUP BY employee_id
            ) t ON t.max_id = pe1.id
     ) pe_latest ON pe_latest.employee_id = e.id
          $whereSql
     HAVING compa_ratio BETWEEN ? AND ?
     ORDER BY $orderCol $dir
     LIMIT $perPage OFFSET $offset";
$rows = cm_fetch_all($sql, array_merge($params, [$minCR, $maxCR]));

// CSV export of current view
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="merit_candidates.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Employee', 'Department', 'Position', 'Midpoint', 'Net Pay', 'Compa-Ratio']);
    foreach ($rows as $r) {
        fputcsv($out, [
            (string) ($r['employee_number'] . ' - ' . $r['employee_name']),
            (string) ($r['department_name'] ?? ''),
            (string) ($r['position_title'] ?? ''),
            number_format((float) $r['midpoint'], 2, '.', ''),
            number_format((float) $r['net_pay'], 2, '.', ''),
            number_format((float) $r['compa_ratio'], 2, '.', ''),
        ]);
    }
    fclose($out);
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR4 - Merit Increases</title>
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
                            <h1 class="text-lg font-semibold">Merit Increases</h1>
                            <p class="text-xs text-slate-500 mt-1">Review cycles, manager input, batch processing</p>
                        </div>
                        <div class="rounded-lg border border-[hsl(var(--border))] overflow-hidden">
                            <div class="p-3 border-b border-[hsl(var(--border))] bg-[hsl(var(--secondary))]">
                                <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                                    <div class="flex gap-2">
                                    </div>
                                    <div>
                                        <button
                                            class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">New
                                            Cycle</button>
                                    </div>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-[hsl(var(--secondary))]">
                                        <tr>
                                            <th class="text-left px-3 py-2 font-semibold">Cycle</th>
                                            <th class="text-left px-3 py-2 font-semibold">Dept</th>
                                            <th class="text-left px-3 py-2 font-semibold">Budget</th>
                                            <th class="text-left px-3 py-2 font-semibold">Status</th>
                                            <th class="text-left px-3 py-2 font-semibold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="px-3 py-6 text-center text-slate-500" colspan="5">
                                                <div
                                                    class="text-center py-10 border border-dashed border-[hsl(var(--border))] rounded-md">
                                                    <div class="text-sm font-medium">No merit cycles</div>
                                                    <div class="text-xs text-slate-500 mt-1">Create or open a review
                                                        cycle.</div>
                                                    <div class="mt-3">
                                                        <button
                                                            class="bg-[hsl(var(--primary))] text-[hsl(var(--primary-foreground))] shadow hover:opacity-95 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[hsl(var(--ring))] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3">New
                                                            Cycle</button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                </main>
            </div>
        </div>
    </div>
    <script src="/HR4_COMPEN&INTELLI/shared/scripts.js"></script>
</body>

</html>