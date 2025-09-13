<?php
/**
 * Force Reset Database Script
 * HR4 Compensation & Intelligence System
 * 
 * This script completely resets the database by disabling foreign key checks
 */

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Force resetting HR4 Compensation & Intelligence System...\n";
    echo "=======================================================\n\n";
    
    // Disable foreign key checks
    echo "Disabling foreign key checks...\n";
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "✓ Foreign key checks disabled\n\n";
    
    // Drop all tables
    echo "Dropping all tables...\n";
    $tables = ['sessions', 'audit_logs', 'users', 'employees', 'departments', 'roles'];
    foreach ($tables as $table) {
        try {
            $db->exec("DROP TABLE IF EXISTS $table");
            echo "✓ Dropped table: $table\n";
        } catch (Exception $e) {
            echo "⚠ Warning dropping $table: " . $e->getMessage() . "\n";
        }
    }
    
    // Re-enable foreign key checks
    echo "\nRe-enabling foreign key checks...\n";
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "✓ Foreign key checks enabled\n\n";
    
    // Now create fresh tables
    echo "Creating fresh database...\n";
    
    // Create roles table
    $db->exec("
        CREATE TABLE roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_name VARCHAR(50) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Roles table created\n";
    
    // Create departments table
    $db->exec("
        CREATE TABLE departments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            manager_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✓ Departments table created\n";
    
    // Create employees table
    $db->exec("
        CREATE TABLE employees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_number VARCHAR(20) NOT NULL UNIQUE,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            phone VARCHAR(20),
            hire_date DATE NOT NULL,
            status ENUM('Active', 'Inactive', 'Terminated') DEFAULT 'Active',
            department_id INT,
            position VARCHAR(100),
            salary DECIMAL(10,2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (department_id) REFERENCES departments(id)
        )
    ");
    echo "✓ Employees table created\n";
    
    // Create users table
    $db->exec("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role_id INT NOT NULL,
            employee_id INT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            FOREIGN KEY (role_id) REFERENCES roles(id),
            FOREIGN KEY (employee_id) REFERENCES employees(id)
        )
    ");
    echo "✓ Users table created\n";
    
    // Create sessions table
    $db->exec("
        CREATE TABLE sessions (
            id VARCHAR(128) PRIMARY KEY,
            user_id INT NOT NULL,
            data TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "✓ Sessions table created\n";
    
    // Create audit_logs table
    $db->exec("
        CREATE TABLE audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100) NOT NULL,
            table_name VARCHAR(50),
            record_id INT,
            old_values JSON,
            new_values JSON,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
    echo "✓ Audit logs table created\n\n";
    
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
    echo "✓ Roles created\n";
    
    // Insert departments
    echo "Creating departments...\n";
    $departments = [
        ['Human Resources', 'HR Department'],
        ['Compensation', 'Compensation Department'],
        ['Benefits', 'Benefits Department'],
        ['Payroll', 'Payroll Department'],
        ['Medical', 'Medical Department'],
        ['Administration', 'Administration Department'],
        ['Management', 'Executive Management']
    ];
    
    $deptStmt = $db->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
    foreach ($departments as $dept) {
        $deptStmt->execute($dept);
    }
    echo "✓ Departments created\n";
    
    // Get role and department IDs
    $roleMap = [];
    $roleQuery = $db->query("SELECT id, role_name FROM roles");
    while ($row = $roleQuery->fetch()) {
        $roleMap[$row['role_name']] = $row['id'];
    }
    
    $deptMap = [];
    $deptQuery = $db->query("SELECT id, name FROM departments");
    while ($row = $deptQuery->fetch()) {
        $deptMap[$row['name']] = $row['id'];
    }
    
    // Insert demo employees
    echo "Creating demo employees...\n";
    $employees = [
        ['EMP001', 'John', 'Smith', 'john.smith@hospital.com', '09123456789', '2020-01-15', 'Active', $deptMap['Human Resources'], 'HR Manager', 75000],
        ['EMP002', 'Sarah', 'Johnson', 'sarah.johnson@hospital.com', '09123456790', '2020-03-20', 'Active', $deptMap['Compensation'], 'Compensation Manager', 70000],
        ['EMP003', 'Mike', 'Brown', 'mike.brown@hospital.com', '09123456791', '2020-05-10', 'Active', $deptMap['Benefits'], 'Benefits Coordinator', 65000],
        ['EMP004', 'Lisa', 'Davis', 'lisa.davis@hospital.com', '09123456792', '2020-07-15', 'Active', $deptMap['Payroll'], 'Payroll Administrator', 68000],
        ['EMP005', 'David', 'Wilson', 'david.wilson@hospital.com', '09123456793', '2020-09-01', 'Active', $deptMap['Medical'], 'Department Head', 80000],
        ['EMP006', 'Maria', 'Garcia', 'maria.garcia@hospital.com', '09123456794', '2020-11-15', 'Active', $deptMap['Medical'], 'Nurse', 45000],
        ['EMP007', 'Robert', 'Taylor', 'robert.taylor@hospital.com', '09123456795', '2021-01-10', 'Active', $deptMap['Management'], 'CEO', 120000]
    ];
    
    $empStmt = $db->prepare("
        INSERT INTO employees (employee_number, first_name, last_name, email, phone, hire_date, status, department_id, position, salary) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($employees as $emp) {
        $empStmt->execute($emp);
    }
    echo "✓ Employees created\n";
    
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
    echo "✓ Users created\n\n";
    
    echo "🎉 Force reset completed successfully!\n\n";
    echo "Demo Credentials:\n";
    echo "================\n";
    echo "HR Manager: hr.manager / manager123\n";
    echo "Compensation Manager: comp.manager / comp123\n";
    echo "Benefits Coordinator: benefits.coord / benefits123\n";
    echo "Payroll Administrator: payroll.admin / payroll123\n";
    echo "Department Head: dept.head / dept123\n";
    echo "Hospital Employee: employee / emp123\n";
    echo "Hospital Management: executive / exec123\n\n";
    echo "You can now access the system at: http://localhost/HR4_COMPEN&INTELLI/\n";
    
} catch (Exception $e) {
    echo "❌ Error force resetting database: " . $e->getMessage() . "\n";
    echo "Make sure your database is properly configured and accessible.\n";
}
?>
