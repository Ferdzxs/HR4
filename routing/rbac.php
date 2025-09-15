<?php
// RBAC configuration and sidebar definitions based on PRD
// This file contains the role definitions and sidebar items

$ROLES = [
    'HR_MANAGER' => 'HR Manager',
    'COMPENSATION_MANAGER' => 'Compensation Manager',
    'BENEFITS_COORDINATOR' => 'Benefits Coordinator',
    'PAYROLL_ADMIN' => 'Payroll Administrator',
    'DEPT_HEAD' => 'Department Head',
    'EMPLOYEE' => 'Hospital Employee',
    'EXECUTIVE' => 'Hospital Management',
];

// Session timeout settings (hours)
$SESSION_TIMEOUT_HOURS = [
    $ROLES['HR_MANAGER'] => 4,
    $ROLES['COMPENSATION_MANAGER'] => 4,
    $ROLES['BENEFITS_COORDINATOR'] => 4,
    $ROLES['PAYROLL_ADMIN'] => 4,
    $ROLES['DEPT_HEAD'] => 8,
    $ROLES['EXECUTIVE'] => 12,
    $ROLES['EMPLOYEE'] => 24,
];

// MFA requirements
$MFA_REQUIRED = [
    $ROLES['HR_MANAGER'],
    $ROLES['COMPENSATION_MANAGER'],
    $ROLES['BENEFITS_COORDINATOR'],
    $ROLES['PAYROLL_ADMIN'],
];

// Allowed modules per role (simplified access matrix)
$ROLE_MODULES = [
    $ROLES['HR_MANAGER'] => [
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
    $ROLES['COMPENSATION_MANAGER'] => [
        'dashboard',
        'employees-read',
        'compensation',
        'analytics',
    ],
    $ROLES['BENEFITS_COORDINATOR'] => [
        'dashboard',
        'employees-read',
        'benefits',
        'analytics',
        'providers',
    ],
    $ROLES['PAYROLL_ADMIN'] => [
        'dashboard',
        'employees-read',
        'payroll',
        'analytics',
        'compliance',
    ],
    $ROLES['DEPT_HEAD'] => ['dashboard', 'team', 'analytics', 'approvals', 'reports'],
    $ROLES['EMPLOYEE'] => [
        'dashboard',
        'profile',
        'payslips',
        'benefits-center',
        'leave',
        'documents',
        'help',
    ],
    $ROLES['EXECUTIVE'] => ['dashboard', 'executive', 'analytics', 'reports'],
];

// Sidebar items per role
$SIDEBAR_ITEMS = [
    $ROLES['HR_MANAGER'] => [
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
    $ROLES['COMPENSATION_MANAGER'] => [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'g'],
        ['id' => 'compensation', 'label' => 'Compensation Planning', 'icon' => 'c'],
        ['id' => 'structures', 'label' => 'Salary Structures', 'icon' => 't'],
        ['id' => 'merit', 'label' => 'Merit Increases', 'icon' => 'm'],
        ['id' => 'equity', 'label' => 'Pay Equity Analysis', 'icon' => 'e'],
        ['id' => 'budget', 'label' => 'Budget Management', 'icon' => 'u'],
        ['id' => 'reports', 'label' => 'Reports Center', 'icon' => 'r'],
        ['id' => 'market', 'label' => 'Market Analysis', 'icon' => 'k'],
    ],
    $ROLES['BENEFITS_COORDINATOR'] => [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'g'],
        ['id' => 'benefits', 'label' => 'HMO Management', 'icon' => 'b'],
        ['id' => 'claims', 'label' => 'Claims Processing', 'icon' => 'l'],
        ['id' => 'providers', 'label' => 'Provider Network', 'icon' => 'n'],
        ['id' => 'enrollment', 'label' => 'Enrollment Center', 'icon' => 'e'],
        ['id' => 'benefits-analytics', 'label' => 'Benefits Analytics', 'icon' => 'a'],
        ['id' => 'documents', 'label' => 'Document Library', 'icon' => 'd'],
        ['id' => 'member', 'label' => 'Member Services', 'icon' => 's'],
    ],
    $ROLES['PAYROLL_ADMIN'] => [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'g'],
        ['id' => 'payroll', 'label' => 'Payroll Processing', 'icon' => 'p'],
        ['id' => 'tax', 'label' => 'Tax Management', 'icon' => 'x'],
        ['id' => 'deductions', 'label' => 'Deductions Control', 'icon' => 'd'],
        ['id' => 'bank', 'label' => 'Bank Files', 'icon' => 'f'],
        ['id' => 'payslips', 'label' => 'Payslip Generation', 'icon' => 'y'],
        ['id' => 'reports', 'label' => 'Reports & Analytics', 'icon' => 'r'],
        ['id' => 'compliance', 'label' => 'Compliance Center', 'icon' => 'c'],
    ],
    $ROLES['DEPT_HEAD'] => [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'g'],
        ['id' => 'team', 'label' => 'Team Management', 'icon' => 't'],
        ['id' => 'budget', 'label' => 'Budget Tracking', 'icon' => 'u'],
        ['id' => 'leave', 'label' => 'Leave Management', 'icon' => 'v'],
        ['id' => 'performance', 'label' => 'Performance Review', 'icon' => 'p'],
        ['id' => 'reports', 'label' => 'Department Reports', 'icon' => 'r'],
        ['id' => 'documents', 'label' => 'Document Access', 'icon' => 'd'],
    ],
    $ROLES['EMPLOYEE'] => [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'g'],
        ['id' => 'profile', 'label' => 'My Profile', 'icon' => 'i'],
        ['id' => 'payslips', 'label' => 'Payslips', 'icon' => 'y'],
        ['id' => 'benefits-center', 'label' => 'Benefits Center', 'icon' => 'b'],
        ['id' => 'leave', 'label' => 'Leave Request', 'icon' => 'v'],
        ['id' => 'documents', 'label' => 'Documents', 'icon' => 'd'],
        ['id' => 'help', 'label' => 'Help Center', 'icon' => 'h'],
    ],
    $ROLES['EXECUTIVE'] => [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'g'],
        ['id' => 'executive', 'label' => 'Executive Dashboard', 'icon' => 'e'],
        ['id' => 'strategy', 'label' => 'Strategic Planning', 'icon' => 's'],
        ['id' => 'cost', 'label' => 'Cost Analysis', 'icon' => 'c'],
        ['id' => 'workforce', 'label' => 'Workforce Analytics', 'icon' => 'w'],
        ['id' => 'compliance', 'label' => 'Compliance Overview', 'icon' => 'o'],
        ['id' => 'reports', 'label' => 'Executive Reports', 'icon' => 'r'],
    ],
];
?>
