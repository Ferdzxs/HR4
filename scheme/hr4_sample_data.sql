-- =====================================================
-- HR4 Compensation & Intelligence Database Sample Data
-- =====================================================
-- This script inserts 7 realistic sample records into each table
-- to support all user roles in the HR system:
-- 1. HR Manager, 2. Compensation Manager, 3. Benefits Coordinator
-- 4. Payroll Administrator, 5. Department Head, 6. Hospital Employee, 7. Hospital Executive
-- =====================================================

-- Sample Data for HR4 Compensation & Intelligence Database
-- Adding 7 realistic entries per table to support all user roles

-- 1. Roles table
INSERT INTO roles (role_name, permissions) VALUES
('HR Manager', '{"dashboard":true,"employee_records":true,"leave_admin":true,"payroll_overview":true,"compensation_mgmt":true,"benefits_admin":true,"hr_analytics":true,"hr_documents":true,"delegations":true,"settings":true}'),
('Compensation Manager', '{"dashboard":true,"salary_structures":true,"merit_increases":true,"compensation_budgeting":true,"pay_equity_reports":true,"internal_benchmarking":true,"compensation_analytics":true}'),
('Benefits Coordinator', '{"dashboard":true,"hmo_benefits_mgmt":true,"claims_processing":true,"provider_directory":true,"enrollment_center":true,"benefits_analytics":true,"employee_benefits_support":true}'),
('Payroll Administrator', '{"dashboard":true,"payroll_processing":true,"deductions_contributions":true,"tax_compliance":true,"disbursements":true,"payslip_generation":true,"payroll_reports":true}'),
('Department Head', '{"dashboard":true,"team_records":true,"leave_approvals":true,"performance_review":true,"staffing_cost_overview":true,"department_reports":true,"department_documents":true}'),
('Hospital Employee', '{"home_page":true,"my_profile":true,"payslips":true,"leave_requests":true,"benefits_center":true,"my_documents":true,"help_center":true}'),
('Hospital Executive', '{"executive_dashboard":true,"workforce_analytics":true,"compensation_insights":true,"benefits_overview":true,"compliance_summary":true,"executive_reports":true}');

-- 2. Departments table
INSERT INTO departments (department_name, department_head_id, parent_department_id, budget_allocation) VALUES
('Emergency Department', NULL, NULL, 2500000.00),
('Surgery Department', NULL, NULL, 4200000.00),
('Pediatrics Department', NULL, NULL, 1800000.00),
('Cardiology Department', NULL, NULL, 3100000.00),
('Radiology Department', NULL, NULL, 1600000.00),
('Human Resources', NULL, NULL, 850000.00),
('Administration', NULL, NULL, 1200000.00);

-- 3. Salary grades table
INSERT INTO salary_grades (grade_level, min_salary, max_salary) VALUES
('SG-1', 25000.00, 35000.00),
('SG-2', 32000.00, 45000.00),
('SG-3', 42000.00, 60000.00),
('SG-4', 55000.00, 75000.00),
('SG-5', 70000.00, 95000.00),
('SG-6', 90000.00, 125000.00),
('SG-7', 120000.00, 180000.00);

-- 4. Positions table
INSERT INTO positions (position_title, salary_grade_id, job_description, reports_to_position_id) VALUES
('Chief Medical Officer', 7, 'Overall medical operations leadership', NULL),
('Department Head - Emergency', 6, 'Lead Emergency Department operations', 1),
('Staff Nurse', 2, 'Provide direct patient care', 2),
('Senior Physician', 5, 'Provide specialized medical care', 2),
('Medical Technologist', 3, 'Perform laboratory tests and analysis', 4),
('HR Manager', 5, 'Manage human resources operations', NULL),
('Administrative Assistant', 1, 'Provide administrative support', 6);

-- 5. Employees table
INSERT INTO employees (employee_number, first_name, last_name, department_id, position_id, hire_date, status) VALUES
('EMP001', 'Maria', 'Santos', 1, 2, '2022-01-15', 'Active'),
('EMP002', 'Juan', 'Dela Cruz', 2, 4, '2021-03-10', 'Active'),
('EMP003', 'Ana', 'Rodriguez', 1, 3, '2023-02-20', 'Active'),
('EMP004', 'Carlos', 'Garcia', 3, 4, '2020-06-05', 'Active'),
('EMP005', 'Lisa', 'Tan', 5, 5, '2022-09-12', 'Active'),
('EMP006', 'Roberto', 'Cruz', 6, 6, '2019-11-08', 'Active'),
('EMP007', 'Elena', 'Reyes', 7, 7, '2023-04-03', 'Active');

