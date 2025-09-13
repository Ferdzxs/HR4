-- Reset Database and Load Sample Data
-- This script will clear all existing data and load the sample data

USE hr4_compensation_intelli;

-- Disable foreign key checks temporarily to avoid constraint issues during deletion
SET FOREIGN_KEY_CHECKS = 0;

-- Clear all data from tables in reverse dependency order
-- (child tables first, then parent tables)

-- Clear child tables first
DELETE FROM audit_logs;
DELETE FROM analytics_metrics;
DELETE FROM benefit_claims;
DELETE FROM benefit_dependents;
DELETE FROM benefit_enrollments;
DELETE FROM hmo_plans;
DELETE FROM providers;
DELETE FROM bank_accounts;
DELETE FROM tax_calculations;
DELETE FROM government_contributions;
DELETE FROM employee_loans;
DELETE FROM salary_components;
DELETE FROM payroll_entries;
DELETE FROM payroll_periods;
DELETE FROM employee_documents;
DELETE FROM user_sessions;
DELETE FROM users;
DELETE FROM employee_details;
DELETE FROM employees;
DELETE FROM positions;
DELETE FROM salary_grades;
DELETE FROM departments;
DELETE FROM roles;
DELETE FROM report_templates;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Reset AUTO_INCREMENT counters
ALTER TABLE roles AUTO_INCREMENT = 1;
ALTER TABLE departments AUTO_INCREMENT = 1;
ALTER TABLE salary_grades AUTO_INCREMENT = 1;
ALTER TABLE positions AUTO_INCREMENT = 1;
ALTER TABLE employees AUTO_INCREMENT = 1;
ALTER TABLE employee_details AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE user_sessions AUTO_INCREMENT = 1;
ALTER TABLE employee_documents AUTO_INCREMENT = 1;
ALTER TABLE payroll_periods AUTO_INCREMENT = 1;
ALTER TABLE payroll_entries AUTO_INCREMENT = 1;
ALTER TABLE salary_components AUTO_INCREMENT = 1;
ALTER TABLE employee_loans AUTO_INCREMENT = 1;
ALTER TABLE government_contributions AUTO_INCREMENT = 1;
ALTER TABLE tax_calculations AUTO_INCREMENT = 1;
ALTER TABLE bank_accounts AUTO_INCREMENT = 1;
ALTER TABLE providers AUTO_INCREMENT = 1;
ALTER TABLE hmo_plans AUTO_INCREMENT = 1;
ALTER TABLE benefit_enrollments AUTO_INCREMENT = 1;
ALTER TABLE benefit_dependents AUTO_INCREMENT = 1;
ALTER TABLE benefit_claims AUTO_INCREMENT = 1;
ALTER TABLE analytics_metrics AUTO_INCREMENT = 1;
ALTER TABLE report_templates AUTO_INCREMENT = 1;
ALTER TABLE audit_logs AUTO_INCREMENT = 1;

-- Now load the sample data
-- Insert roles
INSERT INTO roles (role_name, permissions) VALUES
('HR Manager', '{"modules": ["employees", "organization", "payroll", "compensation", "benefits", "analytics", "delegations", "bulk", "settings"]}'),
('Compensation Manager', '{"modules": ["employees-read", "compensation", "analytics"]}'),
('Benefits Coordinator', '{"modules": ["employees-read", "benefits", "analytics", "providers"]}'),
('Payroll Administrator', '{"modules": ["employees-read", "payroll", "analytics", "compliance"]}'),
('Department Head', '{"modules": ["team", "analytics", "approvals", "reports"]}'),
('Hospital Employee', '{"modules": ["profile", "payslips", "benefits-center", "leave", "documents", "help"]}'),
('Hospital Management', '{"modules": ["executive", "analytics", "reports"]}');

-- Insert salary grades
INSERT INTO salary_grades (grade_level, min_salary, max_salary) VALUES
('Grade 1', 15000.00, 20000.00),
('Grade 2', 20000.00, 25000.00),
('Grade 3', 25000.00, 30000.00),
('Grade 4', 30000.00, 35000.00),
('Grade 5', 35000.00, 40000.00),
('Grade 6', 40000.00, 50000.00),
('Grade 7', 50000.00, 60000.00),
('Grade 8', 60000.00, 75000.00),
('Grade 9', 75000.00, 90000.00),
('Grade 10', 90000.00, 120000.00);

