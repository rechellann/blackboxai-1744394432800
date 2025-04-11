<?php
require_once '../includes/header.php';
requireLogin();

// Fetch user's loans
try {
    $stmt = $pdo->prepare("
        SELECT * FROM loans 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $loans = $stmt->fetchAll();

    // Get payment history for each loan
    foreach ($loans as &$loan) {
        $stmt = $pdo->prepare("
            SELECT SUM(amount_paid) as total_paid 
            FROM payments 
            WHERE loan_id = ? AND payment_status = 'completed'
        ");
        $stmt->execute([$loan['id']]);
        $result = $stmt->fetch();
        $loan['total_paid'] = $result['total_paid'] ?? 0;
        $loan['remaining_amount'] = $loan['loan_amount'] - $loan['total_paid'];
    }
} catch (PDOException $e) {
    $_SESSION['alert'] = [
        'message' => 'Error fetching loans.',
        'type' => 'error'
    ];
    $loans = [];
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">My Loans</h1>
        <a href="apply_loan.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Apply for New Loan
        </a>
    </div>

    <?php if (!empty($_SESSION['alert'])): ?>
        <div class="mb-4 p-4 rounded <?php echo $_SESSION['alert']['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php 
            echo $_SESSION['alert']['message'];
            unset($_SESSION['alert']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($loans)): ?>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <p class="text-gray-500 mb-4">You haven't applied for any loans yet.</p>
            <a href="apply_loan.php" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-plus-circle mr-2"></i>Apply for your first loan
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($loans as $loan): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">
                                <?php echo formatCurrency($loan['loan_amount']); ?>
                            </h3>
                            <p class="text-sm text-gray-500">
                                <?php echo $loan['term_months']; ?> months @ <?php echo $loan['interest_rate']; ?>%
                            </p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo getStatusBadgeClass($loan['status']); ?>">
                            <?php echo ucfirst($loan['status']); ?>
                        </span>
                    </div>

                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Paid:</span>
                            <span class="font-medium text-green-600"><?php echo formatCurrency($loan['total_paid']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Remaining:</span>
                            <span class="font-medium text-blue-600"><?php echo formatCurrency($loan['remaining_amount']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Applied On:</span>
                            <span class="text-gray-600"><?php echo date('M d, Y', strtotime($loan['created_at'])); ?></span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Purpose:</span>
                            <span class="text-sm font-medium"><?php echo htmlspecialchars($loan['purpose']); ?></span>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2">
                        <?php if ($loan['status'] === 'approved'): ?>
                            <a href="make_payment.php?loan_id=<?php echo $loan['id']; ?>" 
                               class="block w-full bg-green-600 text-white text-center px-4 py-2 rounded hover:bg-green-700">
                                <i class="fas fa-credit-card mr-2"></i>Make Payment
                            </a>
                        <?php endif; ?>
                        <?php if ($loan['total_paid'] > 0): ?>
                            <a href="view_loan.php?id=<?php echo $loan['id']; ?>#payments" 
                               class="block w-full bg-blue-600 text-white text-center px-4 py-2 rounded hover:bg-blue-700">
                                <i class="fas fa-receipt mr-2"></i>View Payment History
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