-- 6. Employee details table
INSERT INTO employee_details (employee_id, birth_date, gender, civil_status, contact_number, email, address, emergency_contact_name, emergency_contact_number, sss_no, philhealth_no, pagibig_no, tin_no, employment_type) VALUES
(1, '1985-07-15', 'F', 'Married', '09171234567', 'maria.santos@hospital.com', '123 Rizal St, Quezon City', 'Pedro Santos', '09181234567', '03-1234567-8', '12-345678901-2', '1234-5678-9012', '123-456-789-000', 'Regular'),
(2, '1978-12-03', 'M', 'Married', '09182345678', 'juan.delacruz@hospital.com', '456 Bonifacio Ave, Manila', 'Carmen Dela Cruz', '09192345678', '03-2345678-9', '12-456789012-3', '2345-6789-0123', '234-567-890-111', 'Regular'),
(3, '1992-04-22', 'F', 'Single', '09193456789', 'ana.rodriguez@hospital.com', '789 Malvar St, Makati', 'Rosa Rodriguez', '09203456789', '03-3456789-0', '12-567890123-4', '3456-7890-1234', '345-678-901-222', 'Regular'),
(4, '1980-09-18', 'M', 'Married', '09204567890', 'carlos.garcia@hospital.com', '321 Luna St, Pasig', 'Isabel Garcia', '09214567890', '03-4567890-1', '12-678901234-5', '4567-8901-2345', '456-789-012-333', 'Regular'),
(5, '1988-11-30', 'F', 'Single', '09215678901', 'lisa.tan@hospital.com', '654 Aguinaldo Ave, Taguig', 'William Tan', '09225678901', '03-5678901-2', '12-789012345-6', '5678-9012-3456', '567-890-123-444', 'Regular'),
(6, '1975-02-14', 'M', 'Married', '09226789012', 'roberto.cruz@hospital.com', '987 Mabini St, Manila', 'Gloria Cruz', '09236789012', '03-6789012-3', '12-890123456-7', '6789-0123-4567', '678-901-234-555', 'Regular'),
(7, '1995-06-08', 'F', 'Single', '09237890123', 'elena.reyes@hospital.com', '147 Katipunan Ave, Quezon City', 'Miguel Reyes', '09247890123', '03-7890123-4', '12-901234567-8', '7890-1234-5678', '789-012-345-666', 'Contractual');

-- 7. Users table
INSERT INTO users (username, password_hash, role_id, employee_id) VALUES
('msantos', '$2y$10$abcdefghijklmnopqrstuvwxyz', 5, 1),
('jdelacruz', '$2y$10$bcdefghijklmnopqrstuvwxyza', 7, 2),
('arodriguez', '$2y$10$cdefghijklmnopqrstuvwxyzab', 6, 3),
('cgarcia', '$2y$10$defghijklmnopqrstuvwxyzabc', 5, 4),
('ltan', '$2y$10$efghijklmnopqrstuvwxyzabcd', 3, 5),
('rcruz', '$2y$10$fghijklmnopqrstuvwxyzabcde', 1, 6),
('ereyes', '$2y$10$ghijklmnopqrstuvwxyzabcdef', 6, 7);

-- 8. User sessions table
INSERT INTO user_sessions (user_id, session_token, expires_at, ip_address) VALUES
(1, 'sess_001_abc123def456', '2024-09-19 08:00:00', '192.168.1.101'),
(2, 'sess_002_def456ghi789', '2024-09-19 09:30:00', '192.168.1.102'),
(3, 'sess_003_ghi789jkl012', '2024-09-19 10:15:00', '192.168.1.103'),
(4, 'sess_004_jkl012mno345', '2024-09-19 11:45:00', '192.168.1.104'),
(5, 'sess_005_mno345pqr678', '2024-09-19 13:20:00', '192.168.1.105'),
(6, 'sess_006_pqr678stu901', '2024-09-19 14:10:00', '192.168.1.106'),
(7, 'sess_007_stu901vwx234', '2024-09-19 15:30:00', '192.168.1.107');

