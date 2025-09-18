-- HR4 Compensation & Intelligence Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS hr4_compensation_intelli;
USE hr4_compensation_intelli;

-- Roles table
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL,
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Departments table
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100),
    department_head_id INT NULL,
    parent_department_id INT NULL,
    budget_allocation DECIMAL(12,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Salary grades table
CREATE TABLE salary_grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grade_level VARCHAR(20),
    min_salary DECIMAL(10,2),
    max_salary DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Positions table
CREATE TABLE positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    position_title VARCHAR(100),
    salary_grade_id INT,
    job_description TEXT,
    reports_to_position_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (salary_grade_id) REFERENCES salary_grades(id),
    FOREIGN KEY (reports_to_position_id) REFERENCES positions(id)
);

-- Employees table
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_number VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    department_id INT,
    position_id INT,
    hire_date DATE,
    status ENUM('Active','Inactive','Resigned','Terminated','Retired') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Employee details table
CREATE TABLE employee_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    birth_date DATE,
    gender ENUM('M','F','Other'),
    civil_status ENUM('Single','Married','Widowed'),
    contact_number VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    emergency_contact_name VARCHAR(100),
    emergency_contact_number VARCHAR(20),
    sss_no VARCHAR(50),
    philhealth_no VARCHAR(50),
    pagibig_no VARCHAR(50),
    tin_no VARCHAR(50),
    employment_type ENUM('Regular','Contractual','Probationary','Part-time'),
    resignation_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT,
    employee_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User sessions table
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_token VARCHAR(255),
    expires_at TIMESTAMP,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Employee documents table
CREATE TABLE employee_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    document_type VARCHAR(100),
    file_path VARCHAR(255),
    upload_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);

-- Payroll periods table
CREATE TABLE payroll_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period_start DATE,
    period_end DATE,
    status ENUM('Open','Processed','Closed') DEFAULT 'Open',
    processed_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Payroll entries table
CREATE TABLE payroll_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    period_id INT,
    basic_salary DECIMAL(10,2),
    overtime DECIMAL(10,2),
    deductions DECIMAL(10,2),
    net_pay DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (period_id) REFERENCES payroll_periods(id)
);

-- Salary components table
CREATE TABLE salary_components (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    component_type ENUM('Allowance','Bonus','HazardPay','LoanDeduction','Other'),
    amount DECIMAL(10,2),
    effective_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);

-- Employee loans table
CREATE TABLE employee_loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    loan_type VARCHAR(50),
    loan_amount DECIMAL(12,2),
    balance DECIMAL(12,2),
    monthly_deduction DECIMAL(10,2),
    start_date DATE,
    end_date DATE,
    status ENUM('Active','Closed') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);

-- Government contributions table
CREATE TABLE government_contributions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    period_id INT,
    contribution_type ENUM('SSS','PhilHealth','PagIBIG'),
    amount DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (period_id) REFERENCES payroll_periods(id)
);

-- Tax calculations table
CREATE TABLE tax_calculations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payroll_entry_id INT,
    taxable_income DECIMAL(10,2),
    withholding_tax DECIMAL(10,2),
    tax_period DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (payroll_entry_id) REFERENCES payroll_entries(id)
);

-- Bank accounts table
CREATE TABLE bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    bank_name VARCHAR(100),
    account_number VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
);

-- Providers table
CREATE TABLE providers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_name VARCHAR(100),
    contact_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- HMO plans table
CREATE TABLE hmo_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(100),
    provider_id INT,
    premium_amount DECIMAL(10,2),
    coverage_details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES providers(id)
);

-- Benefit enrollments table
CREATE TABLE benefit_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    plan_id INT,
    enrollment_date DATE,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (plan_id) REFERENCES hmo_plans(id)
);

-- Benefit dependents table
CREATE TABLE benefit_dependents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT,
    dependent_name VARCHAR(100),
    relationship VARCHAR(50),
    birth_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES benefit_enrollments(id)
);

-- Benefit claims table
CREATE TABLE benefit_claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    plan_id INT,
    claim_amount DECIMAL(10,2),
    claim_date DATE,
    status ENUM('Pending','Approved','Rejected','Paid') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (plan_id) REFERENCES hmo_plans(id)
);

-- Analytics metrics table
CREATE TABLE analytics_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metric_type VARCHAR(100),
    metric_value DECIMAL(12,2),
    calculation_date DATE,
    department_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Report templates table
CREATE TABLE report_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_name VARCHAR(100),
    report_type VARCHAR(50),
    template_config JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Audit logs table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action_type VARCHAR(50),
    table_affected VARCHAR(100),
    old_values JSON,
    new_values JSON,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



-- ================================================
-- LEAVE MANAGEMENT
-- ================================================
CREATE TABLE leave_policies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    policy_name VARCHAR(100),
    leave_type ENUM('Vacation','Sick','Emergency','Maternity','Paternity','Other'),
    accrual_rate DECIMAL(5,2),
    max_balance DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE leave_balances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    policy_id INT,
    available_days DECIMAL(5,2),
    used_days DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (policy_id) REFERENCES leave_policies(id) ON DELETE CASCADE
);

CREATE TABLE leave_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    policy_id INT,
    start_date DATE,
    end_date DATE,
    status ENUM('Pending','Approved','Rejected','Cancelled') DEFAULT 'Pending',
    approver_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (policy_id) REFERENCES leave_policies(id) ON DELETE CASCADE,
    FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ================================================
-- COMPENSATION PLANNING
-- ================================================
CREATE TABLE compensation_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(100),
    cycle_start DATE,
    cycle_end DATE,
    budget_allocated DECIMAL(12,2),
    budget_utilized DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE merit_increases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    plan_id INT,
    recommended_amount DECIMAL(10,2),
    approved_amount DECIMAL(10,2),
    approver_id INT,
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES compensation_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (approver_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE pay_equity_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    generated_on DATE,
    scope ENUM('Department','Organization'),
    findings JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE internal_benchmarking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT,
    avg_salary DECIMAL(12,2),
    comparison_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

-- ================================================
-- PERFORMANCE MANAGEMENT
-- ================================================
CREATE TABLE performance_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    reviewer_id INT,
    review_period_start DATE,
    review_period_end DATE,
    rating DECIMAL(3,2),
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ================================================
-- DELEGATIONS
-- ================================================
CREATE TABLE delegations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_user_id INT,
    to_user_id INT,
    role_granted VARCHAR(100),
    start_date DATE,
    end_date DATE,
    status ENUM('Active','Expired','Revoked') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ================================================
-- EMPLOYEE/ BENEFITS SUPPORT
-- ================================================
CREATE TABLE benefit_inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    subject VARCHAR(200),
    description TEXT,
    status ENUM('Open','In Progress','Resolved','Closed') DEFAULT 'Open',
    resolution_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- ================================================
-- PAYSLIPS
-- ================================================
CREATE TABLE payslips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payroll_entry_id INT,
    file_path VARCHAR(255),
    issue_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payroll_entry_id) REFERENCES payroll_entries(id) ON DELETE CASCADE
);

-- ================================================
-- HELP CENTER
-- ================================================
CREATE TABLE help_articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE support_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT,
    category ENUM('Payroll','Benefits','Compensation','General'),
    subject VARCHAR(200),
    description TEXT,
    status ENUM('Open','In Progress','Resolved','Closed') DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- ================================================
-- EXECUTIVE COMPENSATION INSIGHTS
-- ================================================
CREATE TABLE compensation_insights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT,
    total_salary DECIMAL(15,2),
    avg_salary DECIMAL(12,2),
    equity_index DECIMAL(5,2),
    snapshot_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);
