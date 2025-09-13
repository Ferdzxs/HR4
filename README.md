# HR4 Compensation & Intelligence System

A comprehensive Human Resources management system focused on compensation, payroll, and benefits administration for healthcare organizations.

## 🚀 Features

### Core Modules

- **Employee Management** - Complete employee lifecycle management
- **Compensation Planning** - Salary structures, merit increases, and pay equity analysis
- **Payroll Processing** - Automated payroll with tax calculations and compliance
- **Benefits Administration** - HMO management, claims processing, and enrollment
- **Analytics & Reporting** - Comprehensive dashboards and business intelligence
- **Role-Based Access Control** - Secure multi-role authentication system

### Key Capabilities

- Multi-department organizational structure
- Government contribution management (SSS, PhilHealth, PagIBIG)
- Employee loan tracking and management
- Document management system
- Audit logging and compliance tracking
- Real-time analytics and reporting

## 🛠️ Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Frontend**: Vanilla JavaScript, Tailwind CSS
- **Architecture**: RESTful API with SPA frontend
- **Security**: Password hashing, session management, RBAC

## 📋 Prerequisites

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Web server (Apache/Nginx)
- XAMPP/WAMP/LAMP stack (recommended for development)

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/HR4_COMPEN_INTELLI.git
cd HR4_COMPEN_INTELLI
```

### 2. Database Setup

1. Create a MySQL database named `hr4_compensation_intelli`
2. Import the database schema:
   ```bash
   mysql -u root -p hr4_compensation_intelli < database/schema_fixed.sql
   ```
3. Load sample data:
   ```bash
   mysql -u root -p hr4_compensation_intelli < database/sample_data.sql
   ```

### 3. Configuration

1. Update database credentials in `config/database.php`:
   ```php
   $host = 'localhost';
   $dbname = 'hr4_compensation_intelli';
   $username = 'your_db_username';
   $password = 'your_db_password';
   ```

### 4. Web Server Setup

- Place the project in your web server's document root
- Ensure PHP sessions are enabled
- Set proper file permissions

### 5. Quick Setup (Alternative)

Run the setup script for automated installation:

```bash
php setup.php
```

## 🔐 Default Login Credentials

| Role                  | Username       | Password    |
| --------------------- | -------------- | ----------- |
| HR Manager            | hr.manager     | manager123  |
| Compensation Manager  | comp.manager   | comp123     |
| Benefits Coordinator  | benefits.coord | benefits123 |
| Payroll Administrator | payroll.admin  | payroll123  |
| Department Head       | dept.head      | dept123     |
| Hospital Employee     | employee       | emp123      |
| Hospital Management   | executive      | exec123     |

## 📁 Project Structure

```
HR4_COMPEN_INTELLI/
├── api/                    # REST API endpoints
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── employees.php
│   ├── departments.php
│   ├── payroll.php
│   ├── benefits.php
│   ├── positions.php
│   └── salary_components.php
├── config/                 # Configuration files
│   └── database.php
├── database/              # Database files
│   ├── schema_fixed.sql
│   ├── sample_data.sql
│   └── reset_and_load_sample_data.sql
├── includes/              # Shared PHP files
│   ├── header.php
│   └── auth.php
├── js/                    # Frontend JavaScript
│   ├── app.js
│   ├── rbac.js
│   └── ui.js
├── index.php              # Main entry point
├── setup.php              # Setup script
└── reset_database_with_sample.php
```

## 🔧 API Endpoints

### Authentication

- `POST /api/login.php` - User login
- `POST /api/logout.php` - User logout

### Core Data

- `GET /api/dashboard.php` - Dashboard data
- `GET /api/employees.php` - Employee management
- `GET /api/departments.php` - Department management
- `GET /api/positions.php` - Position management

### Payroll & Benefits

- `GET /api/payroll.php` - Payroll processing
- `GET /api/benefits.php` - Benefits management
- `GET /api/salary_components.php` - Salary components

## 🎯 User Roles & Permissions

### HR Manager

- Full system access
- Employee management
- Payroll oversight
- Compensation planning
- Benefits administration

### Compensation Manager

- Compensation planning
- Salary structure management
- Market analysis
- Budget management

### Benefits Coordinator

- HMO management
- Claims processing
- Provider network management
- Enrollment center

### Payroll Administrator

- Payroll processing
- Tax management
- Deductions control
- Compliance reporting

### Department Head

- Team management
- Budget tracking
- Leave management
- Performance reviews

### Hospital Employee

- Personal profile
- Payslip access
- Benefits center
- Leave requests

### Hospital Management

- Executive dashboard
- Strategic planning
- Cost analysis
- Workforce analytics

## 🔒 Security Features

- Password hashing with PHP's `password_hash()`
- Session-based authentication
- Role-based access control (RBAC)
- SQL injection prevention with prepared statements
- XSS protection
- CSRF token validation
- Audit logging for all actions

## 📊 Database Schema

The system uses a comprehensive MySQL database with the following key tables:

- **users** - User accounts and authentication
- **roles** - Role definitions and permissions
- **employees** - Employee master data
- **departments** - Organizational structure
- **positions** - Job positions and salary grades
- **payroll_entries** - Payroll transactions
- **benefit_enrollments** - Employee benefits
- **analytics_metrics** - System metrics and KPIs

## 🚀 Development

### Running Locally

1. Start your local server (XAMPP/WAMP)
2. Navigate to `http://localhost/HR4_COMPEN_INTELLI/`
3. Use the default credentials to login

### Database Reset

To reset the database with fresh sample data:

```bash
php reset_database_with_sample.php
```

## 📝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👥 Authors

- **Your Name** - _Initial work_ - [YourGitHub](https://github.com/yourusername)

## 🙏 Acknowledgments

- Healthcare industry best practices
- Philippine labor law compliance
- Modern web development standards
- Open source community contributions

## 📞 Support

For support and questions:

- Create an issue in this repository
- Contact: your.email@example.com

---

**Note**: This system is designed for healthcare organizations and includes features specific to Philippine labor law compliance (SSS, PhilHealth, PagIBIG).