-- Insert departments
INSERT INTO departments (department_name, budget_allocation) VALUES
('Emergency Department', 5000000.00),
('Outpatient Department', 3000000.00),
('Surgery', 8000000.00),
('Pediatrics', 2500000.00),
('Cardiology', 4000000.00),
('Radiology', 2000000.00),
('Laboratory', 1500000.00),
('Pharmacy', 1000000.00),
('Administration', 2000000.00),
('Human Resources', 1000000.00);

-- Insert positions
INSERT INTO positions (position_title, salary_grade_id, job_description) VALUES
('Chief Medical Officer', 10, 'Oversees all medical operations and strategic planning'),
('Department Head - Emergency', 9, 'Manages emergency department operations'),
('Department Head - Surgery', 9, 'Manages surgical department operations'),
('Senior Physician', 8, 'Provides medical care and supervision'),
('Staff Physician', 7, 'Provides direct patient care'),
('Nurse Supervisor', 6, 'Supervises nursing staff and operations'),
('Registered Nurse', 5, 'Provides nursing care to patients'),
('Medical Technologist', 4, 'Performs laboratory tests and procedures'),
('Pharmacist', 4, 'Manages pharmacy operations and medication dispensing'),
('HR Manager', 6, 'Manages human resources operations'),
('HR Assistant', 3, 'Assists with HR administrative tasks'),
('Payroll Administrator', 5, 'Manages payroll processing and administration'),
('Benefits Coordinator', 4, 'Manages employee benefits and enrollments'),
('Compensation Manager', 7, 'Manages compensation planning and analysis'),
('Administrative Assistant', 2, 'Provides administrative support');

-- Insert employees
INSERT INTO employees (employee_number, first_name, last_name, department_id, position_id, hire_date, status) VALUES
('EMP001', 'John', 'Smith', 1, 2, '2020-01-15', 'Active'),
('EMP002', 'Sarah', 'Johnson', 2, 5, '2019-03-20', 'Active'),
('EMP003', 'Michael', 'Brown', 3, 3, '2018-06-10', 'Active'),
('EMP004', 'Emily', 'Davis', 4, 5, '2021-02-28', 'Active'),
('EMP005', 'David', 'Wilson', 5, 5, '2017-09-15', 'Active'),
('EMP006', 'Lisa', 'Anderson', 1, 7, '2020-11-05', 'Active'),
('EMP007', 'Robert', 'Taylor', 2, 7, '2019-07-12', 'Active'),
('EMP008', 'Jennifer', 'Thomas', 3, 7, '2021-04-18', 'Active'),
('EMP009', 'William', 'Jackson', 4, 7, '2018-12-03', 'Active'),
('EMP010', 'Maria', 'White', 5, 7, '2020-08-22', 'Active'),
('EMP011', 'James', 'Harris', 6, 8, '2019-01-30', 'Active'),
('EMP012', 'Patricia', 'Martin', 7, 8, '2020-05-14', 'Active'),
('EMP013', 'Richard', 'Garcia', 8, 9, '2018-10-08', 'Active'),
('EMP014', 'Linda', 'Martinez', 9, 10, '2017-04-25', 'Active'),
('EMP015', 'Charles', 'Robinson', 10, 11, '2019-11-12', 'Active'),
('EMP016', 'Barbara', 'Clark', 10, 12, '2018-02-17', 'Active'),
('EMP017', 'Joseph', 'Rodriguez', 10, 13, '2020-09-03', 'Active'),
('EMP018', 'Susan', 'Lewis', 10, 14, '2019-06-28', 'Active'),
('EMP019', 'Thomas', 'Lee', 1, 1, '2016-01-10', 'Active'),
('EMP020', 'Jessica', 'Walker', 2, 6, '2018-08-15', 'Active');

-- Update department heads
UPDATE departments SET department_head_id = 1 WHERE id = 1; -- Emergency
UPDATE departments SET department_head_id = 3 WHERE id = 3; -- Surgery
UPDATE departments SET department_head_id = 19 WHERE id = 9; -- Administration

