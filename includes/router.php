<?php
require_once __DIR__ . '/rbac.php';
require_once __DIR__ . '/page_renderers.php';

class Router
{
    private $pdo;
    private $auth;
    private $rbac;
    private $pageRenderers;

    public function __construct($pdo, $auth)
    {
        $this->pdo = $pdo;
        $this->auth = $auth;
        $this->rbac = new RBAC();
        $this->pageRenderers = new PageRenderers($pdo, $auth);
    }

    public function handleRequest()
    {
        $page = $_GET['page'] ?? 'login';
        $user = $this->auth->getUserInfo();

        // Handle logout
        if ($page === 'logout') {
            $this->auth->logout();
            header('Location: ?page=login');
            exit;
        }

        // Check authentication
        if (!$this->auth->isLoggedIn() && $page !== 'login') {
            header('Location: ?page=login');
            exit;
        }

        // Check permissions
        if ($this->auth->isLoggedIn() && $page !== 'login' && $page !== 'dashboard') {
            $role = $user['role_name'];
            if (!$this->rbac::hasPermission($role, $page)) {
                $this->pageRenderers->renderPlaceholder(
                    'dashboard',
                    'Unauthorized',
                    'You do not have access to this module.'
                );
                return;
            }
        }

        // Route to appropriate page
        switch ($page) {
            case 'login':
                echo $this->pageRenderers->renderLogin();
                break;

            case 'dashboard':
                echo $this->pageRenderers->renderDashboard();
                break;

            case 'employees':
                echo $this->pageRenderers->renderEmployees();
                break;

            case 'payroll':
                echo $this->pageRenderers->renderPlaceholder('payroll', 'Payroll', 'Processing calendar, approvals and exception handling');
                break;

            case 'benefits':
                echo $this->pageRenderers->renderPlaceholder('benefits', 'Benefits Administration', 'Plans, enrollments, claims and providers');
                break;

            case 'organization':
                echo $this->pageRenderers->renderPlaceholder('organization', 'Department Control', 'Structure, heads, and budget allocation');
                break;

            case 'documents':
                echo $this->pageRenderers->renderPlaceholder('documents', 'Document Center', 'Library with access logs and versioning');
                break;

            case 'analytics':
                echo $this->pageRenderers->renderPlaceholder('analytics', 'Analytics Hub', 'Real-time workforce and payroll analytics');
                break;

            case 'settings':
                echo $this->pageRenderers->renderPlaceholder('settings', 'Settings', 'Users, roles, permissions, and system configuration');
                break;

            case 'delegations':
                echo $this->pageRenderers->renderPlaceholder('delegations', 'Delegations', 'Temporary roles, approval chains, tracking');
                break;

            case 'bulk':
                echo $this->pageRenderers->renderPlaceholder('bulk', 'Bulk Operations', 'Mass updates, document processing, validation');
                break;

            // Compensation Manager pages
            case 'compensation':
                echo $this->pageRenderers->renderPlaceholder('compensation', 'Compensation Planning', 'Budgets, increases, equity, and approvals');
                break;

            case 'structures':
                echo $this->pageRenderers->renderPlaceholder('structures', 'Salary Structures', 'Salary bands, position mapping, history');
                break;

            case 'merit':
                echo $this->pageRenderers->renderPlaceholder('merit', 'Merit Increases', 'Review cycles, manager input, batch processing');
                break;

            case 'equity':
                echo $this->pageRenderers->renderPlaceholder('equity', 'Pay Equity Analysis', 'Gap analysis by role, gender, department');
                break;

            case 'budget':
                echo $this->pageRenderers->renderPlaceholder('budget', 'Budget Management', 'Allocation, utilization, variance');
                break;

            case 'reports':
                echo $this->pageRenderers->renderPlaceholder('reports', 'Reports Center', 'Standard and custom reports');
                break;

            case 'market':
                echo $this->pageRenderers->renderPlaceholder('market', 'Market Analysis', 'Benchmarks, trends, geographic factors');
                break;

            // Benefits Coordinator pages
            case 'claims':
                echo $this->pageRenderers->renderPlaceholder('claims', 'Claims Processing', 'Queue, verification, and approvals');
                break;

            case 'providers':
                echo $this->pageRenderers->renderPlaceholder('providers', 'Provider Network', 'Directory, contracts, and performance');
                break;

            case 'enrollment':
                echo $this->pageRenderers->renderPlaceholder('enrollment', 'Enrollment Center', 'Open windows, changes, verification');
                break;

            case 'benefits-analytics':
                echo $this->pageRenderers->renderPlaceholder('benefits-analytics', 'Benefits Analytics', 'Analytics and reporting for benefits');
                break;

            case 'member':
                echo $this->pageRenderers->renderPlaceholder('member', 'Member Services', 'Inquiries, response times, satisfaction');
                break;

            // Payroll Admin pages
            case 'tax':
                echo $this->pageRenderers->renderPlaceholder('tax', 'Tax Management', 'Withholding, filings, and regulatory updates');
                break;

            case 'deductions':
                echo $this->pageRenderers->renderPlaceholder('deductions', 'Deductions Control', 'Benefit deductions, loans, voluntary contributions');
                break;

            case 'bank':
                echo $this->pageRenderers->renderPlaceholder('bank', 'Bank Files', 'Disbursement generation and reconciliation');
                break;

            case 'payslips':
                echo $this->pageRenderers->renderPlaceholder('payslips', 'Payslips', 'Latest payslips with detailed breakdowns');
                break;

            case 'compliance':
                echo $this->pageRenderers->renderPlaceholder('compliance', 'Compliance Center', 'Obligations, audits, risk flags');
                break;

            // Department Head pages
            case 'team':
                echo $this->pageRenderers->renderPlaceholder('team', 'Team Management', 'Team directory, approvals, and summaries');
                break;

            case 'leave':
                echo $this->pageRenderers->renderPlaceholder('leave', 'Leave Management', 'Requests, approvals, and balances');
                break;

            case 'performance':
                echo $this->pageRenderers->renderPlaceholder('performance', 'Performance Review', 'Evaluations, feedback, calibration');
                break;

            // Employee Self-Service pages
            case 'profile':
                echo $this->pageRenderers->renderPlaceholder('profile', 'My Profile', 'Personal info, employment details, and security');
                break;

            case 'benefits-center':
                echo $this->pageRenderers->renderPlaceholder('benefits-center', 'Benefits Center', 'Active plans, contributions, and claim history');
                break;

            case 'help':
                echo $this->pageRenderers->renderPlaceholder('help', 'Help Center', 'FAQ, tutorials, and support');
                break;

            // Executive pages
            case 'executive':
                echo $this->pageRenderers->renderPlaceholder('executive', 'Executive Dashboard', 'High-level workforce, cost, and compliance KPIs');
                break;

            case 'strategy':
                echo $this->pageRenderers->renderPlaceholder('strategy', 'Strategic Planning', 'Objectives, milestones, resource allocation');
                break;

            case 'cost':
                echo $this->pageRenderers->renderPlaceholder('cost', 'Cost Analysis', 'Cost analysis and reporting');
                break;

            case 'workforce':
                echo $this->pageRenderers->renderPlaceholder('workforce', 'Workforce Analytics', 'Workforce analytics and reporting');
                break;

            default:
                // Check if it's a valid sidebar item for the current user
                if ($user) {
                    $role = $user['role_name'];
                    $sidebarItems = $this->rbac::getSidebarItems($role);
                    $validPages = array_column($sidebarItems, 'id');

                    if (in_array($page, $validPages)) {
                        echo $this->pageRenderers->renderPlaceholder($page, ucfirst(str_replace('-', ' ', $page)), 'Module placeholder');
                        break;
                    }
                }

                // Default to login if page not found
                header('Location: ?page=login');
                exit;
        }
    }
}
?>