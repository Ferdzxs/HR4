<?php
// RBAC configuration and sidebar definitions based on PRD

class RBAC
{
    const ROLES = [
        'HR_MANAGER' => 'HR Manager',
        'COMPENSATION_MANAGER' => 'Compensation Manager',
        'BENEFITS_COORDINATOR' => 'Benefits Coordinator',
        'PAYROLL_ADMIN' => 'Payroll Administrator',
        'DEPT_HEAD' => 'Department Head',
        'EMPLOYEE' => 'Hospital Employee',
        'EXECUTIVE' => 'Hospital Management',
    ];

    // Session timeout settings (hours)
    const SESSION_TIMEOUT_HOURS = [
        'HR Manager' => 4,
        'Compensation Manager' => 4,
        'Benefits Coordinator' => 4,
        'Payroll Administrator' => 4,
        'Department Head' => 8,
        'Hospital Management' => 12,
        'Hospital Employee' => 24,
    ];

    // MFA requirements
    const MFA_REQUIRED = [
        'HR Manager',
        'Compensation Manager',
        'Benefits Coordinator',
        'Payroll Administrator',
    ];

    // Allowed modules per role (simplified access matrix)
    const ROLE_MODULES = [
        'HR Manager' => [
            'dashboard',
            'employees',
            'organization',
            'payroll',
            'compensation',
            'benefits',
            'analytics',
            'delegations',
            'bulk',
            'settings',
        ],
        'Compensation Manager' => [
            'dashboard',
            'employees-read',
            'compensation',
            'analytics',
        ],
        'Benefits Coordinator' => [
            'dashboard',
            'employees-read',
            'benefits',
            'analytics',
            'providers',
        ],
        'Payroll Administrator' => [
            'dashboard',
            'employees-read',
            'payroll',
            'analytics',
            'compliance',
        ],
        'Department Head' => [
            'dashboard',
            'team',
            'analytics',
            'approvals',
            'reports',
        ],
        'Hospital Employee' => [
            'dashboard',
            'profile',
            'payslips',
            'benefits-center',
            'leave',
            'documents',
            'help',
        ],
        'Hospital Management' => [
            'dashboard',
            'executive',
            'analytics',
            'reports',
        ],
    ];

