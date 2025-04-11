<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-6 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="/" class="text-xl font-bold">LoanMS</a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <a href="/admin/dashboard.php" class="hover:text-blue-200">Dashboard</a>
                            <a href="/admin/manage_users.php" class="hover:text-blue-200">Users</a>
                            <a href="/admin/manage_loans.php" class="hover:text-blue-200">Loans</a>
                        <?php else: ?>
                            <a href="/user/dashboard.php" class="hover:text-blue-200">Dashboard</a>
                            <a href="/user/apply_loan.php" class="hover:text-blue-200">Apply for Loan</a>
                            <a href="/user/view_loans.php" class="hover:text-blue-200">My Loans</a>
                        <?php endif; ?>
                        <a href="/auth/logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</a>
                    <?php else: ?>
                        <a href="/auth/login.php" class="hover:text-blue-200">Login</a>
                        <a href="/auth/register.php" class="bg-white text-blue-600 hover:bg-blue-50 px-4 py-2 rounded">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-6 py-8">
        <?php
        if (isset($_SESSION['alert'])) {
            displayAlert($_SESSION['alert']['message'], $_SESSION['alert']['type'] ?? 'success');
            unset($_SESSION['alert']);
        }
        ?>
