# HR4 Database Setup Guide

## Quick Setup Steps

1. **Start XAMPP Services**
   - Start Apache and MySQL services in XAMPP Control Panel

2. **Set Up Database**
   - Go to: `http://localhost/H4/setup_database.php`
   - This will create the database and insert sample data

3. **Fix Password Hashes**
   - Go to: `http://localhost/H4/fix_passwords.php`
   - This will update all user passwords to the correct hashes

4. **Test the System**
   - Go to: `http://localhost/H4/test_connection.php`
   - Verify database connection and user counts

5. **Test Authentication**
   - Go to: `http://localhost/H4/test_auth.php`
   - Test login with demo credentials

6. **Access the System**
   - Go to: `http://localhost/H4/`
   - Login with demo credentials

## Demo Login Credentials

| Role | Username | Password |
|------|----------|----------|
| HR Manager | hr.manager | hr123 |
| Employee | employee | emp123 |
| Payroll Admin | payroll.admin | payroll123 |
| Benefits Coordinator | benefits.coord | benefits123 |
| Compensation Manager | comp.manager | comp123 |
| Department Head | dept.head | dept123 |
| Executive | executive | exec123 |

## Troubleshooting

- **"Could not find driver" error**: The system will automatically fallback to MySQLi if PDO is not available
- **Database connection failed**: Make sure MySQL is running in XAMPP
- **Login still not working**: Run the fix_passwords.php script to update password hashes

## File Structure

```
H4/
├── config/
│   ├── database.php      # Database connection (PDO/MySQLi)
│   └── auth.php          # Authentication class
├── setup_database.php    # Database setup script
├── fix_passwords.php     # Password hash fix script
├── test_connection.php   # Connection test
├── test_auth.php         # Authentication test
├── login.php             # Login page
├── app.php               # Main application router
└── [other files...]
```
