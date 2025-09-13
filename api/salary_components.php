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
            // Get salary components
            $stmt = $pdo->query("
                SELECT 
                    sc.id,
                    sc.component_name,
                    sc.component_type,
                    sc.description,
                    sc.is_active,
                    COUNT(pe.id) as usage_count
                FROM salary_components sc
                LEFT JOIN payroll_entries pe ON sc.id = pe.salary_component_id
                GROUP BY sc.id
                ORDER BY sc.component_type, sc.component_name
            ");

            $components = $stmt->fetchAll();

            echo json_encode([
                'ok' => true,
                'data' => $components
            ]);
            break;

        case 'POST':
            // Create salary component
            if (!in_array($role, ['HR Manager', 'Payroll Administrator'])) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            $stmt = $pdo->prepare("
                INSERT INTO salary_components (component_name, component_type, description, is_active)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $input['component_name'],
                $input['component_type'],
                $input['description'],
                $input['is_active'] ?? 1
            ]);

            $componentId = $pdo->lastInsertId();

            echo json_encode([
                'ok' => true,
                'message' => 'Salary component created successfully',
                'component_id' => $componentId
            ]);
            break;

        case 'PUT':
            // Update salary component
            if (!in_array($role, ['HR Manager', 'Payroll Administrator'])) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $componentId = $input['id'];

            $stmt = $pdo->prepare("
                UPDATE salary_components 
                SET component_name = ?, component_type = ?, description = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $input['component_name'],
                $input['component_type'],
                $input['description'],
                $input['is_active'],
                $componentId
            ]);

            echo json_encode([
                'ok' => true,
                'message' => 'Salary component updated successfully'
            ]);
            break;

        case 'DELETE':
            // Delete salary component
            if ($role !== 'HR Manager') {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $componentId = $_GET['id'];

            // Check if component is used in payroll entries
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM payroll_entries WHERE salary_component_id = ?");
            $stmt->execute([$componentId]);
            $usageCount = $stmt->fetch()['count'];

            if ($usageCount > 0) {
                http_response_code(400);
                echo json_encode([
                    'ok' => false,
                    'message' => 'Cannot delete component that is used in payroll entries'
                ]);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM salary_components WHERE id = ?");
            $stmt->execute([$componentId]);

            echo json_encode([
                'ok' => true,
                'message' => 'Salary component deleted successfully'
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    }

} catch (Exception $e) {
    error_log("Salary Components API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Internal server error']);
}
?>