-- Insert employee details
INSERT INTO employee_details (employee_id, birth_date, gender, civil_status, contact_number, email, address, emergency_contact_name, emergency_contact_number, sss_no, philhealth_no, pagibig_no, tin_no, employment_type) VALUES
(1, '1985-03-15', 'M', 'Married', '09171234567', 'john.smith@hospital.com', '123 Main St, Manila', 'Jane Smith', '09171234568', '1234567890', '123456789012', '123456789012', '123456789', 'Regular'),
(2, '1988-07-22', 'F', 'Single', '09171234569', 'sarah.johnson@hospital.com', '456 Oak Ave, Quezon City', 'Robert Johnson', '09171234570', '1234567891', '123456789013', '123456789013', '123456790', 'Regular'),
(3, '1982-11-08', 'M', 'Married', '09171234571', 'michael.brown@hospital.com', '789 Pine St, Makati', 'Lisa Brown', '09171234572', '1234567892', '123456789014', '123456789014', '123456791', 'Regular'),
(4, '1990-05-14', 'F', 'Single', '09171234573', 'emily.davis@hospital.com', '321 Elm St, Taguig', 'Mark Davis', '09171234574', '1234567893', '123456789015', '123456789015', '123456792', 'Regular'),
(5, '1987-09-30', 'M', 'Married', '09171234575', 'david.wilson@hospital.com', '654 Maple Ave, Pasig', 'Susan Wilson', '09171234576', '1234567894', '123456789016', '123456789016', '123456793', 'Regular');

