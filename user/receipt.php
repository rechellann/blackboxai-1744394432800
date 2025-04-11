<?php
require_once '../includes/header.php';
requireLogin();

$payment_id = $_GET['payment_id'] ?? null;
if (!$payment_id) {
    header('Location: view_loans.php');
    exit();
}

// Fetch payment and loan details
try {
    $stmt = $pdo->prepare("
        SELECT p.*, l.loan_amount, l.interest_rate, l.term_months, u.full_name, u.email
        FROM payments p
        JOIN loans l ON p.loan_id = l.id
        JOIN users u ON l.user_id = u.id
        WHERE p.id = ? AND l.user_id = ?
    ");
    $stmt->execute([$payment_id, $_SESSION['user_id']]);
    $payment = $stmt->fetch();

    if (!$payment) {
        $_SESSION['alert'] = [
            'message' => 'Payment not found or unauthorized access.',
            'type' => 'error'
        ];
        header('Location: view_loans.php');
        exit();
    }

} catch (PDOException $e) {
    $_SESSION['alert'] = [
        'message' => 'Error fetching payment details.',
        'type' => 'error'
    ];
    header('Location: view_loans.php');
    exit();
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Payment Receipt</h1>
            <div class="space-x-4">
                <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-print mr-2"></i>Print Receipt
                </button>
                <a href="view_loans.php" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Loans
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6" id="receipt">
            <!-- Receipt Header -->
            <div class="text-center mb-6 border-b pb-6">
                <h2 class="text-2xl font-bold text-gray-800">Payment Receipt</h2>
                <p class="text-gray-600">Transaction ID: <?php echo str_pad($payment_id, 8, '0', STR_PAD_LEFT); ?></p>
                <p class="text-gray-600"><?php echo date('F d, Y h:i A', strtotime($payment['payment_date'])); ?></p>
            </div>

            <!-- Customer Information -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2">Customer Information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600">Name:</p>
                        <p class="font-medium"><?php echo htmlspecialchars($payment['full_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Email:</p>
                        <p class="font-medium"><?php echo htmlspecialchars($payment['email']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2">Payment Details</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-600">Payment Amount:</p>
                            <p class="text-xl font-semibold text-green-600"><?php echo formatCurrency($payment['amount_paid']); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-600">Payment Method:</p>
                            <p class="font-medium"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></p>
                        </div>
                        <div>
                            <p class="text-gray-600">Payment Status:</p>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $payment['payment_status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo ucfirst($payment['payment_status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loan Information -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-2">Loan Information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600">Loan Amount:</p>
                        <p class="font-medium"><?php echo formatCurrency($payment['loan_amount']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Interest Rate:</p>
                        <p class="font-medium"><?php echo $payment['interest_rate']; ?>%</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Term:</p>
                        <p class="font-medium"><?php echo $payment['term_months']; ?> months</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-gray-500 text-sm mt-8 pt-6 border-t">
                <p>Thank you for your payment!</p>
                <p class="mt-2">For any questions, please contact our support team.</p>
                <p class="mt-4">© <?php echo date('Y'); ?> Loan Management System</p>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #receipt, #receipt * {
        visibility: visible;
    }
    #receipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print {
        display: none;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>