-- 9. Employee documents table
INSERT INTO employee_documents (employee_id, document_type, file_path, upload_date) VALUES
(1, 'Employment Contract', '/documents/contracts/EMP001_contract.pdf', '2022-01-15'),
(2, 'Medical Certificate', '/documents/medical/EMP002_medical.pdf', '2023-07-20'),
(3, 'Training Certificate', '/documents/training/EMP003_cpr_training.pdf', '2023-08-15'),
(4, 'Performance Evaluation', '/documents/performance/EMP004_eval_2023.pdf', '2023-12-31'),
(5, 'License Renewal', '/documents/licenses/EMP005_medtech_license.pdf', '2023-09-01'),
(6, 'ID Card Copy', '/documents/ids/EMP006_company_id.pdf', '2023-01-10'),
(7, 'Background Check', '/documents/background/EMP007_nbi_clearance.pdf', '2023-04-01');

-- 10. Payroll periods table
INSERT INTO payroll_periods (period_start, period_end, status, processed_date) VALUES
('2024-08-01', '2024-08-15', 'Closed', '2024-08-16'),
('2024-08-16', '2024-08-31', 'Closed', '2024-09-01'),
('2024-09-01', '2024-09-15', 'Processed', '2024-09-16'),
('2024-09-16', '2024-09-30', 'Open', NULL),
('2024-10-01', '2024-10-15', 'Open', NULL),
('2024-10-16', '2024-10-31', 'Open', NULL),
('2024-11-01', '2024-11-15', 'Open', NULL);
-- 11. Payroll entries table
INSERT INTO payroll_entries (employee_id, period_id, basic_salary, overtime, deductions, net_pay) VALUES
(1, 1, 52000.00, 3500.00, 7800.00, 47700.00),
(2, 1, 78000.00, 5200.00, 12450.00, 70750.00),
(3, 1, 38000.00, 2100.00, 5985.00, 34115.00),
(4, 1, 72000.00, 4800.00, 11520.00, 65280.00),
(5, 1, 58000.00, 2900.00, 9135.00, 51765.00),
(6, 1, 75000.00, 0.00, 11250.00, 63750.00),
(7, 1, 28000.00, 1400.00, 4410.00, 24990.00);

-- 12. Salary components table
INSERT INTO salary_components (employee_id, component_type, amount, effective_date) VALUES
(1, 'Allowance', 5000.00, '2024-01-01'),
(2, 'HazardPay', 8000.00, '2024-01-01'),
(3, 'Allowance', 3000.00, '2024-01-01'),
(4, 'HazardPay', 7000.00, '2024-01-01'),
(5, 'Allowance', 4000.00, '2024-01-01'),
(6, 'Bonus', 15000.00, '2024-07-01'),
(7, 'Allowance', 2500.00, '2024-04-01');

-- 13. Employee loans table
INSERT INTO employee_loans (employee_id, loan_type, loan_amount, balance, monthly_deduction, start_date, end_date, status) VALUES
(1, 'Emergency Loan', 50000.00, 35000.00, 2500.00, '2023-06-01', '2025-05-31', 'Active'),
(2, 'Salary Loan', 100000.00, 75000.00, 5000.00, '2023-01-01', '2024-12-31', 'Active'),
(3, 'Multipurpose Loan', 25000.00, 15000.00, 1250.00, '2023-09-01', '2025-08-31', 'Active'),
(4, 'Educational Loan', 80000.00, 60000.00, 4000.00, '2022-06-01', '2024-05-31', 'Active'),
(5, 'Medical Loan', 30000.00, 20000.00, 1500.00, '2023-12-01', '2025-11-30', 'Active'),
(6, 'Housing Loan', 150000.00, 120000.00, 7500.00, '2022-01-01', '2026-12-31', 'Active'),
(7, 'Emergency Loan', 15000.00, 10000.00, 750.00, '2024-01-01', '2025-12-31', 'Active');

-- 14. Government contributions table
INSERT INTO government_contributions (employee_id, period_id, contribution_type, amount) VALUES
(1, 1, 'SSS', 1560.00),
(1, 1, 'PhilHealth', 937.50),
(1, 1, 'PagIBIG', 200.00),
(2, 1, 'SSS', 2340.00),
(2, 1, 'PhilHealth', 1406.25),
(2, 1, 'PagIBIG', 200.00),
(3, 1, 'SSS', 1140.00);

-- 15. Tax calculations table
INSERT INTO tax_calculations (payroll_entry_id, taxable_income, withholding_tax, tax_period) VALUES
(1, 48500.00, 4250.00, '2024-08-15'),
(2, 75200.00, 8750.00, '2024-08-15'),
(3, 37100.00, 2850.00, '2024-08-15'),
(4, 70800.00, 8150.00, '2024-08-15'),
(5, 56900.00, 6200.00, '2024-08-15'),
(6, 75000.00, 8500.00, '2024-08-15'),
(7, 27400.00, 1950.00, '2024-08-15');

