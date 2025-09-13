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
            // Get positions with salary grade information
            $stmt = $pdo->query("
                SELECT 
                    p.id,
                    p.position_title,
                    p.job_description,
                    p.reports_to_position_id,
                    sg.id as salary_grade_id,
                    sg.grade_level,
                    sg.min_salary,
                    sg.max_salary,
                    COUNT(e.id) as employee_count
                FROM positions p
                LEFT JOIN salary_grades sg ON p.salary_grade_id = sg.id
                LEFT JOIN employees e ON p.id = e.position_id AND e.status = 'Active'
                GROUP BY p.id
                ORDER BY sg.grade_level, p.position_title
            ");

            $positions = $stmt->fetchAll();

            // Also get salary grades if requested
            if (isset($_GET['include_grades'])) {
                $stmt = $pdo->query("
                    SELECT 
                        sg.id,
                        sg.grade_level,
                        sg.min_salary,
                        sg.max_salary,
                        COUNT(p.id) as position_count
                    FROM salary_grades sg
                    LEFT JOIN positions p ON sg.id = p.salary_grade_id
                    GROUP BY sg.id
                    ORDER BY sg.grade_level
                ");
                $salaryGrades = $stmt->fetchAll();

                echo json_encode([
                    'ok' => true,
                    'data' => [
                        'positions' => $positions,
                        'salary_grades' => $salaryGrades
                    ]
                ]);
            } else {
                echo json_encode([
                    'ok' => true,
                    'data' => $positions
                ]);
            }
            break;

        case 'POST':
            // Create position or salary grade
            if (!in_array($role, ['HR Manager', 'Hospital Management'])) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['grade_level'])) {
                // Create salary grade
                $stmt = $pdo->prepare("
                    INSERT INTO salary_grades (grade_level, min_salary, max_salary)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $input['grade_level'],
                    $input['min_salary'],
                    $input['max_salary']
                ]);

                $gradeId = $pdo->lastInsertId();

                echo json_encode([
                    'ok' => true,
                    'message' => 'Salary grade created successfully',
                    'grade_id' => $gradeId
                ]);
            } else {
                // Create position
                $stmt = $pdo->prepare("
                    INSERT INTO positions (position_title, salary_grade_id, job_description, reports_to_position_id)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $input['position_title'],
                    $input['salary_grade_id'],
                    $input['job_description'],
                    $input['reports_to_position_id'] ?? null
                ]);

                $positionId = $pdo->lastInsertId();

                echo json_encode([
                    'ok' => true,
                    'message' => 'Position created successfully',
                    'position_id' => $positionId
                ]);
            }
            break;

        case 'PUT':
            // Update position or salary grade
            if (!in_array($role, ['HR Manager', 'Hospital Management'])) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['grade_level'])) {
                // Update salary grade
                $stmt = $pdo->prepare("
                    UPDATE salary_grades 
                    SET grade_level = ?, min_salary = ?, max_salary = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $input['grade_level'],
                    $input['min_salary'],
                    $input['max_salary'],
                    $input['id']
                ]);

                echo json_encode([
                    'ok' => true,
                    'message' => 'Salary grade updated successfully'
                ]);
            } else {
                // Update position
                $stmt = $pdo->prepare("
                    UPDATE positions 
                    SET position_title = ?, salary_grade_id = ?, job_description = ?, reports_to_position_id = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $input['position_title'],
                    $input['salary_grade_id'],
                    $input['job_description'],
                    $input['reports_to_position_id'],
                    $input['id']
                ]);

                echo json_encode([
                    'ok' => true,
                    'message' => 'Position updated successfully'
                ]);
            }
            break;

        case 'DELETE':
            // Delete position or salary grade
            if ($role !== 'Hospital Management') {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $type = $_GET['type'] ?? 'position';
            $id = $_GET['id'];

            if ($type === 'grade') {
                // Check if grade is used by positions
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM positions WHERE salary_grade_id = ?");
                $stmt->execute([$id]);
                $positionCount = $stmt->fetch()['count'];

                if ($positionCount > 0) {
                    http_response_code(400);
                    echo json_encode([
                        'ok' => false,
                        'message' => 'Cannot delete salary grade that is used by positions'
                    ]);
                    exit;
                }

                $stmt = $pdo->prepare("DELETE FROM salary_grades WHERE id = ?");
                $stmt->execute([$id]);

                echo json_encode([
                    'ok' => true,
                    'message' => 'Salary grade deleted successfully'
                ]);
            } else {
                // Check if position has employees
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM employees WHERE position_id = ? AND status = 'Active'");
                $stmt->execute([$id]);
                $employeeCount = $stmt->fetch()['count'];

                if ($employeeCount > 0) {
                    http_response_code(400);
                    echo json_encode([
                        'ok' => false,
                        'message' => 'Cannot delete position that has active employees'
                    ]);
                    exit;
                }

                $stmt = $pdo->prepare("DELETE FROM positions WHERE id = ?");
                $stmt->execute([$id]);

                echo json_encode([
                    'ok' => true,
                    'message' => 'Position deleted successfully'
                ]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    }

} catch (Exception $e) {
    error_log("Positions API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Internal server error']);
}
?>
