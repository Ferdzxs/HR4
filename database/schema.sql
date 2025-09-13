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

-- Add foreign key constraints after all tables are created
ALTER TABLE departments 
ADD CONSTRAINT fk_departments_head 
FOREIGN KEY (department_head_id) REFERENCES employees(id) ON DELETE SET NULL;

ALTER TABLE departments 
ADD CONSTRAINT fk_departments_parent 
FOREIGN KEY (parent_department_id) REFERENCES departments(id) ON DELETE SET NULL;

ALTER TABLE users 
ADD CONSTRAINT fk_users_role 
FOREIGN KEY (role_id) REFERENCES roles(id);

ALTER TABLE users 
ADD CONSTRAINT fk_users_employee 
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL;

ALTER TABLE user_sessions 
ADD CONSTRAINT fk_sessions_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

ALTER TABLE employees 
ADD CONSTRAINT fk_employees_department 
FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL;

ALTER TABLE employees 
ADD CONSTRAINT fk_employees_position 
FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE SET NULL;

ALTER TABLE employee_details 
ADD CONSTRAINT fk_employee_details_employee 
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;

ALTER TABLE positions 
ADD CONSTRAINT fk_positions_salary_grade 
FOREIGN KEY (salary_grade_id) REFERENCES salary_grades(id);

ALTER TABLE positions 
ADD CONSTRAINT fk_positions_reports_to 
FOREIGN KEY (reports_to_position_id) REFERENCES positions(id) ON DELETE SET NULL;

ALTER TABLE employee_documents 
ADD CONSTRAINT fk_documents_employee 
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;

ALTER TABLE payroll_entries 
ADD CONSTRAINT fk_payroll_entries_employee 
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;

ALTER TABLE payroll_entries 
ADD CONSTRAINT fk_payroll_entries_period 
FOREIGN KEY (period_id) REFERENCES payroll_periods(id) ON DELETE CASCADE;

ALTER TABLE salary_components 
ADD CONSTRAINT fk_salary_components_employee 
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;

ALTER TABLE employee_loans 
ADD CONSTRAINT fk_loans_employee 
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;

ALTER TABLE government_contributions 
ADD CONSTRAINT fk_contributions_employee 
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;

ALTER TABLE government_contributions 
ADD CONSTRAINT fk_contributions_period 
FOREIGN KEY (period_id) REFERENCES payroll_periods(id) ON DELETE CASCADE;

ALTER TABLE tax_calculations 
ADD CONSTRAINT fk_tax_calculations_payroll 
FOREIGN KEY (payroll_entry_id) REFERENCES payroll_entries(id) ON DELETE CASCADE;

ALTER TABLE bank_accounts 
ADD CONSTRAINT fk_bank_accounts_employee 
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;

ALTER TABLE hmo_plans 
ADD CONSTRAINT fk_hmo_plans_provider 
FOREIGN KEY (provider_id) REFERENCES providers(id);

ALTER TABLE benefit_enrollments 
ADD CONSTRAINT fk_enrollments_employee 
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;

ALTER TABLE benefit_enrollments 
ADD CONSTRAINT fk_enrollments_plan 
FOREIGN KEY (plan_id) REFERENCES hmo_plans(id);

ALTER TABLE benefit_dependents 
ADD CONSTRAINT fk_dependents_enrollment 
FOREIGN KEY (enrollment_id) REFERENCES benefit_enrollments(id) ON DELETE CASCADE;

ALTER TABLE benefit_claims 
ADD CONSTRAINT fk_claims_employee 
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE;

ALTER TABLE benefit_claims 
ADD CONSTRAINT fk_claims_plan 
FOREIGN KEY (plan_id) REFERENCES hmo_plans(id);

ALTER TABLE analytics_metrics 
ADD CONSTRAINT fk_metrics_department 
FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL;

ALTER TABLE audit_logs 
ADD CONSTRAINT fk_audit_logs_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
