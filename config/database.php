<?php
// MySQL Database Configuration for XAMPP
$host = 'localhost';
$dbname = 'loan_management';
$username = 'root';
$password = ''; // Default XAMPP MySQL password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // If still getting errors, show user-friendly message
    die("
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Database Connection Error</title>
            <script src='https://cdn.tailwindcss.com'></script>
        </head>
        <body class='bg-gray-50 p-8'>
            <div class='max-w-3xl mx-auto bg-white rounded-lg shadow-md p-6'>
                <div class='text-red-600 mb-4'>
                    <h1 class='text-2xl font-bold text-center mb-4'>Database Connection Error</h1>
                    <p class='text-center'>" . ($e->getCode() === 1049 ? "Database 'loan_management' not found. Please run the setup script first." : "Connection failed: " . $e->getMessage()) . "</p>
                </div>
                <div class='bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4'>
                    <p class='font-bold'>Please check the following:</p>
                    <ul class='list-disc list-inside mt-2'>
                        <li>XAMPP MySQL service is running</li>
                        <li>Database 'loan_management' exists (run setup.php first)</li>
                        <li>MySQL credentials are correct (default: root with no password)</li>
                        <li>MySQL server is accepting connections on localhost</li>
                    </ul>
                </div>
                <div class='mt-4 text-center space-x-4'>
                    <a href='/setup.php' class='bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700'>
                        Run Setup
                    </a>
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
