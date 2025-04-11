# XAMPP Installation Guide for Loan Management System

## Step 1: XAMPP Setup

1. Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Start XAMPP Control Panel
3. Start Apache and MySQL services

## Step 2: Project Installation

1. Navigate to XAMPP installation directory:
   ```
   C:\xampp\htdocs\
   ```

2. Create a new folder named 'loan_system':
   ```
   C:\xampp\htdocs\loan_system\
   ```

3. Copy all project files into this folder

## Step 3: Database Setup

1. Open phpMyAdmin:
   - Click 'Admin' next to MySQL in XAMPP Control Panel
   - Or visit http://localhost/phpmyadmin

2. Import Database:
   - Click 'Import' in the top menu
   - Click 'Choose File'
   - Navigate to `database/loan_management.sql`
   - Click 'Go' to import

3. Verify Installation:
   - Check 'loan_management' database exists
   - Verify tables are created:
     - users
     - loans
     - payments
   - Confirm sample data is imported

## Step 4: Default Credentials

Admin Account:
- Username: admin
- Password: admin123

Sample User Accounts:
- Username: john_doe
- Password: password

- Username: jane_smith
- Password: password

## Step 5: Testing the System

1. Visit http://localhost/loan_system
2. Log in with admin credentials
3. Test features:
   - User management
   - Loan approval
   - Payment processing
   - Receipt generation

## Troubleshooting

### Database Connection Error
1. Verify MySQL is running in XAMPP Control Panel
2. Check database name is 'loan_management'
3. Verify database credentials in config/database.php:
   ```php
   $host = 'localhost';
   $dbname = 'loan_management';
   $username = 'root';
   $password = ''; // Default XAMPP password is empty
   ```

### Page Not Found Error
1. Check all files are in correct directory
2. Verify Apache is running
3. Check .htaccess configuration

### Permission Issues
1. Right-click on loan_system folder
2. Properties → Security → Edit
3. Add 'Everyone' with Read & Execute permissions

### PHP Errors
1. Check XAMPP error logs:
   - Apache log: xampp/apache/logs/error.log
   - PHP error log: xampp/php/logs/php_error_log
   - MySQL log: xampp/mysql/data/mysql_error.log

## Security Notes

1. Change Default Passwords:
   - Admin account password
   - Sample user account passwords
   - MySQL root password (optional)

2. Remove Installation Files:
   - Delete install.php after setup
   - Remove setup.php when done
   - Secure database/loan_management.sql

3. Configure Error Reporting:
   - Set display_errors = Off in php.ini for production
   - Enable error logging
   - Protect log files

## Maintenance

### Regular Backups
1. Open phpMyAdmin
2. Select 'loan_management' database
3. Click 'Export'
4. Choose 'Quick Export' and 'SQL'
5. Click 'Go' to download backup

### System Updates
1. Backup database before updates
2. Update PHP version in XAMPP
3. Check for system compatibility
4. Test all features after updates

### Performance Optimization
1. Enable MySQL query cache
2. Configure PHP opcache
3. Use appropriate indexes
4. Regular database maintenance

## Additional Resources

- XAMPP Documentation: https://www.apachefriends.org/documentation.html
- PHP Documentation: https://www.php.net/docs.php
- MySQL Documentation: https://dev.mysql.com/doc/
