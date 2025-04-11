<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /user/dashboard.php');
        exit();
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function displayAlert($message, $type = 'success') {
    $class = ($type === 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
    echo "<div class='p-4 mb-4 rounded $class'>$message</div>";
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function calculateMonthlyPayment($loanAmount, $interestRate, $termMonths) {
    $monthlyRate = ($interestRate / 100) / 12;
    $payment = $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) / (pow(1 + $monthlyRate, $termMonths) - 1);
    return round($payment, 2);
}

function getStatusBadgeClass($status) {
    $classes = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'approved' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800',
        'paid' => 'bg-blue-100 text-blue-800'
    ];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
}
?>
