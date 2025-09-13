<?php
require_once __DIR__ . '/ui.php';
require_once __DIR__ . '/rbac.php';

class PageRenderers
{
    private $pdo;
    private $auth;
    private $rbac;

    public function __construct($pdo, $auth)
    {
        $this->pdo = $pdo;
        $this->auth = $auth;
        $this->rbac = new RBAC();
    }

    public function renderLogin()
    {
        $roleOptions = '';
        foreach ($this->rbac::getRoles() as $role) {
            $roleOptions .= sprintf('<option value="%s">%s</option>', $role, $role);
        }

        $demoCredentials = $this->rbac::getDemoCredentials();
        $credentialsScript = '';
        foreach ($demoCredentials as $role => $creds) {
            $credentialsScript .= sprintf(
                '"%s": { username: "%s", password: "%s" },',
                $role,
                $creds['username'],
                $creds['password']
            );
        }

        $html = sprintf(
            '<div class="space-y-6 animate-fade-in">
                <div class="text-center">
                    <div class="inline-flex items-center gap-3 text-brand-600 dark:text-brand-400 mb-2">
                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white font-bold text-lg shadow-lg">H4</span>
                        <div class="text-2xl font-bold">HR4</div>
                    </div>
                    <div class="text-sm text-slate-500">Compensation & HR Intelligence</div>
                </div>
                
                %s
            </div>',
            UI::card([
                'title' => 'Welcome back',
                'content' => sprintf(
                    '<form id="loginForm" class="space-y-5">
                        <div class="space-y-2">
                            <label for="username" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Username</label>
                            <div class="relative">
                                %s
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Password</label>
                            <div class="relative">
                                %s
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg class="h-4 w-4 text-slate-400 hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <label for="role" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Role</label>
                            %s
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="rememberMe" type="checkbox" class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 rounded">
                                <label for="rememberMe" class="ml-2 block text-sm text-slate-700 dark:text-slate-300">Remember me</label>
                            </div>
                            <button type="button" id="forgotPasswordBtn" class="text-sm text-brand-600 hover:text-brand-500 dark:text-brand-400">Forgot password?</button>
                        </div>
                        
                        <div class="space-y-3">
                            %s
                            <div id="loginMsg" class="text-sm text-red-600 dark:text-red-400 text-center hidden"></div>
                        </div>
                    </form>',
                    UI::input(['id' => 'username', 'placeholder' => 'Enter your username', 'extra' => 'pl-10']),
                    UI::input(['id' => 'password', 'type' => 'password', 'placeholder' => 'Enter your password', 'extra' => 'pl-10 pr-10']),
                    UI::select([
                        'id' => 'role',
                        'options' => array_merge(
                            [['value' => '', 'label' => 'Select your role']],
                            array_map(function ($role) {
                                return ['value' => $role, 'label' => $role]; }, array_values($this->rbac::getRoles()))
                        )
                    ]),
                    UI::button('Sign in', ['variant' => 'default', 'size' => 'lg', 'id' => 'btnLogin', 'extra' => 'w-full relative'])
                ),
                'footer' => sprintf(
                    '<div class="text-center space-y-2">
                        <div class="text-xs text-slate-500">Demo credentials available for each role</div>
                    </div>'
                )
            ])
        );

        return UI::renderShell($html, '', ['hideChrome' => true]);
    }

