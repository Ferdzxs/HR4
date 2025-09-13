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
            // Get benefits data
            if (isset($_GET['type'])) {
                $type = $_GET['type'];

                switch ($type) {
                    case 'providers':
                        $stmt = $pdo->query("
                            SELECT 
                                p.id,
                                p.provider_name,
                                p.description,
                                p.status,
                                COUNT(hp.id) as plan_count
                            FROM providers p
                            LEFT JOIN hmo_plans hp ON p.id = hp.provider_id
                            GROUP BY p.id
                            ORDER BY p.provider_name
                        ");
                        $data = $stmt->fetchAll();
                        break;

                    case 'plans':
                        $stmt = $pdo->query("
                            SELECT 
                                hp.id,
                                hp.plan_name,
                                hp.description,
                                hp.monthly_premium,
                                hp.status,
                                p.provider_name
                            FROM hmo_plans hp
                            JOIN providers p ON hp.provider_id = p.id
                            ORDER BY p.provider_name, hp.plan_name
                        ");
                        $data = $stmt->fetchAll();
                        break;

                    case 'enrollments':
                        $stmt = $pdo->query("
                            SELECT 
                                be.id,
                                be.employee_id,
                                e.employee_number,
                                e.first_name,
                                e.last_name,
                                d.department_name,
                                hp.plan_name,
                                p.provider_name,
                                be.enrollment_date,
                                be.status
                            FROM benefit_enrollments be
                            JOIN employees e ON be.employee_id = e.id
                            LEFT JOIN departments d ON e.department_id = d.id
                            JOIN hmo_plans hp ON be.hmo_plan_id = hp.id
                            JOIN providers p ON hp.provider_id = p.id
                            ORDER BY be.enrollment_date DESC
                        ");
                        $data = $stmt->fetchAll();
                        break;

                    case 'claims':
                        $stmt = $pdo->query("
                            SELECT 
                                bc.id,
                                bc.employee_id,
                                e.employee_number,
                                e.first_name,
                                e.last_name,
                                bc.claim_type,
                                bc.claim_amount,
                                bc.claim_date,
                                bc.status,
                                bc.description
                            FROM benefit_claims bc
                            JOIN employees e ON bc.employee_id = e.id
                            ORDER BY bc.claim_date DESC
                        ");
                        $data = $stmt->fetchAll();
                        break;

                    default:
                        http_response_code(400);
                        echo json_encode(['ok' => false, 'message' => 'Invalid type parameter']);
                        exit;
                }

                echo json_encode([
                    'ok' => true,
                    'data' => $data
                ]);
            } else {
                // Get benefits summary
                $stmt = $pdo->query("
                    SELECT 
                        COUNT(DISTINCT p.id) as total_providers,
                        COUNT(DISTINCT hp.id) as total_plans,
                        COUNT(be.id) as total_enrollments,
                        COUNT(bc.id) as total_claims
                    FROM providers p
                    LEFT JOIN hmo_plans hp ON p.id = hp.provider_id
                    LEFT JOIN benefit_enrollments be ON hp.id = be.hmo_plan_id
                    LEFT JOIN benefit_claims bc ON be.employee_id = bc.employee_id
                ");

                $summary = $stmt->fetch();

                echo json_encode([
                    'ok' => true,
                    'data' => $summary
                ]);
            }
            break;

        case 'POST':
            // Create benefits data
            if (!in_array($role, ['HR Manager', 'Benefits Coordinator'])) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $type = $input['type'];

            switch ($type) {
                case 'provider':
                    $stmt = $pdo->prepare("
                        INSERT INTO providers (provider_name, description, status)
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([
                        $input['provider_name'],
                        $input['description'],
                        $input['status'] ?? 'Active'
                    ]);
                    break;

                case 'plan':
                    $stmt = $pdo->prepare("
                        INSERT INTO hmo_plans (provider_id, plan_name, description, monthly_premium, status)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $input['provider_id'],
                        $input['plan_name'],
                        $input['description'],
                        $input['monthly_premium'],
                        $input['status'] ?? 'Active'
                    ]);
                    break;

                case 'enrollment':
                    $stmt = $pdo->prepare("
                        INSERT INTO benefit_enrollments (employee_id, hmo_plan_id, enrollment_date, status)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $input['employee_id'],
                        $input['hmo_plan_id'],
                        $input['enrollment_date'],
                        $input['status'] ?? 'Active'
                    ]);
                    break;

                case 'claim':
                    $stmt = $pdo->prepare("
                        INSERT INTO benefit_claims (employee_id, claim_type, claim_amount, claim_date, status, description)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $input['employee_id'],
                        $input['claim_type'],
                        $input['claim_amount'],
                        $input['claim_date'],
                        $input['status'] ?? 'Pending',
                        $input['description']
                    ]);
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'message' => 'Invalid type parameter']);
                    exit;
            }

            echo json_encode([
                'ok' => true,
                'message' => ucfirst($type) . ' created successfully'
            ]);
            break;

        case 'PUT':
            // Update benefits data
            if (!in_array($role, ['HR Manager', 'Benefits Coordinator'])) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $type = $input['type'];
            $id = $input['id'];

            switch ($type) {
                case 'provider':
                    $stmt = $pdo->prepare("
                        UPDATE providers 
                        SET provider_name = ?, description = ?, status = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $input['provider_name'],
                        $input['description'],
                        $input['status'],
                        $id
                    ]);
                    break;

                case 'plan':
                    $stmt = $pdo->prepare("
                        UPDATE hmo_plans 
                        SET provider_id = ?, plan_name = ?, description = ?, monthly_premium = ?, status = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $input['provider_id'],
                        $input['plan_name'],
                        $input['description'],
                        $input['monthly_premium'],
                        $input['status'],
                        $id
                    ]);
                    break;

                case 'enrollment':
                    $stmt = $pdo->prepare("
                        UPDATE benefit_enrollments 
                        SET employee_id = ?, hmo_plan_id = ?, enrollment_date = ?, status = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $input['employee_id'],
                        $input['hmo_plan_id'],
                        $input['enrollment_date'],
                        $input['status'],
                        $id
                    ]);
                    break;

                case 'claim':
                    $stmt = $pdo->prepare("
                        UPDATE benefit_claims 
                        SET claim_type = ?, claim_amount = ?, claim_date = ?, status = ?, description = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $input['claim_type'],
                        $input['claim_amount'],
                        $input['claim_date'],
                        $input['status'],
                        $input['description'],
                        $id
                    ]);
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'message' => 'Invalid type parameter']);
                    exit;
            }

            echo json_encode([
                'ok' => true,
                'message' => ucfirst($type) . ' updated successfully'
            ]);
            break;

        case 'DELETE':
            // Delete benefits data
            if ($role !== 'HR Manager') {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $type = $_GET['type'];
            $id = $_GET['id'];

            switch ($type) {
                case 'provider':
                    // Check if provider has plans
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM hmo_plans WHERE provider_id = ?");
                    $stmt->execute([$id]);
                    $planCount = $stmt->fetch()['count'];

                    if ($planCount > 0) {
                        http_response_code(400);
                        echo json_encode([
                            'ok' => false,
                            'message' => 'Cannot delete provider with active plans'
                        ]);
                        exit;
                    }

                    $stmt = $pdo->prepare("DELETE FROM providers WHERE id = ?");
                    $stmt->execute([$id]);
                    break;

                case 'plan':
                    // Check if plan has enrollments
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM benefit_enrollments WHERE hmo_plan_id = ?");
                    $stmt->execute([$id]);
                    $enrollmentCount = $stmt->fetch()['count'];

                    if ($enrollmentCount > 0) {
                        http_response_code(400);
                        echo json_encode([
                            'ok' => false,
                            'message' => 'Cannot delete plan with active enrollments'
                        ]);
                        exit;
                    }

                    $stmt = $pdo->prepare("DELETE FROM hmo_plans WHERE id = ?");
                    $stmt->execute([$id]);
                    break;

                case 'enrollment':
                    $stmt = $pdo->prepare("DELETE FROM benefit_enrollments WHERE id = ?");
                    $stmt->execute([$id]);
                    break;

                case 'claim':
                    $stmt = $pdo->prepare("DELETE FROM benefit_claims WHERE id = ?");
                    $stmt->execute([$id]);
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['ok' => false, 'message' => 'Invalid type parameter']);
                    exit;
            }

            echo json_encode([
                'ok' => true,
                'message' => ucfirst($type) . ' deleted successfully'
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    }

} catch (Exception $e) {
    error_log("Benefits API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Internal server error']);
}
?>
