<?php
session_start();
$checks = [];
$all_passed = true;

function checkStatus($condition, $message, $details = '') {
    global $all_passed;
    $status = $condition ? 'passed' : 'failed';
    if (!$condition) $all_passed = false;
    return [
        'status' => $status,
        'message' => $message,
        'details' => $details
    ];
}

// Check PHP Version
$checks['php'] = checkStatus(
    version_compare(PHP_VERSION, '7.4.0', '>='),
    'PHP Version Check',
    'Current version: ' . PHP_VERSION
);

// Check Required Extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'session'];
$missing_extensions = [];
foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}
$checks['extensions'] = checkStatus(
    empty($missing_extensions),
    'PHP Extensions Check',
    empty($missing_extensions) ? 'All required extensions installed' : 'Missing: ' . implode(', ', $missing_extensions)
);

// Check Database Connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=loan_management", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $checks['database'] = checkStatus(true, 'Database Connection', 'Successfully connected to loan_management database');
} catch (PDOException $e) {
    $checks['database'] = checkStatus(false, 'Database Connection', 'Error: ' . $e->getMessage() . '. Please ensure the PDO MySQL extension is enabled in your php.ini file.');
}

// Check Required Tables
if (isset($pdo)) {
    try {
        $required_tables = ['users', 'loans', 'payments'];
        $missing_tables = [];
        foreach ($required_tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if (!$stmt->fetch()) {
                $missing_tables[] = $table;
            }
        }
        $checks['tables'] = checkStatus(
            empty($missing_tables),
            'Database Tables Check',
            empty($missing_tables) ? 'All required tables exist' : 'Missing tables: ' . implode(', ', $missing_tables)
        );
    } catch (PDOException $e) {
        $checks['tables'] = checkStatus(false, 'Database Tables Check', 'Error: ' . $e->getMessage());
    }
}

// Check File Permissions
$required_paths = [
    'logs' => __DIR__ . '/logs',
    'config' => __DIR__ . '/config',
    '.htaccess' => __DIR__ . '/.htaccess'
];
$permission_issues = [];
foreach ($required_paths as $name => $path) {
    if (!file_exists($path)) {
        $permission_issues[] = "$name (missing)";
    } elseif (!is_readable($path)) {
        $permission_issues[] = "$name (not readable)";
    } elseif (is_dir($path) && !is_writable($path)) {
        $permission_issues[] = "$name (not writable)";
    }
}
$checks['permissions'] = checkStatus(
    empty($permission_issues),
    'File Permissions Check',
    empty($permission_issues) ? 'All files have correct permissions' : 'Issues found: ' . implode(', ', $permission_issues)
);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Installation Verification</h1>
                <p class="text-gray-600 mt-2">Checking system requirements and configuration</p>
            </div>

            <div class="space-y-6">
                <?php foreach ($checks as $check): ?>
                    <div class="border rounded-lg p-4 <?php echo $check['status'] === 'passed' ? 'bg-green-50' : 'bg-red-50'; ?>">
                        <div class="flex items-center justify-between">
                            <h2 class="font-semibold"><?php echo $check['message']; ?></h2>
                            <span class="<?php echo $check['status'] === 'passed' ? 'text-green-600' : 'text-red-600'; ?>">
                                <i class="fas <?php echo $check['status'] === 'passed' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                <?php echo ucfirst($check['status']); ?>
                            </span>
                        </div>
                        <p class="text-sm mt-2 <?php echo $check['status'] === 'passed' ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $check['details']; ?>
                        </p>
                    </div>
                <?php endforeach; ?>

                <?php if ($all_passed): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-green-700">
                                    All checks passed! The system is properly installed and configured.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-6">
                        <a href="/index.php" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            Go to Homepage
                        </a>
                    </div>
                <?php else: ?>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-yellow-700">
                                    Some checks failed. Please review the issues above and consult the installation guide.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-6 space-x-4">
                        <a href="/XAMPP_GUIDE.md" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            View Setup Guide
                        </a>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                            Check Again
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
