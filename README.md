# HR4 - Unified HR Management System

A comprehensive HR management system with role-based access control, built with PHP and modern web technologies.

## Features

- **🔐 Unified Login System** - Single login page for all roles
- **👥 Role-Based Access Control** - 7 different user roles with specific permissions
- **📊 Dashboard Analytics** - Real-time insights and metrics
- **🎨 Modern UI** - Clean, responsive design with dark/light theme support
- **📱 Mobile Responsive** - Works seamlessly on all devices
- **🛡️ Security** - CSRF protection, session validation, rate limiting, persistent sessions (remember me)

## User Roles

| Role                      | Description                  | Key Features                                                    |
| ------------------------- | ---------------------------- | --------------------------------------------------------------- |
| **HR Manager**            | Complete HR oversight        | Employee management, payroll, compensation, benefits, analytics |
| **Compensation Manager**  | Salary and benefits planning | Budget management, market analysis, equity planning             |
| **Benefits Coordinator**  | Benefits administration      | HMO management, claims processing, enrollment                   |
| **Payroll Administrator** | Payroll processing           | Payroll runs, tax compliance, bank management                   |
| **Department Head**       | Team management              | Team oversight, budget approval, performance reviews            |
| **Hospital Employee**     | Self-service portal          | Profile management, payslips, leave requests                    |
| **Hospital Management**   | Executive oversight          | Strategic planning, workforce analytics, compliance             |

## Quick Start

1. **Place the project**

   - Copy this folder to your web root, e.g. `C:\xampp\htdocs\HR4_COMPEN&INTELLI\`

2. **Set up the database**

   - Start MySQL/MariaDB and Apache in XAMPP
   - In your browser, open:
     - `http://localhost/HR4_COMPEN&INTELLI/setup/setup_database.php` (creates schema and loads sample data)
     - Optional: `http://localhost/HR4_COMPEN&INTELLI/setup/test_connection.php` (verifies DB connection)
     - Optional: `http://localhost/HR4_COMPEN&INTELLI/setup/fix_passwords.php` (resets demo passwords)

3. **Access the system**

   - Go to `http://localhost/HR4_COMPEN&INTELLI/`
   - You will be redirected to `routing/login.php`

4. **Login with Demo Credentials**
   - HR Manager: `hr.manager` / `hr123`
   - Compensation Manager: `comp.manager` / `comp123`
   - Benefits Coordinator: `benefits.coord` / `benefits123`
   - Payroll Administrator: `payroll.admin` / `payroll123`
   - Department Head: `dept.head` / `dept123`
   - Hospital Employee: `employee` / `emp123`
   - Hospital Management: `executive` / `exec123`

## App Structure

```
HR4_COMPEN&INTELLI/
├── index.php                     # Redirects to routing/login.php
├── routing/
│   ├── login.php                 # Unified login with CSRF, rate limit, remember-me
│   ├── app.php                   # Main router; role-based page access
│   ├── logout.php                # Logout handler (destroys session/cookies)
│   └── rbac.php                  # Role/permission utilities
├── config/
│   ├── database.php              # DB connection (PDO/MySQLi)
│   └── auth.php                  # Auth service (sessions, tokens, locks)
├── shared/                       # Shared components
│   ├── header.php                # Header with user info & logout
│   ├── sidebar.php               # Navigation sidebar
│   ├── styles.css                # Base styles & theme tokens
│   ├── scripts.php               # Shared JS includes
│   └── scripts.js                # Client-side helpers (theme, UI)
├── roles/                        # Role-specific pages
│   ├── HR_MANAGER/               # dashboard.php, employees.php, payroll.php, ...
│   ├── COMPENSATION_MANAGER/     # dashboard.php, compensation.php, market.php, ...
│   ├── BENEFITS_COORDINATOR/     # dashboard.php, benefits.php, claims.php, ...
│   ├── PAYROLL_ADMIN/            # dashboard.php, payroll.php, tax.php, ...
│   ├── DEPT_HEAD/                # dashboard.php, team.php, budget.php, ...
│   ├── EMPLOYEE/                 # dashboard.php, profile.php, payslips.php, ...
│   └── EXECUTIVE/                # dashboard.php, strategy.php, workforce.php, ...
├── scheme/
│   ├── schema.sql                # Database schema
│   └── sample_data.sql           # Demo data (roles, users, payroll, benefits, ...)
├── setup/
│   ├── setup_database.php        # Runs schema + sample data loader
│   ├── test_connection.php       # Quick DB connection check
│   └── fix_passwords.php         # Reset demo user passwords
└── README.md
```

## Routing and Navigation

- Main entry: `routing/login.php` → `routing/app.php`
- Route pattern: `routing/app.php?page=<module>`
- Pages are mapped by role inside `routing/app.php` using the user’s role.

## Security Features

- ✅ Session-based authentication with DB-backed sessions
- ✅ CSRF tokens on login
- ✅ Rate limiting and temporary lockout on repeated failures
- ✅ Remember-me token with server validation
- ✅ Role-based access enforcement in `routing/app.php`

## Requirements

- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx (XAMPP recommended on Windows)
- Modern web browser

## Installation

1. Download or clone this repository into `C:\xampp\htdocs\HR4_COMPEN&INTELLI\`.
2. Ensure MySQL and Apache are running in XAMPP.
3. Configure database credentials if needed:
   - Edit `config/database.php` to match your local MySQL credentials. Default is `root` with password `54321` and database `hr4_compensation_intelli`.
4. Initialize the database and demo data:
   - Open `http://localhost/HR4_COMPEN&INTELLI/setup/setup_database.php` in your browser.
   - You should see success messages for schema creation and sample data.
5. Verify connectivity (optional):
   - Open `http://localhost/HR4_COMPEN&INTELLI/setup/test_connection.php`.
6. Reset demo passwords (optional but recommended):
   - Open `http://localhost/HR4_COMPEN&INTELLI/setup/fix_passwords.php`.
7. Launch the app:
   - Navigate to `http://localhost/HR4_COMPEN&INTELLI/` and log in using demo credentials below.

## Troubleshooting

- If logins fail due to unknown passwords, run `setup/fix_passwords.php` to reset demo accounts.
- If you see DB errors, ensure MySQL is running and `config/database.php` has correct credentials.
- To reload demo data, re-run `setup/setup_database.php` (it recreates schema and inserts samples).

---

**Note**: This is a demo system with mock data. For production, configure secure DB credentials, HTTPS, hardened PHP settings, and comprehensive input validation throughout the app.
