<?php
/**
 * Shared Sidebar Component
 * HR4 Compensation & Intelligence System
 */

function renderSidebar($role, $activePage = 'dashboard')
{
    $sidebarItems = getSidebarItems($role);

    ob_start();
    ?>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">HR4</div>
            <div class="sidebar-title">HR Intelligence</div>
        </div>

        <nav class="sidebar-nav">
            <?php foreach ($sidebarItems as $item): ?>
                <div class="nav-item">
                    <a href="<?php echo $item['url']; ?>"
                        class="nav-link <?php echo $activePage === $item['id'] ? 'active' : ''; ?>"
                        data-tooltip="<?php echo $item['tooltip']; ?>">
                        <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <?php echo getIcon($item['icon']); ?>
                        </svg>
                        <span class="nav-text"><?php echo $item['label']; ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </nav>

        <!-- Sidebar Footer -->
        <div class="mt-auto p-4 border-t border-gray-200">
            <div class="text-xs text-gray-500 text-center">
                <p>HR4 v1.0.0</p>
                <p>&copy; 2024 Hospital System</p>
            </div>
        </div>
    </aside>
    <?php
    return ob_get_clean();
}

function getSidebarItems($role)
{
    $sidebarItems = [
        'HR Manager' => [
            [
                'id' => 'dashboard',
                'label' => 'Dashboard',
                'url' => 'roles/hr_manager/dashboard.php',
                'icon' => 'dashboard',
                'tooltip' => 'Comprehensive overview with key metrics, headcount, payroll status, compliance alerts'
            ],
            [
                'id' => 'employees',
                'label' => 'Employees',
                'url' => 'roles/hr_manager/employees.php',
                'icon' => 'users',
                'tooltip' => 'Employee profile management, creating/updating records, lifecycle tracking, document management'
            ],
            [
                'id' => 'organization',
                'label' => 'Organization',
                'url' => 'roles/hr_manager/organization.php',
                'icon' => 'building',
                'tooltip' => 'Department management, positions, hierarchies, role-based access controls'
            ],
            [
                'id' => 'payroll',
                'label' => 'Payroll',
                'url' => 'roles/hr_manager/payroll.php',
                'icon' => 'currency-dollar',
                'tooltip' => 'Full payroll processing, calculations, tax management, payslip generation, error corrections'
            ],
            [
                'id' => 'compensation',
                'label' => 'Compensation',
                'url' => 'roles/hr_manager/compensation.php',
                'icon' => 'chart-bar',
                'tooltip' => 'Salary grade setup, merit increase workflows, pay equity analysis, benchmarking tools'
            ],
            [
                'id' => 'benefits',
                'label' => 'Benefits',
                'url' => 'roles/hr_manager/benefits.php',
                'icon' => 'heart',
                'tooltip' => 'HMO plan administration, enrollment management, claims processing, provider network tools'
            ],
            [
                'id' => 'analytics',
                'label' => 'Analytics',
                'url' => 'roles/hr_manager/analytics.php',
                'icon' => 'chart-pie',
                'tooltip' => 'Real-time dashboards, workforce metrics, predictive analytics, custom report generation'
            ],
            [
                'id' => 'delegations',
                'label' => 'Delegations',
                'url' => 'roles/hr_manager/delegations.php',
                'icon' => 'user-group',
                'tooltip' => 'Temporary role assignments, approval chains, delegation tracking'
            ],
            [
                'id' => 'bulk_operations',
                'label' => 'Bulk Operations',
                'url' => 'roles/hr_manager/bulk_operations.php',
                'icon' => 'upload',
                'tooltip' => 'Mass updates, document processing, change validation'
            ],
            [
                'id' => 'settings',
                'label' => 'Settings',
                'url' => 'roles/hr_manager/settings.php',
                'icon' => 'cog',
                'tooltip' => 'System configuration, user management, policy creation, audit trail access'
            ]
        ],
        'Compensation Manager' => [
            [
                'id' => 'dashboard',
                'label' => 'Dashboard',
                'url' => 'roles/compensation_manager/dashboard.php',
                'icon' => 'dashboard',
                'tooltip' => 'Compensation-focused overview with salary planning metrics, equity alerts, budget summaries'
            ],
            [
                'id' => 'employees',
                'label' => 'Employees',
                'url' => 'roles/compensation_manager/employees.php',
                'icon' => 'users',
                'tooltip' => 'Read-only view of employee records and payroll data for compensation reference'
            ],
            [
                'id' => 'compensation',
                'label' => 'Compensation',
                'url' => 'roles/compensation_manager/compensation.php',
                'icon' => 'chart-bar',
                'tooltip' => 'Core tools for salary structures, merit increases, pay equity analysis, market benchmarking'
            ],
            [
                'id' => 'analytics',
                'label' => 'Analytics',
                'url' => 'roles/compensation_manager/analytics.php',
                'icon' => 'chart-pie',
                'tooltip' => 'Compensation-specific reports and KPIs linked to performance metrics'
            ],
            [
                'id' => 'settings',
                'label' => 'Settings',
                'url' => 'roles/compensation_manager/settings.php',
                'icon' => 'cog',
                'tooltip' => 'Limited access to compensation policy recommendations (no approval or config)'
            ]
        ],
        'Benefits Coordinator' => [
            [
                'id' => 'dashboard',
                'label' => 'Dashboard',
                'url' => 'roles/benefits_coordinator/dashboard.php',
                'icon' => 'dashboard',
                'tooltip' => 'Benefits overview with enrollment status, claims queue, cost analytics'
            ],
            [
                'id' => 'employees',
                'label' => 'Employees',
                'url' => 'roles/benefits_coordinator/employees.php',
                'icon' => 'users',
                'tooltip' => 'Read-only employee records for benefits reference'
            ],
            [
                'id' => 'benefits',
                'label' => 'Benefits',
                'url' => 'roles/benefits_coordinator/benefits.php',
                'icon' => 'heart',
                'tooltip' => 'Full administration of HMO plans, enrollments, dependents, claims workflows, provider management'
            ],
            [
                'id' => 'analytics',
                'label' => 'Analytics',
                'url' => 'roles/benefits_coordinator/analytics.php',
                'icon' => 'chart-pie',
                'tooltip' => 'Benefits utilization reports, cost tracking, government benefits compliance'
            ],
            [
                'id' => 'settings',
                'label' => 'Settings',
                'url' => 'roles/benefits_coordinator/settings.php',
                'icon' => 'cog',
                'tooltip' => 'Provider network configuration and renewal tracking'
            ]
        ],
        'Payroll Administrator' => [
            [
                'id' => 'dashboard',
                'label' => 'Dashboard',
                'url' => 'roles/payroll_administrator/dashboard.php',
                'icon' => 'dashboard',
                'tooltip' => 'Payroll status overview with processing queues, error alerts, compliance summaries'
            ],
            [
                'id' => 'employees',
                'label' => 'Employees',
                'url' => 'roles/payroll_administrator/employees.php',
                'icon' => 'users',
                'tooltip' => 'Read-only employee and compensation data for payroll reference'
            ],
            [
                'id' => 'payroll',
                'label' => 'Payroll',
                'url' => 'roles/payroll_administrator/payroll.php',
                'icon' => 'currency-dollar',
                'tooltip' => 'Automated processing, calculations, deductions, payslips, bank integrations, retroactive adjustments'
            ],
            [
                'id' => 'analytics',
                'label' => 'Analytics',
                'url' => 'roles/payroll_administrator/analytics.php',
                'icon' => 'chart-pie',
                'tooltip' => 'Payroll cost reports and tax compliance analytics'
            ],
            [
                'id' => 'settings',
                'label' => 'Settings',
                'url' => 'roles/payroll_administrator/settings.php',
                'icon' => 'cog',
                'tooltip' => 'Tax and compliance configuration (no salary structure changes)'
            ]
        ],
        'Department Head' => [
            [
                'id' => 'dashboard',
                'label' => 'Dashboard',
                'url' => 'roles/department_head/dashboard.php',
                'icon' => 'dashboard',
                'tooltip' => 'Department analytics with team headcount, budget monitoring, performance summaries'
            ],
            [
                'id' => 'team',
                'label' => 'Team Members',
                'url' => 'roles/department_head/team.php',
                'icon' => 'users',
                'tooltip' => 'View team employee info, payroll summaries, approval workflows for requests'
            ],
            [
                'id' => 'analytics',
                'label' => 'Analytics',
                'url' => 'roles/department_head/analytics.php',
                'icon' => 'chart-pie',
                'tooltip' => 'Department-specific metrics on costs, retention, and equity'
            ],
            [
                'id' => 'approvals',
                'label' => 'Approvals',
                'url' => 'roles/department_head/approvals.php',
                'icon' => 'check',
                'tooltip' => 'Interface for reviewing and approving team-related requests like leaves or benefits'
            ]
        ],
        'Hospital Employee' => [
            [
                'id' => 'dashboard',
                'label' => 'Dashboard',
                'url' => 'roles/hospital_employee/dashboard.php',
                'icon' => 'dashboard',
                'tooltip' => 'Personal overview with latest payslip, benefits status, leave balances'
            ],
            [
                'id' => 'profile',
                'label' => 'My Profile',
                'url' => 'roles/hospital_employee/profile.php',
                'icon' => 'user',
                'tooltip' => 'Update personal info, emergency contacts, download documents'
            ],
            [
                'id' => 'payslips',
                'label' => 'Payslips',
                'url' => 'roles/hospital_employee/payslips.php',
                'icon' => 'document',
                'tooltip' => 'View current and historical payslips with breakdowns'
            ],
            [
                'id' => 'benefits',
                'label' => 'Benefits',
                'url' => 'roles/hospital_employee/benefits.php',
                'icon' => 'heart',
                'tooltip' => 'Enrollment portal, dependent management, claims submission, status tracking'
            ],
            [
                'id' => 'policies',
                'label' => 'Policies',
                'url' => 'roles/hospital_employee/policies.php',
                'icon' => 'folder',
                'tooltip' => 'Access to HR policies, forms, and tax documents'
            ]
        ],
        'Hospital Management' => [
            [
                'id' => 'dashboard',
                'label' => 'Dashboard',
                'url' => 'roles/hospital_management/dashboard.php',
                'icon' => 'dashboard',
                'tooltip' => 'Executive metrics on workforce, costs, compliance, strategic insights'
            ],
            [
                'id' => 'analytics',
                'label' => 'Analytics',
                'url' => 'roles/hospital_management/analytics.php',
                'icon' => 'chart-pie',
                'tooltip' => 'High-level dashboards for headcount, turnover, budget analysis, predictive trends'
            ],
            [
                'id' => 'reports',
                'label' => 'Reports',
                'url' => 'roles/hospital_management/reports.php',
                'icon' => 'document-report',
                'tooltip' => 'Access to executive reports and compliance summaries'
            ]
        ]
    ];

    return $sidebarItems[$role] ?? [];
}

?>