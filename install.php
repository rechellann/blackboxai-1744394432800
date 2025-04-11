<?php
session_start();
$step = $_GET['step'] ?? 1;
$errors = [];
$success = false;

function checkDatabaseConnection() {
    try {
        $pdo = new PDO("mysql:host=localhost", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function checkDatabaseExists() {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=loan_management", "root", "");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 1) {
        if (checkDatabaseConnection()) {
            header('Location: install.php?step=2');
            exit;
        } else {
            $errors[] = "Could not connect to MySQL. Please verify XAMPP MySQL service is running.";
        }
    } elseif ($step == 2) {
        try {
            $pdo = new PDO("mysql:host=localhost", "root", "");
            $pdo->exec("CREATE DATABASE IF NOT EXISTS loan_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            header('Location: install.php?step=3');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Could not create database: " . $e->getMessage();
        }
    } elseif ($step == 3) {
        if (file_exists('config/db_setup.php')) {
            require_once 'config/db_setup.php';
            header('Location: install.php?step=4');
            exit;
        } else {
            $errors[] = "Database setup file not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Management System Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Loan Management System Installation</h1>
                <div class="mt-4 flex justify-center">
                    <div class="flex items-center">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center <?php echo $i == $step ? 'bg-blue-600 text-white' : ($i < $step ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600'); ?>">
                                    <?php if ($i < $step): ?>
                                        <i class="fas fa-check"></i>
                                    <?php else: ?>
                                        <?php echo $i; ?>
                                    <?php endif; ?>
                                </div>
                                <?php if ($i < 4): ?>
                                    <div class="w-12 h-1 <?php echo $i < $step ? 'bg-green-500' : 'bg-gray-200'; ?>"></div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
                <div class="space-y-6">
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                        <h2 class="text-lg font-semibold text-yellow-800">Step 1: Check XAMPP Requirements</h2>
                        <ul class="mt-2 space-y-2">
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                XAMPP installed
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                Apache running on port 80
                            </li>
                            <li class="flex items-center">
                                <i class="fas <?php echo checkDatabaseConnection() ? 'fa-check-circle text-green-500' : 'fa-times-circle text-red-500'; ?> mr-2"></i>
                                MySQL running on port 3306
                            </li>
                        </ul>
                    </div>

                    <form method="POST" class="space-y-4">
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                            Check MySQL Connection
                        </button>
                    </form>
                </div>

            <?php elseif ($step == 2): ?>
                <div class="space-y-6">
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                        <h2 class="text-lg font-semibold text-yellow-800">Step 2: Create Database</h2>
                        <p class="mt-2">We will create a new database named 'loan_management'.</p>
                    </div>

                    <form method="POST" class="space-y-4">
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                            Create Database
                        </button>
                    </form>
                </div>

            <?php elseif ($step == 3): ?>
                <div class="space-y-6">
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                        <h2 class="text-lg font-semibold text-yellow-800">Step 3: Setup Database Tables</h2>
                        <p class="mt-2">We will create all necessary tables and the default admin account.</p>
                    </div>

                    <form method="POST" class="space-y-4">
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">
                            Setup Database Tables
                        </button>
                    </form>
                </div>

            <?php elseif ($step == 4): ?>
                <div class="space-y-6">
                    <div class="bg-green-100 border-l-4 border-green-500 p-4">
                        <h2 class="text-lg font-semibold text-green-800">Installation Complete!</h2>
                        <div class="mt-4">
                            <p class="font-semibold">Default Admin Account:</p>
                            <ul class="mt-2 space-y-1">
                                <li>Username: admin</li>
                                <li>Password: admin123</li>
                            </ul>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                        <h3 class="font-semibold text-yellow-800">Important Security Steps:</h3>
                        <ol class="mt-2 list-decimal list-inside space-y-1">
                            <li>Change the default admin password immediately</li>
                            <li>Delete install.php after successful installation</li>
                            <li>Set proper file permissions</li>
                            <li>Configure error reporting for production</li>
                        </ol>
                    </div>

                    <div class="flex space-x-4">
                        <a href="/auth/login.php" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 text-center">
                            Go to Login
                        </a>
                        <a href="/setup.php" class="flex-1 bg-gray-600 text-white py-2 px-4 rounded hover:bg-gray-700 text-center">
                            System Check
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