-- 16. Bank accounts table
INSERT INTO bank_accounts (employee_id, bank_name, account_number) VALUES
(1, 'BPI', '1234567890'),
(2, 'BDO', '2345678901'),
(3, 'Metrobank', '3456789012'),
(4, 'Security Bank', '4567890123'),
(5, 'PNB', '5678901234'),
(6, 'UnionBank', '6789012345'),
(7, 'Landbank', '7890123456');

-- 17. Providers table
INSERT INTO providers (provider_name, contact_info) VALUES
('PhilCare Health Corporation', 'Address: Makati City, Contact: 02-8888-9999, Email: info@philcare.com.ph'),
('Maxicare Healthcare Corporation', 'Address: Pasig City, Contact: 02-7789-8888, Email: customercare@maxicare.com.ph'),
('Medicard Philippines', 'Address: Manila, Contact: 02-8531-8888, Email: info@medicard.com.ph'),
('Intellicare', 'Address: Mandaluyong City, Contact: 02-7750-2273, Email: customercare@intellicare.com.ph'),
('Caritas Health Shield', 'Address: Quezon City, Contact: 02-8441-7442, Email: info@caritashealth.ph'),
('HealthPlan Philippines', 'Address: Makati City, Contact: 02-8897-8888, Email: customerservice@healthplan.ph'),
('Asian Life and General Assurance', 'Address: Makati City, Contact: 02-8867-2267, Email: info@asianlife.com');

-- 18. HMO plans table
INSERT INTO hmo_plans (plan_name, provider_id, premium_amount, coverage_details) VALUES
('PhilCare Premium Plan', 1, 2500.00, 'Comprehensive medical coverage including hospitalization, outpatient, and emergency care up to PHP 150,000 annually'),
('Maxicare Gold Plan', 2, 3200.00, 'Full medical coverage with specialist consultations, diagnostic tests, and hospitalization up to PHP 200,000 annually'),
('Medicard Executive Plan', 3, 2800.00, 'Executive-level coverage including dental, optical, and wellness programs up to PHP 180,000 annually'),
('Intellicare Complete Care', 4, 2200.00, 'Complete healthcare package with preventive care and chronic disease management up to PHP 120,000 annually'),
('Caritas Total Health', 5, 1800.00, 'Basic comprehensive coverage including emergency and inpatient care up to PHP 100,000 annually'),
('HealthPlan Premier', 6, 3500.00, 'Premier healthcare coverage with international assistance up to PHP 250,000 annually'),
('Asian Life Health Plus', 7, 2000.00, 'Health insurance with life coverage component up to PHP 100,000 annually');

-- 19. Benefit enrollments table
INSERT INTO benefit_enrollments (employee_id, plan_id, enrollment_date, status) VALUES
(1, 2, '2022-01-15', 'Active'),
(2, 1, '2021-03-10', 'Active'),
(3, 5, '2023-02-20', 'Active'),
(4, 3, '2020-06-05', 'Active'),
(5, 4, '2022-09-12', 'Active'),
(6, 6, '2019-11-08', 'Active'),
(7, 7, '2023-04-03', 'Active');

-- 20. Benefit dependents table
INSERT INTO benefit_dependents (enrollment_id, dependent_name, relationship, birth_date) VALUES
(1, 'Pedro Santos Jr.', 'Son', '2010-05-15'),
(1, 'Maria Santos', 'Spouse', '1987-03-20'),
(2, 'Carmen Dela Cruz', 'Spouse', '1982-08-12'),
(2, 'Miguel Dela Cruz', 'Son', '2005-11-30'),
(4, 'Isabel Garcia', 'Spouse', '1985-04-18'),
(4, 'Sofia Garcia', 'Daughter', '2015-09-22'),
(6, 'Gloria Cruz', 'Spouse', '1978-12-05');
-- 21. Benefit claims table
INSERT INTO benefit_claims (employee_id, plan_id, claim_amount, claim_date, status) VALUES
(1, 2, 15000.00, '2024-07-15', 'Paid'),
(2, 1, 8500.00, '2024-08-20', 'Approved'),
(3, 5, 3200.00, '2024-09-05', 'Pending'),
(4, 3, 12000.00, '2024-06-10', 'Paid'),
(5, 4, 6800.00, '2024-08-25', 'Approved'),
(6, 6, 25000.00, '2024-05-30', 'Paid'),
(7, 7, 4500.00, '2024-09-12', 'Pending');

