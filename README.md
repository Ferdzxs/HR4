# HR4 - Unified HR Management System

A comprehensive HR management system with role-based access control, built with PHP and modern web technologies.

## Features

- **🔐 Unified Login System** - Single login page for all roles
- **👥 Role-Based Access Control** - 7 different user roles with specific permissions
- **📊 Dashboard Analytics** - Real-time insights and metrics
- **🎨 Modern UI** - Clean, responsive design with dark/light theme support
- **📱 Mobile Responsive** - Works seamlessly on all devices

## User Roles

| Role | Description | Key Features |
|------|-------------|--------------|
| **HR Manager** | Complete HR oversight | Employee management, payroll, compensation, benefits, analytics |
| **Compensation Manager** | Salary and benefits planning | Budget management, market analysis, equity planning |
| **Benefits Coordinator** | Benefits administration | HMO management, claims processing, enrollment |
| **Payroll Administrator** | Payroll processing | Payroll runs, tax compliance, bank management |
| **Department Head** | Team management | Team oversight, budget approval, performance reviews |
| **Hospital Employee** | Self-service portal | Profile management, payslips, leave requests |
| **Hospital Management** | Executive oversight | Strategic planning, workforce analytics, compliance |

## Quick Start

1. **Access the System**
   ```
   http://localhost/H4/
   ```

2. **Login with Demo Credentials**
   - **HR Manager**: `hr.manager` / `hr123`
   - **Employee**: `employee` / `emp123`
   - **Payroll Admin**: `payroll.admin` / `payroll123`
   - **Benefits Coord**: `benefits.coord` / `benefits123`

3. **Navigate the Interface**
   - Use the sidebar to access different modules
   - Each role sees only their authorized pages
   - Responsive design works on desktop and mobile

## File Structure

```
HR4/
├── index.php                    # Redirects to login
├── login.php                    # Unified login page
├── app.php                      # Main application router
├── logout.php                   # Logout handler
├── rbac.php                     # Role-based access control
├── shared/                      # Shared components
│   ├── header.php              # Header with user info & logout
│   ├── sidebar.php             # Navigation sidebar
│   ├── styles.php              # CSS styles & themes
│   └── scripts.php             # JavaScript functionality
└── roles/                      # Role-specific pages
    ├── HR_MANAGER/             # 11 pages
    ├── COMPENSATION_MANAGER/   # 8 pages
    ├── BENEFITS_COORDINATOR/   # 8 pages
    ├── PAYROLL_ADMIN/          # 8 pages
    ├── DEPT_HEAD/              # 7 pages
    ├── EMPLOYEE/               # 7 pages
    └── EXECUTIVE/              # 7 pages
```

## Technical Details

### Authentication
- Session-based authentication
- Automatic logout on session expiry
- Secure credential validation

### Navigation
- URL-based routing (`?page=dashboard`)
- Role-based page access control
- Automatic redirects for unauthorized access

### UI/UX
- Tailwind CSS for styling
- Dark/light theme support
- Mobile-responsive design
- Consistent component structure

## Demo Credentials

| Role | Username | Password |
|------|----------|----------|
| HR Manager | hr.manager | hr123 |
| Compensation Manager | comp.manager | comp123 |
| Benefits Coordinator | benefits.coord | benefits123 |
| Payroll Administrator | payroll.admin | payroll123 |
| Department Head | dept.head | dept123 |
| Hospital Employee | employee | emp123 |
| Hospital Management | executive | exec123 |

## Development

### Requirements
- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx)
- Modern web browser

### Installation
1. **Clone/download the project**
2. **Place in web server directory** (e.g., `C:\xampp\htdocs\H4\`)
3. **Set up the database:**
   - Start MySQL/MariaDB service
   - Access `http://localhost/H4/setup_database.php` to create database and tables
   - Or manually run `schema.sql` and `sample_data.sql` in your MySQL client
4. **Test the connection:**
   - Access `http://localhost/H4/test_connection.php` to verify database setup
5. **Access the system:**
   - Go to `http://localhost/H4/`
   - Login with demo credentials

## Security Features

- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ Secure logout functionality
- ✅ Input validation and sanitization
- ✅ No direct file access without authentication

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

---

**Note**: This is a demo system with mock data. In production, implement proper database integration and enhanced security measures.