    // Sidebar items per role
    const SIDEBAR_ITEMS = [
        'HR Manager' => [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'g'],
            ['id' => 'employees', 'label' => 'Employee Management', 'icon' => 'u'],
            ['id' => 'organization', 'label' => 'Department Control', 'icon' => 'o'],
            ['id' => 'payroll', 'label' => 'Payroll Overview', 'icon' => 'p'],
            ['id' => 'compensation', 'label' => 'Compensation', 'icon' => 'c'],
            ['id' => 'benefits', 'label' => 'Benefits Administration', 'icon' => 'b'],
            ['id' => 'documents', 'label' => 'Document Center', 'icon' => 'd'],
            ['id' => 'analytics', 'label' => 'Analytics Hub', 'icon' => 'a'],
            ['id' => 'delegations', 'label' => 'Delegations', 'icon' => 'r'],
            ['id' => 'bulk', 'label' => 'Bulk Operations', 'icon' => 'm'],
            ['id' => 'settings', 'label' => 'Settings', 'icon' => 's'],
        ],
        'Compensation Manager' => [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'g'],
            ['id' => 'compensation', 'label' => 'Compensation Planning', 'icon' => 'c'],
            ['id' => 'structures', 'label' => 'Salary Structures', 'icon' => 't'],
            ['id' => 'merit', 'label' => 'Merit Increases', 'icon' => 'm'],
            ['id' => 'equity', 'label' => 'Pay Equity Analysis', 'icon' => 'e'],
            ['id' => 'budget', 'label' => 'Budget Management', 'icon' => 'u'],
            ['id' => 'reports', 'label' => 'Reports Center', 'icon' => 'r'],
            ['id' => 'market', 'label' => 'Market Analysis', 'icon' => 'k'],
        ],
        'Benefits Coordinator' => [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'g'],
            ['id' => 'benefits', 'label' => 'HMO Management', 'icon' => 'b'],
            ['id' => 'claims', 'label' => 'Claims Processing', 'icon' => 'l'],
            ['id' => 'providers', 'label' => 'Provider Network', 'icon' => 'n'],
            ['id' => 'enrollment', 'label' => 'Enrollment Center', 'icon' => 'e'],
            ['id' => 'benefits-analytics', 'label' => 'Benefits Analytics', 'icon' => 'a'],
            ['id' => 'documents', 'label' => 'Document Library', 'icon' => 'd'],
            ['id' => 'member', 'label' => 'Member Services', 'icon' => 's'],
        ],
        'Payroll Administrator' => [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'g'],
            ['id' => 'payroll', 'label' => 'Payroll Processing', 'icon' => 'p'],
            ['id' => 'tax', 'label' => 'Tax Management', 'icon' => 'x'],
            ['id' => 'deductions', 'label' => 'Deductions Control', 'icon' => 'd'],
            ['id' => 'bank', 'label' => 'Bank Files', 'icon' => 'f'],
            ['id' => 'payslips', 'label' => 'Payslip Generation', 'icon' => 'y'],
            ['id' => 'reports', 'label' => 'Reports & Analytics', 'icon' => 'r'],
            ['id' => 'compliance', 'label' => 'Compliance Center', 'icon' => 'c'],
        ],
        'Department Head' => [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'g'],
            ['id' => 'team', 'label' => 'Team Management', 'icon' => 't'],
            ['id' => 'budget', 'label' => 'Budget Tracking', 'icon' => 'u'],
            ['id' => 'leave', 'label' => 'Leave Management', 'icon' => 'v'],
            ['id' => 'performance', 'label' => 'Performance Review', 'icon' => 'p'],
            ['id' => 'reports', 'label' => 'Department Reports', 'icon' => 'r'],
            ['id' => 'documents', 'label' => 'Document Access', 'icon' => 'd'],
        ],
        'Hospital Employee' => [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'g'],
            ['id' => 'profile', 'label' => 'My Profile', 'icon' => 'i'],
            ['id' => 'payslips', 'label' => 'Payslips', 'icon' => 'y'],
            ['id' => 'benefits-center', 'label' => 'Benefits Center', 'icon' => 'b'],
            ['id' => 'leave', 'label' => 'Leave Request', 'icon' => 'v'],
            ['id' => 'documents', 'label' => 'Documents', 'icon' => 'd'],
            ['id' => 'help', 'label' => 'Help Center', 'icon' => 'h'],
        ],
        'Hospital Management' => [
            ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'g'],
            ['id' => 'executive', 'label' => 'Executive Dashboard', 'icon' => 'e'],
            ['id' => 'strategy', 'label' => 'Strategic Planning', 'icon' => 's'],
            ['id' => 'cost', 'label' => 'Cost Analysis', 'icon' => 'c'],
            ['id' => 'workforce', 'label' => 'Workforce Analytics', 'icon' => 'w'],
            ['id' => 'compliance', 'label' => 'Compliance Overview', 'icon' => 'o'],
            ['id' => 'reports', 'label' => 'Executive Reports', 'icon' => 'r'],
        ],
    ];

    public static function getRoles()
    {
        return self::ROLES;
    }

    public static function getSessionTimeout($role)
    {
        return self::SESSION_TIMEOUT_HOURS[$role] ?? 8;
    }

    public static function isMfaRequired($role)
    {
        return in_array($role, self::MFA_REQUIRED);
    }

    public static function getRoleModules($role)
    {
        return self::ROLE_MODULES[$role] ?? [];
    }

    public static function getSidebarItems($role)
    {
        return self::SIDEBAR_ITEMS[$role] ?? [];
    }

    public static function hasPermission($role, $module)
    {
        $modules = self::getRoleModules($role);
        return in_array($module, $modules) || in_array($module . '-read', $modules);
    }

    public static function getDemoCredentials()
    {
        return [
            'HR Manager' => ['username' => 'hr.manager', 'password' => 'manager123'],
            'Compensation Manager' => ['username' => 'comp.manager', 'password' => 'comp123'],
            'Benefits Coordinator' => ['username' => 'benefits.coord', 'password' => 'benefits123'],
            'Payroll Administrator' => ['username' => 'payroll.admin', 'password' => 'payroll123'],
            'Department Head' => ['username' => 'dept.head', 'password' => 'dept123'],
            'Hospital Employee' => ['username' => 'employee', 'password' => 'emp123'],
            'Hospital Management' => ['username' => 'executive', 'password' => 'exec123'],
        ];
    }
}
?>