-- 22. Analytics metrics table
INSERT INTO analytics_metrics (metric_type, metric_value, calculation_date, department_id) VALUES
('Average Salary', 58500.00, '2024-09-01', 1),
('Headcount', 45.00, '2024-09-01', 1),
('Attrition Rate', 8.5, '2024-09-01', 2),
('Benefits Utilization', 78.2, '2024-09-01', 3),
('Overtime Hours', 240.5, '2024-09-01', 4),
('Training Hours', 120.0, '2024-09-01', 5),
('Performance Score', 4.2, '2024-09-01', 6);

-- 23. Report templates table
INSERT INTO report_templates (report_name, report_type, template_config) VALUES
('Monthly Payroll Summary', 'Payroll', '{"columns":["employee_name","basic_salary","overtime","deductions","net_pay"],"format":"PDF","schedule":"monthly"}'),
('Departmental Headcount Report', 'Analytics', '{"columns":["department","headcount","budget_utilization"],"format":"Excel","schedule":"quarterly"}'),
('Benefits Enrollment Report', 'Benefits', '{"columns":["employee_name","plan_name","enrollment_date","status"],"format":"CSV","schedule":"annual"}'),
('Leave Balance Report', 'Leave', '{"columns":["employee_name","leave_type","available_days","used_days"],"format":"PDF","schedule":"monthly"}'),
('Compensation Analysis Report', 'Compensation', '{"columns":["position","salary_range","current_avg","market_comparison"],"format":"Excel","schedule":"quarterly"}'),
('Performance Review Summary', 'Performance', '{"columns":["employee_name","rating","review_period","feedback_summary"],"format":"PDF","schedule":"annual"}'),
('Executive Dashboard Report', 'Executive', '{"columns":["kpi_name","current_value","target","variance"],"format":"Dashboard","schedule":"weekly"}');

-- 24. Audit logs table
INSERT INTO audit_logs (user_id, action_type, table_affected, old_values, new_values) VALUES
(6, 'UPDATE', 'employees', '{"salary":50000}', '{"salary":52000}'),
(1, 'INSERT', 'leave_requests', '{}', '{"employee_id":3,"start_date":"2024-09-20","end_date":"2024-09-22"}'),
(5, 'DELETE', 'benefit_claims', '{"claim_id":8,"status":"rejected"}', '{}'),
(2, 'UPDATE', 'employee_details', '{"contact_number":"09171234567"}', '{"contact_number":"09181234567"}'),
(3, 'INSERT', 'payroll_entries', '{}', '{"employee_id":7,"period_id":3,"net_pay":24990}'),
(4, 'UPDATE', 'hmo_plans', '{"premium_amount":2200}', '{"premium_amount":2400}'),
(6, 'INSERT', 'merit_increases', '{}', '{"employee_id":5,"recommended_amount":5000}');

-- 25. Leave policies table
INSERT INTO leave_policies (policy_name, leave_type, accrual_rate, max_balance) VALUES
('Annual Vacation Leave', 'Vacation', 1.25, 15.00),
('Sick Leave', 'Sick', 1.00, 12.00),
('Emergency Leave', 'Emergency', 0.50, 6.00),
('Maternity Leave', 'Maternity', 0.00, 105.00),
('Paternity Leave', 'Paternity', 0.00, 7.00),
('Bereavement Leave', 'Other', 0.00, 5.00),
('Study Leave', 'Other', 0.00, 30.00);

-- 26. Leave balances table
INSERT INTO leave_balances (employee_id, policy_id, available_days, used_days) VALUES
(1, 1, 12.5, 2.5),
(1, 2, 10.0, 2.0),
(2, 1, 15.0, 0.0),
(2, 2, 8.5, 3.5),
(3, 1, 8.0, 7.0),
(3, 2, 12.0, 0.0),
(4, 1, 14.0, 1.0);

-- 27. Leave requests table
INSERT INTO leave_requests (employee_id, policy_id, start_date, end_date, status, approver_id) VALUES
(3, 1, '2024-09-20', '2024-09-22', 'Pending', 1),
(1, 2, '2024-08-15', '2024-08-16', 'Approved', 1),
(5, 1, '2024-10-05', '2024-10-07', 'Pending', 4),
(2, 1, '2024-11-15', '2024-11-18', 'Pending', 2),
(4, 2, '2024-09-10', '2024-09-11', 'Approved', 4),
(7, 1, '2024-12-20', '2024-12-23', 'Pending', 6),
(6, 3, '2024-09-25', '2024-09-25', 'Approved', 2);

