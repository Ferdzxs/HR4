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
            // Get departments list
            $stmt = $pdo->query("
                SELECT 
                    d.id,
                    d.department_name,
                    d.budget_allocation,
                    d.created_at,
                    COUNT(e.id) as employee_count,
                    AVG(sg.min_salary) as avg_min_salary,
                    AVG(sg.max_salary) as avg_max_salary
                FROM departments d
                LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'Active'
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN salary_grades sg ON p.salary_grade_id = sg.id
                GROUP BY d.id, d.department_name, d.budget_allocation, d.created_at
                ORDER BY d.department_name
            ");

            $departments = $stmt->fetchAll();

            echo json_encode([
                'ok' => true,
                'data' => $departments
            ]);
            break;

        case 'POST':
            // Create new department
            if (!in_array($role, ['HR Manager', 'Hospital Management'])) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            $stmt = $pdo->prepare("
                INSERT INTO departments (department_name, budget_allocation)
                VALUES (?, ?)
            ");
            $stmt->execute([
                $input['department_name'],
                $input['budget_allocation']
            ]);

            $departmentId = $pdo->lastInsertId();

            echo json_encode([
                'ok' => true,
                'message' => 'Department created successfully',
                'department_id' => $departmentId
            ]);
            break;

        case 'PUT':
            // Update department
            if (!in_array($role, ['HR Manager', 'Hospital Management'])) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $departmentId = $input['id'];

            $stmt = $pdo->prepare("
                UPDATE departments 
                SET department_name = ?, budget_allocation = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $input['department_name'],
                $input['budget_allocation'],
                $departmentId
            ]);

            echo json_encode([
                'ok' => true,
                'message' => 'Department updated successfully'
            ]);
            break;

        case 'DELETE':
            // Delete department
            if ($role !== 'Hospital Management') {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $departmentId = $_GET['id'];

            // Check if department has employees
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM employees WHERE department_id = ? AND status = 'Active'");
            $stmt->execute([$departmentId]);
            $employeeCount = $stmt->fetch()['count'];

            if ($employeeCount > 0) {
                http_response_code(400);
                echo json_encode([
                    'ok' => false,
                    'message' => 'Cannot delete department with active employees'
                ]);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
            $stmt->execute([$departmentId]);

            echo json_encode([
                'ok' => true,
                'message' => 'Department deleted successfully'
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    }

} catch (Exception $e) {
    error_log("Departments API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Internal server error']);
}
?>
