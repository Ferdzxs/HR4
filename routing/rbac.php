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
        'payroll',
        'compensation',
        'benefits',
        'leave',
        'analytics',
        'documents',
        'delegations',
        'settings',
    ],
    $ROLES['COMPENSATION_MANAGER'] => [
        'dashboard',
        'structures',
        'merit',
        'budget',
        'equity',
        'benchmarking',
        'analytics',
    ],
    $ROLES['BENEFITS_COORDINATOR'] => [
        'dashboard',
        'benefits',
        'claims',
        'providers',
        'enrollment',
        'benefits-analytics',
        'documents',
        'member',
    ],
    $ROLES['PAYROLL_ADMIN'] => [
        'dashboard',
        'payroll',
        'deductions',
        'tax',
        'bank',
        'payslips',
        'reports',
    ],
    $ROLES['DEPT_HEAD'] => ['dashboard', 'team', 'leave', 'performance', 'budget', 'reports', 'documents'],
    $ROLES['EMPLOYEE'] => [
        'dashboard',
        'profile',
        'payslips',
        'benefits-center',
        'leave',
        'documents',
        'help',
    ],
    $ROLES['EXECUTIVE'] => ['dashboard', 'workforce', 'cost', 'benefits', 'compliance', 'reports'],
];

// Sidebar items per role
$SIDEBAR_ITEMS = [
    $ROLES['HR_MANAGER'] => [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'g'],
        ['id' => 'employees', 'label' => 'Employee Records', 'icon' => 'u'],
        ['id' => 'payroll', 'label' => 'Payroll Overview', 'icon' => 'p'],
        ['id' => 'compensation', 'label' => 'Compensation Management', 'icon' => 'c'],
        ['id' => 'benefits', 'label' => 'Benefits Administration', 'icon' => 'b'],
        ['id' => 'leave', 'label' => 'Leave Administration', 'icon' => 'v'],
        ['id' => 'analytics', 'label' => 'HR Analytics Dashboard', 'icon' => 'a'],
        ['id' => 'documents', 'label' => 'HR Documents', 'icon' => 'd'],
        ['id' => 'delegations', 'label' => 'Delegations', 'icon' => 'r'],
        ['id' => 'settings', 'label' => 'Settings', 'icon' => 's'],
    ],
    $ROLES['COMPENSATION_MANAGER'] => [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'g'],
        ['id' => 'structures', 'label' => 'Salary Structures', 'icon' => 't'],
        ['id' => 'merit', 'label' => 'Merit Increases', 'icon' => 'm'],
        ['id' => 'budget', 'label' => 'Compensation Budgeting', 'icon' => 'u'],
        ['id' => 'equity', 'label' => 'Pay Equity Reports', 'icon' => 'e'],
        ['id' => 'benchmarking', 'label' => 'Internal Pay Benchmarking', 'icon' => 'b'],
        ['id' => 'analytics', 'label' => 'Compensation Analytics', 'icon' => 'a'],
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
        ['id' => 'team', 'label' => 'Team Records', 'icon' => 't'],
        ['id' => 'budget', 'label' => 'Staffing Cost', 'icon' => 'u'],
        ['id' => 'leave', 'label' => 'Leave Approvals', 'icon' => 'v'],
        ['id' => 'performance', 'label' => 'Performance Review', 'icon' => 'p'],
        ['id' => 'reports', 'label' => 'Department Reports', 'icon' => 'r'],
        ['id' => 'documents', 'label' => 'Department Documents', 'icon' => 'd'],
    ],
    $ROLES['EMPLOYEE'] => [
        ['id' => 'dashboard', 'label' => 'Home', 'icon' => 'g'],
        ['id' => 'profile', 'label' => 'My Profile', 'icon' => 'i'],
        ['id' => 'payslips', 'label' => 'Payslips', 'icon' => 'y'],
        ['id' => 'benefits-center', 'label' => 'Benefits Center', 'icon' => 'b'],
        ['id' => 'leave', 'label' => 'Leave Requests', 'icon' => 'v'],
        ['id' => 'documents', 'label' => 'My Documents', 'icon' => 'd'],
        ['id' => 'help', 'label' => 'Help Center', 'icon' => 'h'],
    ],
    $ROLES['EXECUTIVE'] => [
        ['id' => 'dashboard', 'label' => 'Executive Dashboard', 'icon' => 'g'],
        ['id' => 'workforce', 'label' => 'Workforce Analytics', 'icon' => 'w'],
        ['id' => 'cost', 'label' => 'Compensation Insights', 'icon' => 'c'],
        ['id' => 'benefits', 'label' => 'Benefits Overview', 'icon' => 'b'],
        ['id' => 'compliance', 'label' => 'Compliance Summary', 'icon' => 'o'],
        ['id' => 'reports', 'label' => 'Executive Reports', 'icon' => 'r'],
    ],
];
?>