-- 28. Compensation plans table
INSERT INTO compensation_plans (plan_name, cycle_start, cycle_end, budget_allocated, budget_utilized) VALUES
('2024 Annual Merit Increase', '2024-01-01', '2024-12-31', 500000.00, 285000.00),
('Mid-Year Adjustment 2024', '2024-07-01', '2024-07-31', 150000.00, 125000.00),
('Performance Bonus Q3 2024', '2024-07-01', '2024-09-30', 200000.00, 180000.00),
('Promotion Salary Adjustment', '2024-01-01', '2024-12-31', 300000.00, 95000.00),
('Market Adjustment 2024', '2024-04-01', '2024-04-30', 180000.00, 165000.00),
('Retention Bonus Program', '2024-06-01', '2024-11-30', 250000.00, 75000.00),
('New Hire Signing Bonus', '2024-01-01', '2024-12-31', 100000.00, 45000.00);

-- 29. Merit increases table
INSERT INTO merit_increases (employee_id, plan_id, recommended_amount, approved_amount, approver_id, status) VALUES
(1, 1, 5000.00, 4500.00, 6, 'Approved'),
(2, 1, 8000.00, 7500.00, 6, 'Approved'),
(3, 1, 3500.00, 3500.00, 6, 'Approved'),
(4, 1, 7000.00, 6500.00, 6, 'Approved'),
(5, 1, 5500.00, 5000.00, 6, 'Approved'),
(6, 4, 10000.00, 10000.00, 2, 'Approved'),
(7, 5, 2500.00, 2000.00, 6, 'Pending');

-- 30. Pay equity reports table
INSERT INTO pay_equity_reports (generated_on, scope, findings) VALUES
('2024-03-31', 'Organization', '{"gender_pay_gap":0.08,"department_analysis":{"emergency":0.05,"surgery":0.12,"pediatrics":0.03},"recommendations":["Review surgery department compensation","Implement structured salary bands"]}'),
('2024-06-30', 'Department', '{"department":"Emergency","pay_variance":0.15,"position_analysis":{"nurses":0.08,"physicians":0.22},"action_items":["Standardize physician compensation","Review nurse pay scales"]}'),
('2024-09-15', 'Organization', '{"overall_equity_score":0.92,"improved_areas":["administrative","radiology"],"focus_areas":["surgery","cardiology"],"budget_impact":125000}'),
('2024-07-31', 'Department', '{"department":"Surgery","equity_improvements":0.18,"interventions_implemented":3,"employee_satisfaction_increase":0.25}'),
('2024-08-31', 'Organization', '{"quarterly_progress":0.15,"target_achievement":0.78,"remaining_budget":375000,"projected_completion":"2024-12-31"}'),
('2024-05-31', 'Department', '{"department":"Pediatrics","compliance_score":0.95,"minor_adjustments_needed":2,"estimated_cost":15000}'),
('2024-09-01', 'Organization', '{"annual_review_status":"in_progress","departments_completed":4,"remaining_departments":3,"overall_timeline":"on_track"}');
-- 31. Internal benchmarking table
INSERT INTO internal_benchmarking (department_id, avg_salary, comparison_notes) VALUES
(1, 58500.00, 'Emergency Department salaries are 8% above hospital average due to hazard pay and overtime requirements'),
(2, 72300.00, 'Surgery Department has highest average salaries reflecting specialized skills and critical nature of work'),
(3, 52100.00, 'Pediatrics Department salaries align with market standards for specialized pediatric care'),
(4, 68900.00, 'Cardiology Department compensation competitive with market rates for cardiac specialists'),
(5, 49200.00, 'Radiology Department salaries reflect technical expertise required for diagnostic imaging'),
(6, 64500.00, 'Human Resources Department compensation aligns with industry standards for healthcare HR'),
(7, 42800.00, 'Administration Department salaries competitive for healthcare administrative support roles');

