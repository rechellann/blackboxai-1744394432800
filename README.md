# Loan Management System

A comprehensive loan management system built with PHP and MySQL, designed to run on XAMPP.

## Features

### Admin Features
- User Management (Add, Edit, Delete)
- Loan Management (Approve, Reject, View)
- Payment Tracking
- System Statistics
- User Activity Monitoring

### User Features
- Loan Application
- Payment Management
- Payment Receipt Generation
- Account Management
- Loan History Tracking

## Technical Specifications

- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.2+
- PDO MySQL Extension
- Apache with mod_rewrite
- Tailwind CSS for styling
- Font Awesome for icons

## Installation

1. **XAMPP Setup**
   - Download and install XAMPP
   - Start Apache and MySQL services
   - Verify services are running

2. **Project Installation**
   ```bash
   # Navigate to XAMPP htdocs
   cd /path/to/xampp/htdocs
   
   # Clone or copy project files
   git clone [repository-url] loan_system
   # OR copy files to loan_system directory
   ```

3. **Database Setup**
   - Open http://localhost/phpmyadmin
   - Create new database 'loan_management'
   - Import database structure (if provided)
   - OR run installation script

4. **Configuration**
   - Update database credentials in config/database.php
   - Set appropriate file permissions
   - Configure error reporting

5. **Installation Wizard**
   - Visit http://localhost/loan_system/install.php
   - Follow the installation steps
   - Create admin account

## Directory Structure

```
loan_system/
├── admin/               # Admin panel files
├── auth/               # Authentication files
├── config/             # Configuration files
├── includes/           # Common includes
├── logs/               # System logs
├── user/               # User panel files
├── .htaccess          # Apache configuration
├── error.php          # Error handling
├── index.php          # Main entry point
├── install.php        # Installation wizard
└── setup.php          # System setup
```

## Security Features

1. **Database Security**
   - PDO prepared statements
   - SQL injection prevention
   - Input validation and sanitization

2. **Authentication**
   - Secure password hashing
   - Session management
   - Role-based access control

3. **File Security**
   - Protected sensitive directories
   - Restricted file access
   - Error logging

4. **System Security**
   - XSS prevention
   - CSRF protection
   - Secure cookie handling

## Default Credentials

```
Admin Account:
Username: admin
Password: admin123

IMPORTANT: Change these credentials immediately after installation!
```

## Post-Installation Steps

1. **Security Measures**
   - Change default admin password
   - Delete installation files
   - Set proper file permissions
   - Configure error reporting
   - Secure sensitive directories

2. **System Verification**
   - Test admin login
   - Test user registration
   - Verify loan application process
   - Test payment system
   - Check receipt generation

3. **Regular Maintenance**
   - Monitor error logs
   - Backup database regularly
   - Update dependencies
   - Check system performance

## Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Verify MySQL is running
   - Check database credentials
   - Confirm database exists
   - Test MySQL connection

2. **Page Not Found Errors**
   - Check .htaccess configuration
   - Verify mod_rewrite is enabled
   - Confirm file permissions
   - Check file paths

3. **Permission Issues**
   - Set correct file ownership
   - Configure directory permissions
   - Check log file access
   - Verify upload directories

### Error Logging

- Error logs are stored in /logs directory
- Check Apache error logs for server issues
- Monitor MySQL error log for database problems
- Review PHP error log for script errors

## Support

For issues and support:
1. Check the error logs
2. Review documentation
3. Verify XAMPP configuration
4. Contact system administrator

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- XAMPP development team
- Tailwind CSS
- Font Awesome
- PHP community