-- Insert users with hashed passwords
INSERT INTO users (username, password_hash, role_id, employee_id) VALUES
('hr.manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 15), -- password: hr123
('comp.manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 18), -- password: comp123
('benefits.coord', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 17), -- password: benefits123
('payroll.admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 16), -- password: payroll123
('dept.head', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, 1), -- password: dept123
('employee', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 2), -- password: emp123
('executive', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 7, 19); -- password: exec123

-- Insert providers
INSERT INTO providers (provider_name, contact_info) VALUES
('Maxicare Healthcare', 'Phone: (02) 8888-8888, Email: info@maxicare.com.ph'),
('PhilCare', 'Phone: (02) 7777-7777, Email: contact@philcare.com.ph'),
('MediCard', 'Phone: (02) 6666-6666, Email: support@medicard.com.ph'),
('Intellicare', 'Phone: (02) 5555-5555, Email: help@intellicare.com.ph');

-- Insert HMO plans
INSERT INTO hmo_plans (plan_name, provider_id, premium_amount, coverage_details) VALUES
('Maxicare Gold', 1, 2500.00, 'Comprehensive coverage including hospitalization, outpatient, and emergency care'),
('Maxicare Silver', 1, 1800.00, 'Standard coverage with hospitalization and outpatient benefits'),
('PhilCare Premium', 2, 2200.00, 'Premium plan with extended coverage and wellness benefits'),
('MediCard Plus', 3, 2000.00, 'Enhanced coverage with dental and optical benefits'),
('Intellicare Basic', 4, 1500.00, 'Basic coverage for essential medical services');

-- Insert benefit enrollments
INSERT INTO benefit_enrollments (employee_id, plan_id, enrollment_date, status) VALUES
(1, 1, '2020-01-15', 'Active'),
(2, 2, '2019-03-20', 'Active'),
(3, 1, '2018-06-10', 'Active'),
(4, 3, '2021-02-28', 'Active'),
(5, 2, '2017-09-15', 'Active'),
(6, 4, '2020-11-05', 'Active'),
(7, 1, '2019-07-12', 'Active'),
(8, 2, '2021-04-18', 'Active'),
(9, 3, '2018-12-03', 'Active'),
(10, 4, '2020-08-22', 'Active');

-- Insert payroll periods
INSERT INTO payroll_periods (period_start, period_end, status) VALUES
('2024-01-01', '2024-01-15', 'Closed'),
('2024-01-16', '2024-01-31', 'Closed'),
('2024-02-01', '2024-02-15', 'Closed'),
('2024-02-16', '2024-02-29', 'Closed'),
('2024-03-01', '2024-03-15', 'Closed'),
('2024-03-16', '2024-03-31', 'Closed'),
('2024-04-01', '2024-04-15', 'Closed'),
('2024-04-16', '2024-04-30', 'Closed'),
('2024-05-01', '2024-05-15', 'Closed'),
('2024-05-16', '2024-05-31', 'Closed'),
('2024-06-01', '2024-06-15', 'Closed'),
('2024-06-16', '2024-06-30', 'Closed'),
('2024-07-01', '2024-07-15', 'Closed'),
('2024-07-16', '2024-07-31', 'Closed'),
('2024-08-01', '2024-08-15', 'Closed'),
('2024-08-16', '2024-08-31', 'Closed'),
('2024-09-01', '2024-09-15', 'Closed'),
('2024-09-16', '2024-09-30', 'Closed'),
('2024-10-01', '2024-10-15', 'Closed'),
('2024-10-16', '2024-10-31', 'Closed'),
('2024-11-01', '2024-11-15', 'Closed'),
('2024-11-16', '2024-11-30', 'Closed'),
('2024-12-01', '2024-12-15', 'Processed'),
('2024-12-16', '2024-12-31', 'Open');

-- Insert sample payroll entries
INSERT INTO payroll_entries (employee_id, period_id, basic_salary, overtime, deductions, net_pay) VALUES
(1, 23, 75000.00, 5000.00, 8500.00, 71500.00),
(2, 23, 45000.00, 2000.00, 5200.00, 41800.00),
(3, 23, 80000.00, 8000.00, 9200.00, 78800.00),
(4, 23, 45000.00, 1500.00, 5100.00, 41400.00),
(5, 23, 50000.00, 3000.00, 5800.00, 47200.00),
(6, 23, 35000.00, 2500.00, 4200.00, 33300.00),
(7, 23, 35000.00, 2000.00, 4100.00, 32900.00),
(8, 23, 35000.00, 3000.00, 4200.00, 33800.00),
(9, 23, 35000.00, 1500.00, 4050.00, 32450.00),
(10, 23, 35000.00, 2000.00, 4100.00, 32900.00);

-- Insert salary components
INSERT INTO salary_components (employee_id, component_type, amount, effective_date) VALUES
(1, 'Allowance', 5000.00, '2024-01-01'),
(1, 'HazardPay', 3000.00, '2024-01-01'),
(2, 'Allowance', 3000.00, '2024-01-01'),
(3, 'Allowance', 5000.00, '2024-01-01'),
(3, 'HazardPay', 4000.00, '2024-01-01'),
(4, 'Allowance', 3000.00, '2024-01-01'),
(5, 'Allowance', 3500.00, '2024-01-01'),
(6, 'Allowance', 2500.00, '2024-01-01'),
(7, 'Allowance', 2500.00, '2024-01-01'),
(8, 'Allowance', 2500.00, '2024-01-01');

-- Insert government contributions
INSERT INTO government_contributions (employee_id, period_id, contribution_type, amount) VALUES
(1, 23, 'SSS', 1200.00),
(1, 23, 'PhilHealth', 800.00),
(1, 23, 'PagIBIG', 200.00),
(2, 23, 'SSS', 800.00),
(2, 23, 'PhilHealth', 600.00),
(2, 23, 'PagIBIG', 200.00),
(3, 23, 'SSS', 1200.00),
(3, 23, 'PhilHealth', 800.00),
(3, 23, 'PagIBIG', 200.00),
(4, 23, 'SSS', 800.00),
(4, 23, 'PhilHealth', 600.00),
(4, 23, 'PagIBIG', 200.00),
(5, 23, 'SSS', 900.00),
(5, 23, 'PhilHealth', 650.00),
(5, 23, 'PagIBIG', 200.00);

-- Insert bank accounts
INSERT INTO bank_accounts (employee_id, bank_name, account_number) VALUES
(1, 'BDO', '1234567890'),
(2, 'BPI', '0987654321'),
(3, 'Metrobank', '1122334455'),
(4, 'BDO', '5566778899'),
(5, 'BPI', '9988776655'),
(6, 'Metrobank', '4433221100'),
(7, 'BDO', '7788990011'),
(8, 'BPI', '2233445566'),
(9, 'Metrobank', '6677889900'),
(10, 'BDO', '3344556677');

-- Insert analytics metrics
INSERT INTO analytics_metrics (metric_type, metric_value, calculation_date, department_id) VALUES
('Total Headcount', 20.00, '2024-12-01', NULL),
('Average Salary', 45000.00, '2024-12-01', NULL),
('Turnover Rate', 5.00, '2024-12-01', NULL),
('Emergency Headcount', 3.00, '2024-12-01', 1),
('Surgery Headcount', 3.00, '2024-12-01', 3),
('Outpatient Headcount', 3.00, '2024-12-01', 2),
('Pediatrics Headcount', 2.00, '2024-12-01', 4),
('Cardiology Headcount', 2.00, '2024-12-01', 5);

-- Insert additional employee details for remaining employees
INSERT INTO employee_details (employee_id, birth_date, gender, civil_status, contact_number, email, address, emergency_contact_name, emergency_contact_number, sss_no, philhealth_no, pagibig_no, tin_no, employment_type) VALUES
(6, '1992-08-12', 'F', 'Married', '09171234577', 'lisa.anderson@hospital.com', '987 Cedar St, Mandaluyong', 'Mark Anderson', '09171234578', '1234567895', '123456789017', '123456789017', '123456794', 'Regular'),
(7, '1989-12-03', 'M', 'Single', '09171234579', 'robert.taylor@hospital.com', '654 Birch Ave, San Juan', 'Mary Taylor', '09171234580', '1234567896', '123456789018', '123456789018', '123456795', 'Regular'),
(8, '1991-04-18', 'F', 'Married', '09171234581', 'jennifer.thomas@hospital.com', '321 Walnut St, Marikina', 'John Thomas', '09171234582', '1234567897', '123456789019', '123456789019', '123456796', 'Regular'),
(9, '1986-09-25', 'M', 'Single', '09171234583', 'william.jackson@hospital.com', '789 Spruce Ave, Pasay', 'Sarah Jackson', '09171234584', '1234567898', '123456789020', '123456789020', '123456797', 'Regular'),
(10, '1993-01-14', 'F', 'Married', '09171234585', 'maria.white@hospital.com', '456 Ash St, Las Pinas', 'Carlos White', '09171234586', '1234567899', '123456789021', '123456789021', '123456798', 'Regular'),
(11, '1984-06-30', 'M', 'Married', '09171234587', 'james.harris@hospital.com', '123 Oak Ave, Paranaque', 'Linda Harris', '09171234588', '1234567900', '123456789022', '123456789022', '123456799', 'Regular'),
(12, '1987-11-22', 'F', 'Single', '09171234589', 'patricia.martin@hospital.com', '987 Pine St, Muntinlupa', 'Robert Martin', '09171234590', '1234567901', '123456789023', '123456789023', '123456800', 'Regular'),
(13, '1983-03-08', 'M', 'Married', '09171234591', 'richard.garcia@hospital.com', '654 Maple Ave, Valenzuela', 'Maria Garcia', '09171234592', '1234567902', '123456789024', '123456789024', '123456801', 'Regular'),
(14, '1980-07-15', 'F', 'Married', '09171234593', 'linda.martinez@hospital.com', '321 Elm St, Malabon', 'Jose Martinez', '09171234594', '1234567903', '123456789025', '123456789025', '123456802', 'Regular'),
(15, '1985-10-28', 'M', 'Single', '09171234595', 'charles.robinson@hospital.com', '789 Cherry St, Navotas', 'Susan Robinson', '09171234596', '1234567904', '123456789026', '123456789026', '123456803', 'Regular'),
(16, '1988-02-11', 'F', 'Married', '09171234597', 'barbara.clark@hospital.com', '456 Poplar Ave, Caloocan', 'Michael Clark', '09171234598', '1234567905', '123456789027', '123456789027', '123456804', 'Regular'),
(17, '1982-05-19', 'M', 'Single', '09171234599', 'joseph.rodriguez@hospital.com', '123 Hickory St, Quezon City', 'Ana Rodriguez', '09171234600', '1234567906', '123456789028', '123456789028', '123456805', 'Regular'),
(18, '1989-08-07', 'F', 'Married', '09171234601', 'susan.lewis@hospital.com', '987 Sycamore Ave, Manila', 'David Lewis', '09171234602', '1234567907', '123456789029', '123456789029', '123456806', 'Regular'),
(19, '1978-12-14', 'M', 'Married', '09171234603', 'thomas.lee@hospital.com', '654 Chestnut St, Makati', 'Jennifer Lee', '09171234604', '1234567908', '123456789030', '123456789030', '123456807', 'Regular'),
(20, '1990-03-26', 'F', 'Single', '09171234605', 'jessica.walker@hospital.com', '321 Dogwood Ave, Taguig', 'Robert Walker', '09171234606', '1234567909', '123456789031', '123456789031', '123456808', 'Regular');

-- Insert additional users for all employees
INSERT INTO users (username, password_hash, role_id, employee_id) VALUES
('john.smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 1), -- password: emp123
('sarah.johnson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 2), -- password: emp123
('michael.brown', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 3), -- password: emp123
('emily.davis', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 4), -- password: emp123
('david.wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 5), -- password: emp123
('lisa.anderson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 6), -- password: emp123
('robert.taylor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 7), -- password: emp123
('jennifer.thomas', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 8), -- password: emp123
('william.jackson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 9), -- password: emp123
('maria.white', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 10), -- password: emp123
('james.harris', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 11), -- password: emp123
('patricia.martin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 12), -- password: emp123
('richard.garcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 13), -- password: emp123
('linda.martinez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 6, 14), -- password: emp123
('charles.robinson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 15), -- password: hr123
('barbara.clark', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 16), -- password: payroll123
('joseph.rodriguez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 17), -- password: benefits123
('susan.lewis', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 18), -- password: comp123
('thomas.lee', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 7, 19), -- password: exec123
('jessica.walker', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 5, 20); -- password: dept123

-- Insert employee documents
INSERT INTO employee_documents (employee_id, document_type, file_path, upload_date) VALUES
(1, 'Resume', '/documents/EMP001_resume.pdf', '2020-01-15'),
(1, 'ID Picture', '/documents/EMP001_id_photo.jpg', '2020-01-15'),
(1, 'Medical Certificate', '/documents/EMP001_medical.pdf', '2020-01-15'),
(2, 'Resume', '/documents/EMP002_resume.pdf', '2019-03-20'),
(2, 'ID Picture', '/documents/EMP002_id_photo.jpg', '2019-03-20'),
(3, 'Resume', '/documents/EMP003_resume.pdf', '2018-06-10'),
(3, 'Medical Certificate', '/documents/EMP003_medical.pdf', '2018-06-10'),
(4, 'Resume', '/documents/EMP004_resume.pdf', '2021-02-28'),
(5, 'Resume', '/documents/EMP005_resume.pdf', '2017-09-15'),
(5, 'Medical Certificate', '/documents/EMP005_medical.pdf', '2017-09-15'),
(6, 'Resume', '/documents/EMP006_resume.pdf', '2020-11-05'),
(7, 'Resume', '/documents/EMP007_resume.pdf', '2019-07-12'),
(8, 'Resume', '/documents/EMP008_resume.pdf', '2021-04-18'),
(9, 'Resume', '/documents/EMP009_resume.pdf', '2018-12-03'),
(10, 'Resume', '/documents/EMP010_resume.pdf', '2020-08-22');

-- Insert employee loans
INSERT INTO employee_loans (employee_id, loan_type, loan_amount, balance, monthly_deduction, start_date, end_date, status) VALUES
(1, 'Personal Loan', 50000.00, 25000.00, 2500.00, '2024-01-01', '2025-01-01', 'Active'),
(2, 'Emergency Loan', 20000.00, 10000.00, 2000.00, '2024-06-01', '2024-12-01', 'Active'),
(3, 'Housing Loan', 200000.00, 150000.00, 10000.00, '2023-01-01', '2028-01-01', 'Active'),
(4, 'Personal Loan', 30000.00, 0.00, 0.00, '2023-06-01', '2024-06-01', 'Closed'),
(5, 'Emergency Loan', 15000.00, 7500.00, 1500.00, '2024-03-01', '2024-09-01', 'Active'),
(6, 'Personal Loan', 25000.00, 12500.00, 2500.00, '2024-02-01', '2025-02-01', 'Active'),
(7, 'Emergency Loan', 10000.00, 0.00, 0.00, '2023-12-01', '2024-06-01', 'Closed'),
(8, 'Personal Loan', 35000.00, 17500.00, 3500.00, '2024-04-01', '2025-04-01', 'Active'),
(9, 'Emergency Loan', 12000.00, 6000.00, 2000.00, '2024-05-01', '2024-11-01', 'Active'),
(10, 'Personal Loan', 40000.00, 20000.00, 4000.00, '2024-01-15', '2025-01-15', 'Active');

-- Insert tax calculations
INSERT INTO tax_calculations (payroll_entry_id, taxable_income, withholding_tax, tax_period) VALUES
(1, 80000.00, 12000.00, '2024-12-01'),
(2, 48000.00, 6000.00, '2024-12-01'),
(3, 88000.00, 15000.00, '2024-12-01'),
(4, 48000.00, 6000.00, '2024-12-01'),
(5, 53000.00, 7500.00, '2024-12-01'),
(6, 37500.00, 4000.00, '2024-12-01'),
(7, 37000.00, 3800.00, '2024-12-01'),
(8, 38000.00, 4200.00, '2024-12-01'),
(9, 36500.00, 3600.00, '2024-12-01'),
(10, 37000.00, 3800.00, '2024-12-01');

-- Insert benefit dependents
INSERT INTO benefit_dependents (enrollment_id, dependent_name, relationship, birth_date) VALUES
(1, 'Jane Smith', 'Spouse', '1987-05-20'),
(1, 'John Smith Jr.', 'Child', '2010-08-15'),
(3, 'Lisa Brown', 'Spouse', '1985-12-10'),
(3, 'Michael Brown Jr.', 'Child', '2012-03-22'),
(4, 'Mark Davis', 'Spouse', '1992-07-08'),
(5, 'Susan Wilson', 'Spouse', '1989-11-30'),
(5, 'David Wilson Jr.', 'Child', '2015-04-18'),
(6, 'Mark Anderson', 'Spouse', '1994-02-14'),
(7, 'Mary Taylor', 'Spouse', '1991-09-25'),
(8, 'John Thomas', 'Spouse', '1993-06-12');

-- Insert benefit claims
INSERT INTO benefit_claims (employee_id, plan_id, claim_amount, claim_date, status) VALUES
(1, 1, 15000.00, '2024-01-15', 'Paid'),
(1, 1, 8500.00, '2024-06-20', 'Paid'),
(2, 2, 12000.00, '2024-03-10', 'Paid'),
(3, 1, 25000.00, '2024-02-28', 'Paid'),
(4, 3, 18000.00, '2024-05-15', 'Paid'),
(5, 2, 9500.00, '2024-04-22', 'Paid'),
(6, 4, 22000.00, '2024-07-08', 'Approved'),
(7, 1, 16000.00, '2024-08-12', 'Paid'),
(8, 2, 13500.00, '2024-09-05', 'Paid'),
(9, 3, 19500.00, '2024-10-18', 'Approved'),
(10, 4, 11000.00, '2024-11-25', 'Pending');

-- Insert user sessions (sample active sessions)
INSERT INTO user_sessions (user_id, session_token, expires_at, ip_address) VALUES
(1, 'sess_abc123def456', '2024-12-31 23:59:59', '192.168.1.100'),
(2, 'sess_xyz789uvw012', '2024-12-31 23:59:59', '192.168.1.101'),
(3, 'sess_mno345pqr678', '2024-12-31 23:59:59', '192.168.1.102');

-- Insert report templates
INSERT INTO report_templates (report_name, report_type, template_config) VALUES
('Monthly Payroll Report', 'payroll', '{"sections": ["employee_list", "salary_summary", "deductions", "net_pay"], "format": "pdf", "include_charts": true}'),
('Employee Benefits Summary', 'benefits', '{"sections": ["enrollment_status", "claims_summary", "cost_analysis"], "format": "excel", "group_by": "department"}'),
('Department Headcount Report', 'analytics', '{"sections": ["headcount_by_dept", "position_distribution", "salary_ranges"], "format": "pdf", "include_trends": true}'),
('Government Contributions Report', 'compliance', '{"sections": ["sss_summary", "philhealth_summary", "pagibig_summary"], "format": "excel", "period": "monthly"}'),
('Employee Performance Review', 'hr', '{"sections": ["employee_details", "performance_metrics", "goals", "recommendations"], "format": "pdf", "confidential": true}');

-- Insert audit logs
INSERT INTO audit_logs (user_id, action_type, table_affected, old_values, new_values) VALUES
(1, 'INSERT', 'employees', NULL, '{"employee_number": "EMP021", "first_name": "New", "last_name": "Employee"}'),
(2, 'UPDATE', 'payroll_entries', '{"basic_salary": 45000.00}', '{"basic_salary": 47000.00}'),
(3, 'DELETE', 'benefit_enrollments', '{"employee_id": 15, "plan_id": 2}', NULL),
(1, 'INSERT', 'salary_components', NULL, '{"employee_id": 5, "component_type": "Bonus", "amount": 5000.00}'),
(2, 'UPDATE', 'employee_details', '{"contact_number": "09171234575"}', '{"contact_number": "09171234576"}'),
(3, 'INSERT', 'benefit_claims', NULL, '{"employee_id": 8, "plan_id": 2, "claim_amount": 15000.00}'),
(1, 'UPDATE', 'departments', '{"budget_allocation": 2000000.00}', '{"budget_allocation": 2200000.00}'),
(2, 'INSERT', 'government_contributions', NULL, '{"employee_id": 12, "contribution_type": "SSS", "amount": 800.00}'),
(3, 'UPDATE', 'hmo_plans', '{"premium_amount": 1800.00}', '{"premium_amount": 1900.00}'),
(1, 'DELETE', 'employee_loans', '{"employee_id": 4, "loan_type": "Personal Loan"}', NULL);

-- Insert additional payroll entries for different periods
INSERT INTO payroll_entries (employee_id, period_id, basic_salary, overtime, deductions, net_pay) VALUES
(1, 22, 75000.00, 3000.00, 8200.00, 69800.00),
(2, 22, 45000.00, 1500.00, 5100.00, 41400.00),
(3, 22, 80000.00, 6000.00, 9000.00, 77000.00),
(4, 22, 45000.00, 1000.00, 5000.00, 41000.00),
(5, 22, 50000.00, 2500.00, 5700.00, 46800.00),
(6, 22, 35000.00, 2000.00, 4100.00, 32900.00),
(7, 22, 35000.00, 1500.00, 4000.00, 32500.00),
(8, 22, 35000.00, 2500.00, 4100.00, 33400.00),
(9, 22, 35000.00, 1000.00, 3950.00, 32050.00),
(10, 22, 35000.00, 1500.00, 4000.00, 32500.00);

-- Insert additional salary components
INSERT INTO salary_components (employee_id, component_type, amount, effective_date) VALUES
(11, 'Allowance', 2000.00, '2024-01-01'),
(12, 'Allowance', 2000.00, '2024-01-01'),
(13, 'Allowance', 3000.00, '2024-01-01'),
(14, 'Allowance', 4000.00, '2024-01-01'),
(15, 'Allowance', 5000.00, '2024-01-01'),
(16, 'Allowance', 3000.00, '2024-01-01'),
(17, 'Allowance', 3000.00, '2024-01-01'),
(18, 'Allowance', 4000.00, '2024-01-01'),
(19, 'Allowance', 6000.00, '2024-01-01'),
(20, 'Allowance', 3500.00, '2024-01-01'),
(1, 'Bonus', 10000.00, '2024-12-01'),
(3, 'Bonus', 15000.00, '2024-12-01'),
(19, 'Bonus', 20000.00, '2024-12-01');

-- Insert additional government contributions
INSERT INTO government_contributions (employee_id, period_id, contribution_type, amount) VALUES
(6, 23, 'SSS', 800.00),
(6, 23, 'PhilHealth', 600.00),
(6, 23, 'PagIBIG', 200.00),
(7, 23, 'SSS', 800.00),
(7, 23, 'PhilHealth', 600.00),
(7, 23, 'PagIBIG', 200.00),
(8, 23, 'SSS', 800.00),
(8, 23, 'PhilHealth', 600.00),
(8, 23, 'PagIBIG', 200.00),
(9, 23, 'SSS', 800.00),
(9, 23, 'PhilHealth', 600.00),
(9, 23, 'PagIBIG', 200.00),
(10, 23, 'SSS', 800.00),
(10, 23, 'PhilHealth', 600.00),
(10, 23, 'PagIBIG', 200.00);

-- Insert additional bank accounts
INSERT INTO bank_accounts (employee_id, bank_name, account_number) VALUES
(11, 'Security Bank', '9876543210'),
(12, 'EastWest Bank', '1122334455'),
(13, 'RCBC', '5566778899'),
(14, 'Chinabank', '9988776655'),
(15, 'UnionBank', '4433221100'),
(16, 'Landbank', '7788990011'),
(17, 'PNB', '2233445566'),
(18, 'RCBC', '6677889900'),
(19, 'BDO', '3344556677'),
(20, 'BPI', '8899001122');

-- Insert additional analytics metrics
INSERT INTO analytics_metrics (metric_type, metric_value, calculation_date, department_id) VALUES
('Radiology Headcount', 1.00, '2024-12-01', 6),
('Laboratory Headcount', 1.00, '2024-12-01', 7),
('Pharmacy Headcount', 1.00, '2024-12-01', 8),
('Administration Headcount', 1.00, '2024-12-01', 9),
('HR Headcount', 4.00, '2024-12-01', 10),
('Total Benefits Cost', 45000.00, '2024-12-01', NULL),
('Average Loan Amount', 35000.00, '2024-12-01', NULL),
('Active Claims', 8.00, '2024-12-01', NULL),
('Pending Claims', 1.00, '2024-12-01', NULL),
('Total Government Contributions', 15000.00, '2024-12-01', NULL);

-- Display completion message
SELECT 'Database reset and comprehensive sample data loaded successfully!' as Status;