-- 32. Performance reviews table
INSERT INTO performance_reviews (employee_id, reviewer_id, review_period_start, review_period_end, rating, feedback) VALUES
(1, 1, '2024-01-01', '2024-06-30', 4.2, 'Maria consistently demonstrates excellent clinical skills and leadership in emergency situations. Shows strong teamwork and mentoring abilities.'),
(2, 2, '2024-01-01', '2024-06-30', 4.5, 'Dr. Juan exceptional surgical skills and patient care. Excellent collaboration with surgical team and consistently meets quality metrics.'),
(3, 1, '2024-01-01', '2024-06-30', 3.8, 'Ana shows good nursing competencies and patient care skills. Recommended for additional training in critical care procedures.'),
(4, 4, '2024-01-01', '2024-06-30', 4.3, 'Dr. Carlos demonstrates excellent pediatric care and family communication skills. Strong contributor to department initiatives.'),
(5, 5, '2024-01-01', '2024-06-30', 4.0, 'Lisa maintains high accuracy in laboratory testing and shows good analytical skills. Reliable team member with good attendance.'),
(6, 2, '2024-01-01', '2024-06-30', 4.4, 'Roberto effectively manages HR operations and employee relations. Strong strategic thinking and policy implementation skills.'),
(7, 6, '2024-04-01', '2024-09-30', 3.7, 'Elena shows good administrative support skills and attention to detail. Recommended for additional software training.');

-- 33. Delegations table
INSERT INTO delegations (from_user_id, to_user_id, role_granted, start_date, end_date, status) VALUES
(6, 1, 'Acting HR Manager', '2024-09-20', '2024-09-27', 'Active'),
(1, 4, 'Department Head - Emergency', '2024-08-15', '2024-08-22', 'Expired'),
(2, 4, 'Senior Physician Authority', '2024-09-10', '2024-09-17', 'Expired'),
(4, 1, 'Pediatrics Coverage', '2024-07-01', '2024-07-15', 'Expired'),
(5, 3, 'Lab Supervisor', '2024-09-18', '2024-09-25', 'Active'),
(6, 2, 'Executive Reporting', '2024-09-01', '2024-09-30', 'Active'),
(1, 5, 'Benefits Coordinator Backup', '2024-09-15', '2024-09-22', 'Active');

-- 34. Benefit inquiries table
INSERT INTO benefit_inquiries (employee_id, subject, description, status, resolution_notes) VALUES
(3, 'HMO Coverage Question', 'Need clarification on maternity benefits coverage under current HMO plan', 'Resolved', 'Provided detailed maternity benefits information and coverage limits. Employee satisfied with explanation.'),
(1, 'Dependent Addition Request', 'Want to add newborn to health insurance coverage', 'Resolved', 'Processed dependent addition within 30-day enrollment window. Coverage effective immediately.'),
(7, 'Claims Processing Delay', 'Submitted medical claim 3 weeks ago but no update on status', 'In Progress', 'Following up with provider for claim status. Expected resolution within 5 business days.'),
(5, 'Prescription Coverage', 'Prescription medication not covered under current plan', 'Resolved', 'Explained formulary coverage and provided alternative medication options covered under plan.'),
(2, 'Annual Physical Exam', 'Questions about annual physical exam benefits and approved providers', 'Resolved', 'Provided list of approved providers and explained preventive care benefits with no co-pay.'),
(4, 'Specialist Referral Process', 'Need guidance on specialist referral requirements', 'Closed', 'Explained referral process and provided necessary forms. Employee successfully obtained referral.'),
(6, 'Wellness Program Participation', 'Interest in joining company wellness program and incentives', 'Open', 'Scheduling orientation session for wellness program enrollment and benefits explanation.');

-- 35. Payslips table
INSERT INTO payslips (payroll_entry_id, file_path, issue_date) VALUES
(1, '/payslips/2024/08/EMP001_payslip_20240815.pdf', '2024-08-16'),
(2, '/payslips/2024/08/EMP002_payslip_20240815.pdf', '2024-08-16'),
(3, '/payslips/2024/08/EMP003_payslip_20240815.pdf', '2024-08-16'),
(4, '/payslips/2024/08/EMP004_payslip_20240815.pdf', '2024-08-16'),
(5, '/payslips/2024/08/EMP005_payslip_20240815.pdf', '2024-08-16'),
(6, '/payslips/2024/08/EMP006_payslip_20240815.pdf', '2024-08-16'),
(7, '/payslips/2024/08/EMP007_payslip_20240815.pdf', '2024-08-16');

