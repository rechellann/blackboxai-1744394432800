<?php
$error_code = $_SERVER['REDIRECT_STATUS'] ?? 404;
$error_messages = [
    400 => 'Bad Request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Page Not Found',
    500 => 'Internal Server Error',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout'
];

$error_title = $error_messages[$error_code] ?? 'Unknown Error';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $error_code; ?> - <?php echo $error_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="text-center mb-8">
                    <?php if ($error_code == 404): ?>
                        <i class="fas fa-search text-6xl text-blue-500 mb-4"></i>
                    <?php elseif ($error_code == 403): ?>
                        <i class="fas fa-lock text-6xl text-red-500 mb-4"></i>
                    <?php elseif ($error_code == 500): ?>
                        <i class="fas fa-exclamation-triangle text-6xl text-yellow-500 mb-4"></i>
                    <?php else: ?>
                        <i class="fas fa-exclamation-circle text-6xl text-gray-500 mb-4"></i>
                    <?php endif; ?>
                    
                    <h1 class="text-4xl font-bold text-gray-800 mb-2">Error <?php echo $error_code; ?></h1>
                    <p class="text-xl text-gray-600"><?php echo $error_title; ?></p>
                </div>

                <div class="text-center mb-8">
                    <?php if ($error_code == 404): ?>
                        <p class="text-gray-600">The page you're looking for doesn't exist or has been moved.</p>
                    <?php elseif ($error_code == 403): ?>
                        <p class="text-gray-600">You don't have permission to access this resource.</p>
                    <?php elseif ($error_code == 500): ?>
                        <p class="text-gray-600">Something went wrong on our servers. Please try again later.</p>
                    <?php else: ?>
                        <p class="text-gray-600">An unexpected error occurred. Please try again later.</p>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col space-y-4">
                    <a href="/" class="bg-blue-600 text-white text-center py-2 px-4 rounded hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-home mr-2"></i>Go to Homepage
                    </a>
                    <?php if (isset($_SERVER['HTTP_REFERER'])): ?>
                        <a href="<?php echo htmlspecialchars($_SERVER['HTTP_REFERER']); ?>" 
                           class="bg-gray-600 text-white text-center py-2 px-4 rounded hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>Go Back
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t">
                <p class="text-center text-gray-600 text-sm">
                    If you believe this is a mistake, please contact our support team.
                </p>
            </div>
        </div>
    </div>

    <?php
    // Log error for monitoring
    $log_message = date('Y-m-d H:i:s') . " | Error {$error_code} | " . 
                  $_SERVER['REQUEST_URI'] . " | " . 
                  ($_SERVER['HTTP_REFERER'] ?? 'No referrer') . " | " .
                  $_SERVER['REMOTE_ADDR'] . "\n";
    error_log($log_message, 3, __DIR__ . '/logs/error.log');
    ?>
</body>
</html>
