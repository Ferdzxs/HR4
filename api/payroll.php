<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$userInfo = $auth->getUserInfo();
$role = $userInfo['role_name'];

try {
    switch ($method) {
        case 'GET':
            // Get payroll periods
            $stmt = $pdo->query("
                SELECT 
                    pp.id,
                    pp.start_date,
                    pp.end_date,
                    pp.pay_date,
                    pp.period_name,
                    pp.status,
                    COUNT(pe.id) as entry_count,
                    SUM(pe.amount) as total_amount
                FROM payroll_periods pp
                LEFT JOIN payroll_entries pe ON pp.id = pe.payroll_period_id
                GROUP BY pp.id
                ORDER BY pp.start_date DESC
            ");

            $periods = $stmt->fetchAll();

            // If specific period requested
            if (isset($_GET['period_id'])) {
                $periodId = $_GET['period_id'];

                $stmt = $pdo->prepare("
                    SELECT 
                        pe.id,
                        pe.employee_id,
                        e.employee_number,
                        e.first_name,
                        e.last_name,
                        d.department_name,
                        p.position_title,
                        sc.component_name,
                        sc.component_type,
                        pe.amount,
                        pe.is_processed
                    FROM payroll_entries pe
                    JOIN employees e ON pe.employee_id = e.id
                    LEFT JOIN departments d ON e.department_id = d.id
                    LEFT JOIN positions p ON e.position_id = p.id
                    LEFT JOIN salary_components sc ON pe.salary_component_id = sc.id
                    WHERE pe.payroll_period_id = ?
                    ORDER BY e.last_name, e.first_name, sc.component_type, sc.component_name
                ");
                $stmt->execute([$periodId]);
                $entries = $stmt->fetchAll();

                echo json_encode([
                    'ok' => true,
                    'data' => [
                        'period' => $periods[0] ?? null,
                        'entries' => $entries
                    ]
                ]);
            } else {
                echo json_encode([
                    'ok' => true,
                    'data' => $periods
                ]);
            }
            break;

        case 'POST':
            // Create payroll period or entry
            if (!in_array($role, ['HR Manager', 'Payroll Administrator'])) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['period_name'])) {
                // Create new payroll period
                $stmt = $pdo->prepare("
                    INSERT INTO payroll_periods (start_date, end_date, pay_date, period_name, status)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $input['start_date'],
                    $input['end_date'],
                    $input['pay_date'],
                    $input['period_name'],
                    $input['status'] ?? 'Draft'
                ]);

                $periodId = $pdo->lastInsertId();

                echo json_encode([
                    'ok' => true,
                    'message' => 'Payroll period created successfully',
                    'period_id' => $periodId
                ]);
            } else {
                // Create payroll entry
                $stmt = $pdo->prepare("
                    INSERT INTO payroll_entries (employee_id, payroll_period_id, salary_component_id, amount, is_processed)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $input['employee_id'],
                    $input['payroll_period_id'],
                    $input['salary_component_id'],
                    $input['amount'],
                    $input['is_processed'] ?? 0
                ]);

                echo json_encode([
                    'ok' => true,
                    'message' => 'Payroll entry created successfully'
                ]);
            }
            break;

        case 'PUT':
            // Update payroll period or entry
            if (!in_array($role, ['HR Manager', 'Payroll Administrator'])) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['period_name'])) {
                // Update payroll period
                $stmt = $pdo->prepare("
                    UPDATE payroll_periods 
                    SET start_date = ?, end_date = ?, pay_date = ?, period_name = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $input['start_date'],
                    $input['end_date'],
                    $input['pay_date'],
                    $input['period_name'],
                    $input['status'],
                    $input['id']
                ]);

                echo json_encode([
                    'ok' => true,
                    'message' => 'Payroll period updated successfully'
                ]);
            } else {
                // Update payroll entry
                $stmt = $pdo->prepare("
                    UPDATE payroll_entries 
                    SET amount = ?, is_processed = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $input['amount'],
                    $input['is_processed'],
                    $input['id']
                ]);

                echo json_encode([
                    'ok' => true,
                    'message' => 'Payroll entry updated successfully'
                ]);
            }
            break;

        case 'DELETE':
            // Delete payroll period or entry
            if ($role !== 'HR Manager') {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            if (isset($_GET['period_id'])) {
                $periodId = $_GET['period_id'];

                // Check if period has processed entries
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM payroll_entries WHERE payroll_period_id = ? AND is_processed = 1");
                $stmt->execute([$periodId]);
                $processedCount = $stmt->fetch()['count'];

                if ($processedCount > 0) {
                    http_response_code(400);
                    echo json_encode([
                        'ok' => false,
                        'message' => 'Cannot delete period with processed entries'
                    ]);
                    exit;
                }

                $stmt = $pdo->prepare("DELETE FROM payroll_periods WHERE id = ?");
                $stmt->execute([$periodId]);

                echo json_encode([
                    'ok' => true,
                    'message' => 'Payroll period deleted successfully'
                ]);
            } else {
                $entryId = $_GET['id'];

                $stmt = $pdo->prepare("DELETE FROM payroll_entries WHERE id = ?");
                $stmt->execute([$entryId]);

                echo json_encode([
                    'ok' => true,
                    'message' => 'Payroll entry deleted successfully'
                ]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    }

} catch (Exception $e) {
    error_log("Payroll API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Internal server error']);
}
?>