-- 36. Help articles table
INSERT INTO help_articles (title, content) VALUES
('How to Submit Leave Requests', 'Step-by-step guide: 1. Log into employee portal 2. Navigate to Leave Requests 3. Select leave type and dates 4. Submit for approval 5. Track status in dashboard'),
('Understanding Your Payslip', 'Your payslip contains: Basic Salary, Overtime, Allowances, Deductions (SSS, PhilHealth, PagIBIG, Withholding Tax), Loans, and Net Pay. Contact payroll for clarifications.'),
('HMO Benefits Overview', 'Your HMO coverage includes: Inpatient care, Outpatient consultations, Emergency services, Laboratory tests, Preventive care. Check provider directory for approved facilities.'),
('Password Reset Instructions', 'Forgot your password? Click "Forgot Password" on login page, enter your username, check email for reset link, create new password following security requirements.'),
('Updating Personal Information', 'To update personal details: Go to My Profile, click Edit, update required fields, save changes. Note: Some changes require HR approval and supporting documents.'),
('Benefits Enrollment Guide', 'Open enrollment period: January 1-31 annually. Mid-year changes allowed for qualifying life events (marriage, birth, job change). Contact Benefits team for assistance.'),
('Performance Review Process', 'Annual reviews conducted January-March. Self-assessment due by January 15, supervisor review by February 15, calibration sessions in March, final ratings by March 31.');

-- 37. Support requests table
INSERT INTO support_requests (employee_id, category, subject, description, status) VALUES
(3, 'Payroll', 'Missing Overtime Pay', 'Overtime hours from last week not reflected in current payslip. Worked 8 extra hours on August 10-11.', 'In Progress'),
(1, 'Benefits', 'Claim Reimbursement Status', 'Submitted medical reimbursement claim 2 weeks ago. Need status update and expected processing time.', 'Resolved'),
(7, 'General', 'Login Issues', 'Unable to access employee portal after password reset. Error message appears when trying to log in.', 'Open'),
(5, 'Compensation', 'Salary Grade Question', 'Want to understand promotion requirements and salary grade advancement criteria for medical technologists.', 'Resolved'),
(2, 'Payroll', 'Tax Withholding Adjustment', 'Need to update tax withholding due to additional dependent. Have new BIR 2316 form to submit.', 'In Progress'),
(4, 'Benefits', 'Maternity Benefits', 'Wife is expecting. Need information on paternity leave entitlements and required documentation for claims.', 'Resolved'),
(6, 'General', 'Document Access', 'Need access to updated employee handbook and policy documents. Current version in system appears outdated.', 'Open');

-- 38. Compensation insights table
INSERT INTO compensation_insights (department_id, total_salary, avg_salary, equity_index, snapshot_date) VALUES
(1, 2,925,000.00, 58500.00, 0.92, '2024-09-01'),
(2, 4,338,000.00, 72300.00, 0.88, '2024-09-01'),
(3, 1,563,000.00, 52100.00, 0.95, '2024-09-01'),
(4, 2,756,000.00, 68900.00, 0.90, '2024-09-01'),
(5, 1,476,000.00, 49200.00, 0.94, '2024-09-01'),
(6, 774,000.00, 64500.00, 0.96, '2024-09-01'),
(7, 513,600.00, 42800.00, 0.93, '2024-09-01');

-- =====================================================
-- FOREIGN KEY CONSTRAINTS (to be added after initial data load)
-- =====================================================
ALTER TABLE departments ADD FOREIGN KEY (department_head_id) REFERENCES employees(id);
ALTER TABLE users ADD FOREIGN KEY (role_id) REFERENCES roles(id);
ALTER TABLE users ADD FOREIGN KEY (employee_id) REFERENCES employees(id);
ALTER TABLE employees ADD FOREIGN KEY (department_id) REFERENCES departments(id);
ALTER TABLE employees ADD FOREIGN KEY (position_id) REFERENCES positions(id);
ALTER TABLE employee_details ADD FOREIGN KEY (employee_id) REFERENCES employees(id);

-- =====================================================
-- Data Summary
-- =====================================================
-- Total records inserted: 266 records across 38 tables
-- Departments: 7 (Emergency, Surgery, Pediatrics, Cardiology, Radiology, HR, Administration)
-- Employees: 7 (representing different roles and departments)
-- Salary Grades: 7 (SG-1 to SG-7 covering PHP 25,000 to PHP 180,000)
-- User Roles: 7 (covering all system user types)
-- HMO Plans: 7 (from different providers with various coverage levels)
-- Leave Policies: 7 (including vacation, sick, emergency, maternity, paternity)
-- Compensation Plans: 7 (merit increases, bonuses, adjustments)
-- =====================================================