    public function renderDashboard()
    {
        $user = $this->auth->getUserInfo();
        $role = $user['role_name'] ?? 'Dashboard';
        $subtitle = 'Role-based overview with quick insights';

        try {
            // Get dashboard data from database
            $dashboardData = $this->getDashboardData();

            $cards = [
                UI::kpi(['label' => 'Total Employees', 'value' => $dashboardData['total_employees'] ?? '—']),
                UI::kpi(['label' => 'Departments', 'value' => $dashboardData['total_departments'] ?? '—']),
                UI::kpi(['label' => 'Payroll Entries', 'value' => $dashboardData['payroll']['total_payroll_entries'] ?? '—']),
                UI::kpi(['label' => 'Active Benefits', 'value' => $dashboardData['benefits']['total_enrollments'] ?? '—']),
            ];

            $roleSpecificContent = '';
            if ($role === 'HR Manager' && isset($dashboardData['department_stats'])) {
                $deptStats = '';
                foreach ($dashboardData['department_stats'] as $dept) {
                    $deptStats .= sprintf(
                        '<div class="flex justify-between text-sm">
                            <span>%s</span>
                            <span class="font-medium">%s</span>
                        </div>',
                        htmlspecialchars($dept['department_name']),
                        $dept['employee_count']
                    );
                }
                $roleSpecificContent = sprintf(
                    '<div class="space-y-2">%s</div>',
                    UI::card(['title' => 'Department Statistics', 'content' => $deptStats])
                );
            }

            $activitiesContent = '<div class="text-sm text-slate-500">No recent activities</div>';
            if (isset($dashboardData['recent_activities']) && !empty($dashboardData['recent_activities'])) {
                $activities = '';
                foreach ($dashboardData['recent_activities'] as $activity) {
                    $activities .= sprintf(
                        '<div class="flex justify-between text-sm">
                            <span>%s - %s</span>
                            <span class="text-slate-500">%s</span>
                        </div>',
                        htmlspecialchars($activity['action_type']),
                        htmlspecialchars($activity['action_description']),
                        date('M j, Y', strtotime($activity['action_timestamp']))
                    );
                }
                $activitiesContent = $activities;
            }

            $html = sprintf(
                '<section class="p-4 lg:p-6 space-y-4">
                    <div>
                        <h1 class="text-lg font-semibold">%s</h1>
                        <p class="text-xs text-slate-500 mt-1">%s</p>
                    </div>
                    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">%s</div>
                    %s
                    <div class="space-y-2">%s</div>
                </section>',
                $role,
                $subtitle,
                implode('', $cards),
                $roleSpecificContent,
                UI::card(['title' => 'Recent Activities', 'content' => $activitiesContent])
            );

        } catch (Exception $e) {
            error_log("Dashboard error: " . $e->getMessage());
            $cards = [
                UI::kpi(['label' => 'Headcount', 'value' => '—']),
                UI::kpi(['label' => 'Payroll Status', 'value' => '—']),
                UI::kpi(['label' => 'Compliance Alerts', 'value' => '—']),
                UI::kpi(['label' => 'Quick Actions', 'value' => 'Common functions at a glance']),
            ];

            $html = sprintf(
                '<section class="p-4 lg:p-6 space-y-4">
                    <div>
                        <h1 class="text-lg font-semibold">%s</h1>
                        <p class="text-xs text-slate-500 mt-1">%s</p>
                    </div>
                    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">%s</div>
                    <div class="space-y-2">%s</div>
                </section>',
                $role,
                $subtitle,
                implode('', $cards),
                UI::card(['title' => 'Activity', 'content' => 'Recent actions will appear here'])
            );
        }

        return UI::renderShell($html, 'dashboard');
    }

