<?php
// Database helper class for HR4 system
class DatabaseHelper
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Generic query execution
    public function query($sql, $params = [])
    {
        try {
            if ($this->db instanceof PDO) {
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                return $stmt;
            } else {
                $stmt = $this->db->prepare($sql);
                if (!empty($params)) {
                    $types = str_repeat('s', count($params));
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                return $stmt;
            }
        } catch (Exception $e) {
            error_log("Database query error: " . $e->getMessage());
            return false;
        }
    }

    // Fetch all results
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        if (!$stmt)
            return [];

        if ($this->db instanceof PDO) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    // Fetch single result
    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        if (!$stmt)
            return null;

        if ($this->db instanceof PDO) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }
    }

    // Get dashboard statistics
    public function getDashboardStats()
    {
        $stats = [];

        // Total employees
        $stats['total_employees'] = $this->fetchOne("SELECT COUNT(*) as count FROM employees WHERE status = 'Active'")['count'] ?? 0;

        // Total departments
        $stats['total_departments'] = $this->fetchOne("SELECT COUNT(*) as count FROM departments")['count'] ?? 0;

        // Total payroll entries (current period)
        $stats['payroll_entries'] = $this->fetchOne("
            SELECT COUNT(*) as count 
            FROM payroll_entries pe 
            JOIN payroll_periods pp ON pe.period_id = pp.id 
            WHERE pp.status = 'Processed' 
            ORDER BY pp.period_end DESC 
            LIMIT 1
        ")['count'] ?? 0;

        // Active benefits
        $stats['active_benefits'] = $this->fetchOne("SELECT COUNT(*) as count FROM benefit_enrollments WHERE status = 'Active'")['count'] ?? 0;

        return $stats;
    }

    // Get employees with details
    public function getEmployees($limit = 50, $offset = 0, $search = '', $departmentId = null)
    {
        $searchCondition = $search ? "AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_number LIKE ?)" : "";
        $deptCondition = $departmentId ? "AND e.department_id = ?" : "";
        $searchParams = $search ? ["%$search%", "%$search%", "%$search%"] : [];

        $sql = "
            SELECT e.*, d.department_name, p.position_title, ed.email, ed.contact_number
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            LEFT JOIN employee_details ed ON e.id = ed.employee_id
            WHERE e.status = 'Active' $searchCondition $deptCondition
            ORDER BY e.first_name, e.last_name
            LIMIT ? OFFSET ?
        ";

        $params = $searchParams;
        if ($departmentId) {
            $params[] = $departmentId;
        }
        $params = array_merge($params, [$limit, $offset]);
        return $this->fetchAll($sql, $params);
    }

    // Get departments with heads
    public function getDepartments()
    {
        $sql = "
            SELECT d.*, 
                   CONCAT(e.first_name, ' ', e.last_name) as head_name,
                   COUNT(emp.id) as employee_count
            FROM departments d
            LEFT JOIN employees e ON d.department_head_id = e.id
            LEFT JOIN employees emp ON emp.department_id = d.id AND emp.status = 'Active'
            GROUP BY d.id
            ORDER BY d.department_name
        ";
        return $this->fetchAll($sql);
    }

    // Get payroll data
    public function getPayrollData($periodId = null)
    {
        if (!$periodId) {
            $periodId = $this->fetchOne("SELECT id FROM payroll_periods ORDER BY period_end DESC LIMIT 1")['id'];
        }

        $sql = "
            SELECT pe.*, e.employee_number, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                   d.department_name, pp.period_start, pp.period_end, pp.status as period_status
            FROM payroll_entries pe
            JOIN employees e ON pe.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            JOIN payroll_periods pp ON pe.period_id = pp.id
            WHERE pe.period_id = ?
            ORDER BY e.first_name, e.last_name
        ";
        return $this->fetchAll($sql, [$periodId]);
    }

    // Get benefits data
    public function getBenefitsData()
    {
        $sql = "
            SELECT be.*, e.employee_number, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                   hp.plan_name, hp.premium_amount, p.provider_name
            FROM benefit_enrollments be
            JOIN employees e ON be.employee_id = e.id
            JOIN hmo_plans hp ON be.plan_id = hp.id
            JOIN providers p ON hp.provider_id = p.id
            WHERE be.status = 'Active'
            ORDER BY e.first_name, e.last_name
        ";
        return $this->fetchAll($sql);
    }

    // Get analytics data
    public function getAnalyticsData()
    {
        $sql = "
            SELECT metric_type, metric_value, calculation_date, department_id, d.department_name
            FROM analytics_metrics am
            LEFT JOIN departments d ON am.department_id = d.id
            ORDER BY calculation_date DESC, metric_type
        ";
        return $this->fetchAll($sql);
    }

    // Get recent activities
    public function getRecentActivities($limit = 10)
    {
        $sql = "
            SELECT al.*, u.username, al.timestamp
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.timestamp DESC
            LIMIT ?
        ";
        return $this->fetchAll($sql, [$limit]);
    }

    // Get audit logs with pagination
    public function getAuditLogs($limit = 50, $offset = 0, $actionType = null)
    {
        $condition = $actionType ? "WHERE al.action_type = ?" : "";
        $params = $actionType ? [$actionType] : [];
        $params = array_merge($params, [$limit, $offset]);

        $sql = "
            SELECT al.*, u.username, r.role_name as user_role
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            LEFT JOIN roles r ON u.role_id = r.id
            $condition
            ORDER BY al.timestamp DESC
            LIMIT ? OFFSET ?
        ";
        return $this->fetchAll($sql, $params);
    }

    // Get department budget utilization
    public function getDepartmentBudgetUtilization()
    {
        $sql = "
            SELECT
                d.id,
                d.department_name,
                d.budget_allocation,
                COUNT(e.id) as employee_count,
                COALESCE(SUM(pe.basic_salary), 0) as total_salary,
                CASE
                    WHEN d.budget_allocation > 0 THEN (COALESCE(SUM(pe.basic_salary), 0) / d.budget_allocation) * 100
                    ELSE 0
                END as utilization_percentage
            FROM departments d
            LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'Active'
            LEFT JOIN payroll_entries pe ON e.id = pe.employee_id
            GROUP BY d.id, d.department_name, d.budget_allocation
            ORDER BY utilization_percentage DESC
        ";
        return $this->fetchAll($sql);
    }

    // Get compensation data with salary grades
    public function getCompensationData()
    {
        $sql = "
            SELECT
                e.id as employee_id,
                e.employee_number,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                d.department_name,
                p.position_title,
                sg.grade_level,
                sg.min_salary,
                sg.max_salary,
                pe.basic_salary,
                pe.overtime,
                pe.deductions,
                pe.net_pay
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            LEFT JOIN salary_grades sg ON p.salary_grade_id = sg.id
            LEFT JOIN payroll_entries pe ON e.id = pe.employee_id
            WHERE e.status = 'Active'
            ORDER BY e.first_name, e.last_name
        ";
        return $this->fetchAll($sql);
    }

    // Get user sessions with user details
    public function getUserSessions($userId = null)
    {
        $condition = $userId ? "WHERE us.user_id = ?" : "";
        $params = $userId ? [$userId] : [];

        $sql = "
            SELECT us.*, u.username, r.role_name as user_role
            FROM user_sessions us
            JOIN users u ON us.user_id = u.id
            LEFT JOIN roles r ON u.role_id = r.id
            $condition
            ORDER BY us.expires_at DESC
        ";
        return $this->fetchAll($sql, $params);
    }

    // Get positions with salary grade information
    public function getPositions()
    {
        $sql = "
            SELECT p.*, sg.grade_level, sg.min_salary, sg.max_salary
            FROM positions p
            JOIN salary_grades sg ON p.salary_grade_id = sg.id
            ORDER BY p.position_title
        ";
        return $this->fetchAll($sql);
    }

    // Get employee documents with employee details
    public function getEmployeeDocuments($employeeId = null)
    {
        $condition = $employeeId ? "WHERE ed.employee_id = ?" : "";
        $params = $employeeId ? [$employeeId] : [];

        $sql = "
            SELECT ed.*, e.employee_number, CONCAT(e.first_name, ' ', e.last_name) as employee_name
            FROM employee_documents ed
            JOIN employees e ON ed.employee_id = e.id
            $condition
            ORDER BY ed.upload_date DESC
        ";
        return $this->fetchAll($sql, $params);
    }

    // Get HMO plans with provider information
    public function getHMOPlans()
    {
        $sql = "
            SELECT hp.*, p.provider_name
            FROM hmo_plans hp
            JOIN providers p ON hp.provider_id = p.id
            ORDER BY hp.plan_name
        ";
        return $this->fetchAll($sql);
    }

    // Get benefit claims with employee and plan information
    public function getBenefitClaims($status = null)
    {
        $condition = $status ? "WHERE bc.status = ?" : "";
        $params = $status ? [$status] : [];

        $sql = "
            SELECT bc.*, e.employee_number, CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                   hp.plan_name, p.provider_name
            FROM benefit_claims bc
            JOIN employees e ON bc.employee_id = e.id
            JOIN hmo_plans hp ON bc.plan_id = hp.id
            JOIN providers p ON hp.provider_id = p.id
            $condition
            ORDER BY bc.claim_date DESC
        ";
        return $this->fetchAll($sql, $params);
    }

    // Get payroll periods
    public function getPayrollPeriods()
    {
        $sql = "
            SELECT * FROM payroll_periods
            ORDER BY period_end DESC
        ";
        return $this->fetchAll($sql);
    }

    // Get government contributions for a period
    public function getGovernmentContributions($periodId)
    {
        $sql = "
            SELECT gc.*, e.employee_number, CONCAT(e.first_name, ' ', e.last_name) as employee_name
            FROM government_contributions gc
            JOIN employees e ON gc.employee_id = e.id
            WHERE gc.period_id = ?
            ORDER BY e.first_name, e.last_name
        ";
        return $this->fetchAll($sql, [$periodId]);
    }

    // Get employee loans
    public function getEmployeeLoans($employeeId = null)
    {
        $condition = $employeeId ? "WHERE el.employee_id = ?" : "";
        $params = $employeeId ? [$employeeId] : [];

        $sql = "
            SELECT el.*, e.employee_number, CONCAT(e.first_name, ' ', e.last_name) as employee_name
            FROM employee_loans el
            JOIN employees e ON el.employee_id = e.id
            $condition
            ORDER BY el.start_date DESC
        ";
        return $this->fetchAll($sql, $params);
    }

    // Get bank accounts
    public function getBankAccounts($employeeId = null)
    {
        $condition = $employeeId ? "WHERE ba.employee_id = ?" : "";
        $params = $employeeId ? [$employeeId] : [];

        $sql = "
            SELECT ba.*, e.employee_number, CONCAT(e.first_name, ' ', e.last_name) as employee_name
            FROM bank_accounts ba
            JOIN employees e ON ba.employee_id = e.id
            $condition
            ORDER BY e.first_name, e.last_name
        ";
        return $this->fetchAll($sql, $params);
    }

    // Get tax calculations
    public function getTaxCalculations($payrollEntryId = null)
    {
        $condition = $payrollEntryId ? "WHERE tc.payroll_entry_id = ?" : "";
        $params = $payrollEntryId ? [$payrollEntryId] : [];

        $sql = "
            SELECT tc.*, pe.employee_id, e.employee_number, CONCAT(e.first_name, ' ', e.last_name) as employee_name
            FROM tax_calculations tc
            JOIN payroll_entries pe ON tc.payroll_entry_id = pe.id
            JOIN employees e ON pe.employee_id = e.id
            $condition
            ORDER BY tc.tax_period DESC
        ";
        return $this->fetchAll($sql, $params);
    }

    // Get salary components
    public function getSalaryComponents($employeeId = null)
    {
        $condition = $employeeId ? "WHERE sc.employee_id = ?" : "";
        $params = $employeeId ? [$employeeId] : [];

        $sql = "
            SELECT sc.*, e.employee_number, CONCAT(e.first_name, ' ', e.last_name) as employee_name
            FROM salary_components sc
            JOIN employees e ON sc.employee_id = e.id
            $condition
            ORDER BY sc.effective_date DESC
        ";
        return $this->fetchAll($sql, $params);
    }

    // Get employee performance metrics
    public function getEmployeePerformanceMetrics()
    {
        $sql = "
            SELECT
                e.id,
                e.employee_number,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                d.department_name,
                p.position_title,
                pe.basic_salary,
                pe.overtime,
                pe.net_pay,
                (pe.overtime / pe.basic_salary * 100) as overtime_percentage
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            LEFT JOIN payroll_entries pe ON e.id = pe.employee_id
            WHERE e.status = 'Active'
            ORDER BY pe.net_pay DESC
        ";
        return $this->fetchAll($sql);
    }

    // Get system statistics
    public function getSystemStats()
    {
        $stats = [];

        // User statistics
        $stats['total_users'] = $this->fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
        $stats['active_sessions'] = $this->fetchOne("SELECT COUNT(*) as count FROM user_sessions WHERE expires_at >= NOW()")['count'] ?? 0;

        // Role statistics
        $stats['total_roles'] = $this->fetchOne("SELECT COUNT(*) as count FROM roles")['count'] ?? 0;

        // Department statistics
        $stats['total_departments'] = $this->fetchOne("SELECT COUNT(*) as count FROM departments")['count'] ?? 0;

        // Employee statistics
        $stats['total_employees'] = $this->fetchOne("SELECT COUNT(*) as count FROM employees WHERE status = 'Active'")['count'] ?? 0;

        // Payroll statistics
        $stats['total_payroll_entries'] = $this->fetchOne("SELECT COUNT(*) as count FROM payroll_entries")['count'] ?? 0;
        $stats['total_payroll_amount'] = $this->fetchOne("SELECT SUM(net_pay) as total FROM payroll_entries")['total'] ?? 0;

        // Benefits statistics
        $stats['total_benefits'] = $this->fetchOne("SELECT COUNT(*) as count FROM benefit_enrollments WHERE status = 'Active'")['count'] ?? 0;
        $stats['total_benefit_amount'] = $this->fetchOne("SELECT SUM(hp.premium_amount) as total FROM benefit_enrollments be JOIN hmo_plans hp ON be.plan_id = hp.id WHERE be.status = 'Active'")['total'] ?? 0;

        // Document statistics
        $stats['total_documents'] = $this->fetchOne("SELECT COUNT(*) as count FROM employee_documents")['count'] ?? 0;
        $stats['recent_documents'] = $this->fetchOne(
            "SELECT COUNT(*) as count FROM employee_documents WHERE upload_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"
        )['count'] ?? 0;

        // Audit log statistics
        $stats['total_audit_logs'] = $this->fetchOne("SELECT COUNT(*) as count FROM audit_logs")['count'] ?? 0;

        // Financial statistics
        $stats['total_loans'] = $this->fetchOne("SELECT COUNT(*) as count FROM employee_loans WHERE status = 'Active'")['count'] ?? 0;
        $stats['total_loan_amount'] = $this->fetchOne("SELECT SUM(loan_amount) as total FROM employee_loans WHERE status = 'Active'")['total'] ?? 0;

        return $stats;
    }

    // Delete a document by id
    public function deleteDocumentById($documentId)
    {
        $this->query("DELETE FROM employee_documents WHERE id = ?", [$documentId]);
    }

    // Soft-delete (deactivate) an employee by id to keep referential integrity
    public function deactivateEmployeeById($employeeId)
    {
        $this->query("UPDATE employees SET status = 'Inactive' WHERE id = ?", [$employeeId]);
    }
}
?>