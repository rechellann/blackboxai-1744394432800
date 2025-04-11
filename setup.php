<?php
// Display all errors for debugging during setup
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$requirements_met = true;
$errors = [];
$warnings = [];

function checkRequirement($check, $message, $critical = true) {
    global $requirements_met, $errors, $warnings;
    if (!$check) {
        if ($critical) {
            $requirements_met = false;
            $errors[] = $message;
        } else {
            $warnings[] = $message;
        }
        return false;
    }
    return true;
}

// Start HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Management System Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-3xl font-bold mb-6">Loan Management System Setup</h1>
            
            <div class="space-y-6">
                <?php
                // 1. Check PHP version
                echo "<div class='border rounded-lg p-4 " . (version_compare(PHP_VERSION, '7.4.0', '>=') ? 'bg-green-50' : 'bg-red-50') . "'>";
                echo "<h2 class='font-semibold mb-2'>PHP Version Check</h2>";
                if (checkRequirement(
                    version_compare(PHP_VERSION, '7.4.0', '>='),
                    "PHP 7.4 or higher is required. Current version: " . PHP_VERSION
                )) {
                    echo "<p class='text-green-600'>✓ PHP Version " . PHP_VERSION . " (OK)</p>";
                }
                echo "</div>";

                // 2. Check PHP Extensions
                echo "<div class='border rounded-lg p-4'>";
                echo "<h2 class='font-semibold mb-2'>Required PHP Extensions</h2>";
                $extensions = [
                    'pdo' => 'PDO Extension',
                    'pdo_mysql' => 'PDO MySQL Extension',
                    'json' => 'JSON Extension',
                    'session' => 'Session Extension'
                ];

                foreach ($extensions as $ext => $name) {
                    $loaded = extension_loaded($ext);
                    echo "<div class='mb-2 " . ($loaded ? 'text-green-600' : 'text-red-600') . "'>";
                    echo ($loaded ? "✓" : "✗") . " {$name}";
                    echo "</div>";
                    
                    checkRequirement($loaded, "{$name} is required but not installed.");
                }
                echo "</div>";

                // Display MySQL Requirements
                echo "<div class='border rounded-lg p-4 bg-blue-50'>";
                echo "<h2 class='font-semibold mb-2'>MySQL Requirements</h2>";
                echo "<ul class='list-disc list-inside space-y-2'>";
                echo "<li>MySQL Server 5.7+ or MariaDB 10.2+</li>";
                echo "<li>A MySQL user with CREATE, ALTER, SELECT, INSERT, UPDATE, DELETE permissions</li>";
                echo "<li>An empty database created for the application</li>";
                echo "</ul>";
                echo "<div class='mt-4 p-4 bg-yellow-50 rounded'>";
                echo "<p class='font-semibold'>Before proceeding, make sure you:</p>";
                echo "<ol class='list-decimal list-inside mt-2 space-y-1'>";
                echo "<li>Have MySQL server installed and running</li>";
                echo "<li>Have created a database for the application</li>";
                echo "<li>Have updated the database credentials in config/database.php</li>";
                echo "</ol>";
                echo "</div>";
                echo "</div>";

                // Display Errors if any
                if (!empty($errors)) {
                    echo "<div class='border border-red-200 rounded-lg p-4 bg-red-50'>";
                    echo "<h2 class='font-semibold text-red-700 mb-2'>Critical Issues Found</h2>";
                    echo "<ul class='list-disc list-inside space-y-1 text-red-600'>";
                    foreach ($errors as $error) {
                        echo "<li>{$error}</li>";
                    }
                    echo "</ul>";
                    echo "</div>";
                }

                // Display Warnings if any
                if (!empty($warnings)) {
                    echo "<div class='border border-yellow-200 rounded-lg p-4 bg-yellow-50'>";
                    echo "<h2 class='font-semibold text-yellow-700 mb-2'>Warnings</h2>";
                    echo "<ul class='list-disc list-inside space-y-1 text-yellow-600'>";
                    foreach ($warnings as $warning) {
                        echo "<li>{$warning}</li>";
                    }
                    echo "</ul>";
                    echo "</div>";
                }

                // Display Next Steps
                if ($requirements_met) {
                    echo "<div class='border border-green-200 rounded-lg p-4 bg-green-50'>";
                    echo "<h2 class='font-semibold text-green-700 mb-2'>Next Steps</h2>";
                    echo "<p class='mb-4'>All basic requirements are met. Please follow these steps:</p>";
                    echo "<ol class='list-decimal list-inside space-y-2'>";
                    echo "<li>Update database configuration in config/database.php</li>";
                    echo "<li>Run the database setup script</li>";
                    echo "<li>Set appropriate file permissions</li>";
                    echo "<li>Remove this setup file after installation</li>";
                    echo "</ol>";
                    echo "<div class='mt-6'>";
                    echo "<a href='config/db_setup.php' class='bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700'>Run Database Setup</a>";
                    echo "</div>";
                    echo "</div>";
                } else {
                    echo "<div class='border border-red-200 rounded-lg p-4 bg-red-50'>";
                    echo "<h2 class='font-semibold text-red-700 mb-2'>Setup Blocked</h2>";
                    echo "<p>Please fix the critical issues above before proceeding with the installation.</p>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