    public function renderEmployees()
    {
        try {
            $employees = $this->getEmployees();
            $departments = $this->getDepartments();

            $deptOptions = [['value' => '', 'label' => 'All Departments']];
            foreach ($departments as $dept) {
                $deptOptions[] = ['value' => $dept['id'], 'label' => $dept['department_name']];
            }

            $controls = sprintf(
                '<div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
                    <div class="flex flex-col sm:flex-row gap-2">
                        %s
                        %s
                        %s
                    </div>
                    <div class="flex gap-2">%s%s</div>
                </div>',
                UI::input(['id' => 'empSearch', 'placeholder' => 'Search name/number']),
                UI::select(['id' => 'empDept', 'options' => $deptOptions]),
                UI::select([
                    'id' => 'empStatus',
                    'options' => [
                        ['value' => '', 'label' => 'All Status'],
                        ['value' => 'Active', 'label' => 'Active'],
                        ['value' => 'Inactive', 'label' => 'Inactive'],
                        ['value' => 'Resigned', 'label' => 'Resigned'],
                    ]
                ]),
                UI::button('Add Employee', ['variant' => 'default', 'size' => 'sm', 'id' => 'btnAddEmp']),
                UI::button('Export', ['variant' => 'outline', 'size' => 'sm'])
            );

            $employeeRows = [];
            foreach ($employees as $emp) {
                $statusClass = $emp['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
                $employeeRows[] = [
                    $emp['employee_number'],
                    htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']),
                    htmlspecialchars($emp['department_name'] ?? '—'),
                    htmlspecialchars($emp['position_title'] ?? '—'),
                    sprintf('<span class="px-2 py-1 text-xs rounded-full %s">%s</span>', $statusClass, htmlspecialchars($emp['status'])),
                    sprintf(
                        '<div class="flex gap-1">
                            <button class="text-blue-600 hover:text-blue-800 text-sm" onclick="viewEmployee(%d)">View</button>
                            <button class="text-green-600 hover:text-green-800 text-sm" onclick="editEmployee(%d)">Edit</button>
                        </div>',
                        $emp['id'],
                        $emp['id']
                    )
                ];
            }

            $totalEmployees = count($employees);
            $activeEmployees = count(array_filter($employees, fn($emp) => $emp['status'] === 'Active'));
            $inactiveEmployees = count(array_filter($employees, fn($emp) => $emp['status'] === 'Inactive'));

            $html = sprintf(
                '<section class="p-4 lg:p-6 space-y-4">
                    <div>
                        <h1 class="text-lg font-semibold">Employee Management</h1>
                        <p class="text-xs text-slate-500 mt-1">Directory, profiles, onboarding and offboarding</p>
                    </div>
                    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
                        %s
                        %s
                        %s
                        %s
                    </div>
                    %s
                </section>',
                UI::kpi(['label' => 'Total Employees', 'value' => $totalEmployees]),
                UI::kpi(['label' => 'Active', 'value' => $activeEmployees]),
                UI::kpi(['label' => 'Inactive', 'value' => $inactiveEmployees]),
                UI::kpi(['label' => 'Departments', 'value' => count($departments)]),
                UI::table([
                    'headers' => ['Emp #', 'Name', 'Department', 'Position', 'Status', 'Actions'],
                    'rows' => $employeeRows,
                    'empty' => UI::empty([
                        'title' => 'No employees found',
                        'description' => 'Start by adding your first employee record.',
                        'action' => UI::button('Add Employee', ['variant' => 'default', 'size' => 'sm'])
                    ]),
                    'controls' => $controls
                ])
            );

        } catch (Exception $e) {
            error_log("Employees page error: " . $e->getMessage());
            $html = sprintf(
                '<section class="p-4 lg:p-6 space-y-4">
                    <div>
                        <h1 class="text-lg font-semibold">Employee Management</h1>
                        <p class="text-xs text-slate-500 mt-1">Directory, profiles, onboarding and offboarding</p>
                    </div>
                    <div class="text-center py-8">
                        <p class="text-slate-500">Error loading employees data. Please try again.</p>
                    </div>
                </section>'
            );
        }

        return UI::renderShell($html, 'employees');
    }

    public function renderPlaceholder($id, $title, $description)
    {
        $html = sprintf(
            '<section class="p-4 lg:p-6 space-y-4">
                <h1 class="text-lg font-semibold">%s</h1>
                %s
                %s
            </section>',
            htmlspecialchars($title),
            UI::card(['title' => 'Overview', 'content' => htmlspecialchars($description)]),
            UI::card(['title' => 'Next Steps', 'content' => 'Integrate with backend endpoints per HR4 schema when available.'])
        );

        return UI::renderShell($html, $id);
    }

    private function getDashboardData()
    {
        $data = [];

        // Get basic metrics
        $stmt = $this->pdo->query("SELECT COUNT(*) as total_employees FROM employees WHERE status = 'Active'");
        $data['total_employees'] = $stmt->fetch()['total_employees'];

        $stmt = $this->pdo->query("SELECT COUNT(*) as total_departments FROM departments");
        $data['total_departments'] = $stmt->fetch()['total_departments'];

        // Get payroll summary
        $stmt = $this->pdo->query("
            SELECT 
                COUNT(*) as total_payroll_entries,
                SUM(amount) as total_amount
            FROM payroll_entries 
            WHERE is_processed = 1
        ");
        $data['payroll'] = $stmt->fetch();

        // Get benefits summary
        $stmt = $this->pdo->query("
            SELECT 
                COUNT(*) as total_enrollments,
                COUNT(DISTINCT hmo_plan_id) as active_plans
            FROM benefit_enrollments 
            WHERE status = 'Active'
        ");
        $data['benefits'] = $stmt->fetch();

        // Get department stats for HR Manager
        $user = $this->auth->getUserInfo();
        if ($user['role_name'] === 'HR Manager') {
            $stmt = $this->pdo->query("
                SELECT 
                    d.department_name,
                    COUNT(e.id) as employee_count
                FROM departments d
                LEFT JOIN employees e ON d.id = e.department_id AND e.status = 'Active'
                GROUP BY d.id, d.department_name
                ORDER BY employee_count DESC
            ");
            $data['department_stats'] = $stmt->fetchAll();
        }

        // Get recent activities
        $stmt = $this->pdo->query("
            SELECT 
                al.action_type,
                al.action_description,
                al.timestamp as action_timestamp
            FROM audit_logs al
            ORDER BY al.timestamp DESC
            LIMIT 10
        ");
        $data['recent_activities'] = $stmt->fetchAll();

        return $data;
    }

    private function getEmployees()
    {
        $stmt = $this->pdo->query("
            SELECT 
                e.id,
                e.employee_number,
                e.first_name,
                e.last_name,
                e.status,
                d.department_name,
                p.position_title
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            ORDER BY e.first_name, e.last_name
        ");
        return $stmt->fetchAll();
    }

    private function getDepartments()
    {
        $stmt = $this->pdo->query("
            SELECT id, department_name
            FROM departments
            ORDER BY department_name
        ");
        return $stmt->fetchAll();
    }
}
?>