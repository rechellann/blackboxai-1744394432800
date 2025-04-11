<?php
require_once 'database.php';

try {
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Create loans table
    $pdo->exec("CREATE TABLE IF NOT EXISTS loans (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        loan_amount DECIMAL(15,2) NOT NULL,
        interest_rate DECIMAL(5,2) NOT NULL,
        term_months INT NOT NULL,
        purpose TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected', 'paid') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Create payments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        loan_id INT NOT NULL,
        amount_paid DECIMAL(15,2) NOT NULL,
        payment_method ENUM('credit_card', 'debit_card', 'bank_transfer') NOT NULL,
        payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE
    )");

    // Create default admin user if not exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, 'admin')");
        $stmt->execute(['admin', $adminPassword, 'admin@example.com', 'System Administrator']);
    }

    echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Database Setup Complete</title>
            <script src='https://cdn.tailwindcss.com'></script>
        </head>
        <body class='bg-gray-50 p-8'>
            <div class='max-w-3xl mx-auto bg-white rounded-lg shadow-md p-6'>
                <div class='text-green-600 mb-4'>
                    <h1 class='text-2xl font-bold text-center mb-4'>Database Setup Complete</h1>
                </div>
                <div class='bg-green-50 border-l-4 border-green-500 p-4 mb-4'>
                    <p class='font-bold'>All tables have been created successfully!</p>
                    <ul class='list-disc list-inside mt-2'>
                        <li>Users table created</li>
                        <li>Loans table created</li>
                        <li>Payments table created</li>
                        <li>Default admin user created</li>
                    </ul>
                </div>
                <div class='bg-blue-50 border-l-4 border-blue-500 p-4 mb-4'>
                    <p class='font-bold'>Default Admin Credentials:</p>
                    <ul class='list-none mt-2'>
                        <li>Username: admin</li>
                        <li>Password: admin123</li>
                    </ul>
                </div>
                <div class='mt-4 text-center'>
                    <a href='/index.php' class='bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700'>
                        Go to Homepage
                    </a>
                </div>
            </div>
        </body>
        </html>
    ";

} catch (PDOException $e) {
    die("
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Database Setup Error</title>
            <script src='https://cdn.tailwindcss.com'></script>
        </head>
        <body class='bg-gray-50 p-8'>
            <div class='max-w-3xl mx-auto bg-white rounded-lg shadow-md p-6'>
                <div class='text-red-600 mb-4'>
                    <h1 class='text-2xl font-bold text-center mb-4'>Database Setup Error</h1>
                    <p class='text-center'>Error: " . $e->getMessage() . "</p>
                </div>
                <div class='bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4'>
                    <p class='font-bold'>Please check the following:</p>
                    <ul class='list-disc list-inside mt-2'>
                        <li>XAMPP MySQL service is running</li>
                        <li>Database 'loan_management' exists</li>
                        <li>MySQL user has sufficient privileges</li>
                    </ul>
                </div>
                <div class='mt-4 text-center'>
                    <a href='" . $_SERVER['PHP_SELF'] . "' class='bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700'>
                        Try Again
                    </a>
                </div>
            </div>
        </body>
        </html>
    ");
}
?>
