<?php
require_once '../includes/header.php';
requireLogin();

$loan_id = $_GET['id'] ?? null;
if (!$loan_id) {
    header('Location: view_loans.php');
    exit();
}

// Fetch loan details with payment history
try {
    $stmt = $pdo->prepare("
        SELECT l.*, 
               COALESCE((SELECT SUM(amount_paid) FROM payments WHERE loan_id = l.id AND payment_status = 'completed'), 0) as total_paid
        FROM loans l
        WHERE l.id = ? AND l.user_id = ?
    ");
    $stmt->execute([$loan_id, $_SESSION['user_id']]);
    $loan = $stmt->fetch();

    if (!$loan) {
        $_SESSION['alert'] = [
            'message' => 'Loan not found or unauthorized access.',
            'type' => 'error'
        ];
        header('Location: view_loans.php');
        exit();
    }

    // Calculate remaining amount and monthly payment
    $remaining_amount = $loan['loan_amount'] - $loan['total_paid'];
    $monthly_payment = ($loan['loan_amount'] * (1 + ($loan['interest_rate'] / 100))) / $loan['term_months'];

    // Fetch payment history
    $stmt = $pdo->prepare("
        SELECT * FROM payments 
        WHERE loan_id = ? 
        ORDER BY payment_date DESC
    ");
    $stmt->execute([$loan_id]);
    $payments = $stmt->fetchAll();

} catch (PDOException $e) {
    $_SESSION['alert'] = [
        'message' => 'Error fetching loan details.',
        'type' => 'error'
    ];
    header('Location: view_loans.php');
    exit();
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Loan Details</h1>
            <div class="space-x-4">
                <?php if ($loan['status'] === 'approved' && $remaining_amount > 0): ?>
                    <a href="make_payment.php?loan_id=<?php echo $loan_id; ?>" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-credit-card mr-2"></i>Make Payment
                    </a>
                <?php endif; ?>
                <a href="view_loans.php" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Loans
                </a>
            </div>
        </div>

        <?php if (!empty($_SESSION['alert'])): ?>
            <div class="mb-4 p-4 rounded <?php echo $_SESSION['alert']['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php 
                echo $_SESSION['alert']['message'];
                unset($_SESSION['alert']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Loan Summary -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Loan Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                    <div>
                        <span class="text-gray-600">Loan Amount:</span>
                        <span class="font-semibold"><?php echo formatCurrency($loan['loan_amount']); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Interest Rate:</span>
                        <span class="font-semibold"><?php echo $loan['interest_rate']; ?>%</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Term:</span>
                        <span class="font-semibold"><?php echo $loan['term_months']; ?> months</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Monthly Payment:</span>
                        <span class="font-semibold"><?php echo formatCurrency($monthly_payment); ?></span>
                    </div>
                </div>
                <div class="space-y-3">
                    <div>
                        <span class="text-gray-600">Status:</span>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getStatusBadgeClass($loan['status']); ?>">
                            <?php echo ucfirst($loan['status']); ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-600">Application Date:</span>
                        <span class="font-semibold"><?php echo date('M d, Y', strtotime($loan['created_at'])); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-600">Purpose:</span>
                        <p class="mt-1"><?php echo htmlspecialchars($loan['purpose']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Progress -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Payment Progress</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-green-100 p-4 rounded">
                    <span class="text-green-700">Total Paid:</span>
                    <span class="block text-2xl font-bold text-green-700"><?php echo formatCurrency($loan['total_paid']); ?></span>
                </div>
                <div class="bg-blue-100 p-4 rounded">
                    <span class="text-blue-700">Remaining Amount:</span>
                    <span class="block text-2xl font-bold text-blue-700"><?php echo formatCurrency($remaining_amount); ?></span>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <?php $progress = ($loan['total_paid'] / $loan['loan_amount']) * 100; ?>
            <div class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold inline-block text-blue-600">
                            Payment Progress
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold inline-block text-blue-600">
                            <?php echo round($progress); ?>%
                        </span>
                    </div>
                </div>
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                    <div style="width:<?php echo $progress; ?>%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500"></div>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Payment History</h2>
            <?php if (empty($payments)): ?>
                <p class="text-gray-500 text-center py-4">No payments recorded yet.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('M d, Y', strtotime($payment['payment_date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo formatCurrency($payment['amount_paid']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $payment['payment_status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                            <?php echo ucfirst($payment['payment_status']); ?>
                                        </span>
                                        <?php if ($payment['payment_status'] === 'completed'): ?>
                                            <a href="receipt.php?payment_id=<?php echo $payment['id']; ?>" class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-receipt"></i> View Receipt
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
