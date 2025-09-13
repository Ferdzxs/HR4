<?php
/**
 * Demo Users Setup Script
 * HR4 Compensation & Intelligence System
 * 
 * This script creates demo users for testing the system
 */

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "Setting up demo users...\n";

    // First, let's check if users already exist
    $checkStmt = $db->prepare("SELECT COUNT(*) FROM users");
    $checkStmt->execute();
    $userCount = $checkStmt->fetchColumn();

    if ($userCount > 0) {
        echo "Users already exist. Clearing existing users...\n";
        $db->exec("DELETE FROM users");
        $db->exec("DELETE FROM employees");
        $db->exec("DELETE FROM roles");
    }

    // Insert roles
    echo "Creating roles...\n";
    $roles = [
        ['HR Manager', 'Full access to HR functions'],
        ['Compensation Manager', 'Manages compensation and salary structures'],
        ['Benefits Coordinator', 'Handles benefits administration'],
        ['Payroll Administrator', 'Processes payroll and tax compliance'],
        ['Department Head', 'Manages department and team approvals'],
        ['Hospital Employee', 'Basic employee access'],
        ['Hospital Management', 'Executive level access']
    ];

    $roleStmt = $db->prepare("INSERT INTO roles (role_name, description) VALUES (?, ?)");
    foreach ($roles as $role) {
        $roleStmt->execute($role);
    }

    // Get role IDs
    $roleMap = [];
    $roleQuery = $db->query("SELECT id, role_name FROM roles");
    while ($row = $roleQuery->fetch()) {
        $roleMap[$row['role_name']] = $row['id'];
    }

    // Insert demo employees
    echo "Creating demo employees...\n";
    $employees = [
        ['EMP001', 'John', 'Smith', 'john.smith@hospital.com', '09123456789', '2020-01-15', 'Active', 1],
        ['EMP002', 'Sarah', 'Johnson', 'sarah.johnson@hospital.com', '09123456790', '2020-03-20', 'Active', 2],
        ['EMP003', 'Mike', 'Brown', 'mike.brown@hospital.com', '09123456791', '2020-05-10', 'Active', 3],
        ['EMP004', 'Lisa', 'Davis', 'lisa.davis@hospital.com', '09123456792', '2020-07-15', 'Active', 4],
        ['EMP005', 'David', 'Wilson', 'david.wilson@hospital.com', '09123456793', '2020-09-01', 'Active', 5],
        ['EMP006', 'Maria', 'Garcia', 'maria.garcia@hospital.com', '09123456794', '2020-11-15', 'Active', 6],
        ['EMP007', 'Robert', 'Taylor', 'robert.taylor@hospital.com', '09123456795', '2021-01-10', 'Active', 7]
    ];

    $empStmt = $db->prepare("
        INSERT INTO employees (employee_number, first_name, last_name, email, phone, hire_date, status, department_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($employees as $emp) {
        $empStmt->execute($emp);
    }

    // Get employee IDs
    $empMap = [];
    $empQuery = $db->query("SELECT id, employee_number FROM employees");
    while ($row = $empQuery->fetch()) {
        $empMap[$row['employee_number']] = $row['id'];
    }

    // Insert demo users
    echo "Creating demo users...\n";
    $users = [
        ['hr.manager', password_hash('manager123', PASSWORD_DEFAULT), $roleMap['HR Manager'], $empMap['EMP001']],
        ['comp.manager', password_hash('comp123', PASSWORD_DEFAULT), $roleMap['Compensation Manager'], $empMap['EMP002']],
        ['benefits.coord', password_hash('benefits123', PASSWORD_DEFAULT), $roleMap['Benefits Coordinator'], $empMap['EMP003']],
        ['payroll.admin', password_hash('payroll123', PASSWORD_DEFAULT), $roleMap['Payroll Administrator'], $empMap['EMP004']],
        ['dept.head', password_hash('dept123', PASSWORD_DEFAULT), $roleMap['Department Head'], $empMap['EMP005']],
        ['employee', password_hash('emp123', PASSWORD_DEFAULT), $roleMap['Hospital Employee'], $empMap['EMP006']],
        ['executive', password_hash('exec123', PASSWORD_DEFAULT), $roleMap['Hospital Management'], $empMap['EMP007']]
    ];

    $userStmt = $db->prepare("
        INSERT INTO users (username, password_hash, role_id, employee_id) 
        VALUES (?, ?, ?, ?)
    ");

    foreach ($users as $user) {
        $userStmt->execute($user);
    }

    echo "Demo users created successfully!\n";
    echo "\nDemo Credentials:\n";
    echo "================\n";
    echo "HR Manager: hr.manager / manager123\n";
    echo "Compensation Manager: comp.manager / comp123\n";
    echo "Benefits Coordinator: benefits.coord / benefits123\n";
    echo "Payroll Administrator: payroll.admin / payroll123\n";
    echo "Department Head: dept.head / dept123\n";
    echo "Hospital Employee: employee / emp123\n";
    echo "Hospital Management: executive / exec123\n";

} catch (Exception $e) {
    echo "Error setting up demo users: " . $e->getMessage() . "\n";
    echo "Make sure your database is properly configured and the tables exist.\n";
}
?>
