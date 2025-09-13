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
            // Get employees list
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $department = isset($_GET['department']) ? (int) $_GET['department'] : null;

            $offset = ($page - 1) * $limit;

            $whereClause = "WHERE e.status = 'Active'";
            $params = [];

            if (!empty($search)) {
                $whereClause .= " AND (e.first_name LIKE :search OR e.last_name LIKE :search OR e.employee_number LIKE :search)";
                $params[':search'] = "%$search%";
            }

            if ($department) {
                $whereClause .= " AND e.department_id = :department";
                $params[':department'] = $department;
            }

            $sql = "
                SELECT 
                    e.id,
                    e.employee_number,
                    e.first_name,
                    e.last_name,
                    e.hire_date,
                    e.status,
                    d.department_name,
                    p.position_title,
                    sg.grade_level,
                    ed.email,
                    ed.contact_number,
                    ed.birth_date,
                    ed.gender,
                    ed.civil_status
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN salary_grades sg ON p.salary_grade_id = sg.id
                LEFT JOIN employee_details ed ON e.id = ed.employee_id
                $whereClause
                ORDER BY e.last_name, e.first_name
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $employees = $stmt->fetchAll();

            // Get total count
            $countSql = "
                SELECT COUNT(*) as total
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN salary_grades sg ON p.salary_grade_id = sg.id
                LEFT JOIN employee_details ed ON e.id = ed.employee_id
                $whereClause
            ";

            $countStmt = $pdo->prepare($countSql);
            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }
            $countStmt->execute();
            $total = $countStmt->fetch()['total'];

            echo json_encode([
                'ok' => true,
                'data' => $employees,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
            break;

        case 'POST':
            // Create new employee
            if (!in_array($role, ['HR Manager', 'HR Specialist'])) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            $pdo->beginTransaction();

            try {
                // Insert employee
                $stmt = $pdo->prepare("
                    INSERT INTO employees (employee_number, first_name, last_name, department_id, position_id, hire_date, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $input['employee_number'],
                    $input['first_name'],
                    $input['last_name'],
                    $input['department_id'],
                    $input['position_id'],
                    $input['hire_date'],
                    $input['status'] ?? 'Active'
                ]);

                $employeeId = $pdo->lastInsertId();

                // Insert employee details
                $stmt = $pdo->prepare("
                    INSERT INTO employee_details (employee_id, birth_date, gender, civil_status, contact_number, email, address, emergency_contact_name, emergency_contact_number, sss_no, philhealth_no, pagibig_no, tin_no, employment_type)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $employeeId,
                    $input['birth_date'],
                    $input['gender'],
                    $input['civil_status'],
                    $input['contact_number'],
                    $input['email'],
                    $input['address'],
                    $input['emergency_contact_name'],
                    $input['emergency_contact_number'],
                    $input['sss_no'],
                    $input['philhealth_no'],
                    $input['pagibig_no'],
                    $input['tin_no'],
                    $input['employment_type']
                ]);

                $pdo->commit();

                echo json_encode([
                    'ok' => true,
                    'message' => 'Employee created successfully',
                    'employee_id' => $employeeId
                ]);

            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'PUT':
            // Update employee
            if (!in_array($role, ['HR Manager', 'HR Specialist'])) {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $employeeId = $input['id'];

            $pdo->beginTransaction();

            try {
                // Update employee
                $stmt = $pdo->prepare("
                    UPDATE employees 
                    SET first_name = ?, last_name = ?, department_id = ?, position_id = ?, hire_date = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $input['first_name'],
                    $input['last_name'],
                    $input['department_id'],
                    $input['position_id'],
                    $input['hire_date'],
                    $input['status'],
                    $employeeId
                ]);

                // Update employee details
                $stmt = $pdo->prepare("
                    UPDATE employee_details 
                    SET birth_date = ?, gender = ?, civil_status = ?, contact_number = ?, email = ?, address = ?, emergency_contact_name = ?, emergency_contact_number = ?, sss_no = ?, philhealth_no = ?, pagibig_no = ?, tin_no = ?, employment_type = ?
                    WHERE employee_id = ?
                ");
                $stmt->execute([
                    $input['birth_date'],
                    $input['gender'],
                    $input['civil_status'],
                    $input['contact_number'],
                    $input['email'],
                    $input['address'],
                    $input['emergency_contact_name'],
                    $input['emergency_contact_number'],
                    $input['sss_no'],
                    $input['philhealth_no'],
                    $input['pagibig_no'],
                    $input['tin_no'],
                    $input['employment_type'],
                    $employeeId
                ]);

                $pdo->commit();

                echo json_encode([
                    'ok' => true,
                    'message' => 'Employee updated successfully'
                ]);

            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'DELETE':
            // Delete employee (soft delete)
            if ($role !== 'HR Manager') {
                http_response_code(403);
                echo json_encode(['ok' => false, 'message' => 'Insufficient permissions']);
                exit;
            }

            $employeeId = $_GET['id'];

            $stmt = $pdo->prepare("UPDATE employees SET status = 'Inactive' WHERE id = ?");
            $stmt->execute([$employeeId]);

            echo json_encode([
                'ok' => true,
                'message' => 'Employee deactivated successfully'
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    }

} catch (Exception $e) {
    error_log("Employees API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Internal server error']);
}
?>
