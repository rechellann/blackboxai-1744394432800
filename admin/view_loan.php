<?php
require_once '../includes/header.php';
requireAdmin();

$loan_id = $_GET['id'] ?? null;
if (!$loan_id) {
    header('Location: manage_loans.php');
    exit();
}

// Fetch loan data with user information
try {
    $stmt = $pdo->prepare("
        SELECT l.*, u.username, u.full_name, u.email
        FROM loans l
        JOIN users u ON l.user_id = u.id
        WHERE l.id = ?
    ");
    $stmt->execute([$loan_id]);
    $loan = $stmt->fetch();
    
    if (!$loan) {
        $_SESSION['alert'] = [
            'message' => 'Loan not found.',
            'type' => 'error'
        ];
        header('Location: manage_loans.php');
        exit();
    }

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
        'message' => 'Error fetching loan data.',
        'type' => 'error'
    ];
    header('Location: manage_loans.php');
    exit();
}

// Calculate total amount paid
$total_paid = array_sum(array_column($payments, 'amount_paid'));
$remaining_amount = $loan['loan_amount'] - $total_paid;
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Loan Details</h1>
            <div class="space-x-4">
                <a href="edit_loan.php?id=<?php echo $loan_id; ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-edit mr-2"></i>Edit Loan
                </a>
                <a href="manage_loans.php" class="text-blue-600 hover:text-blue-800">
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

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-xl font-semibold mb-4">Loan Information</h2>
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
                            <span class="text-gray-600">Term (Months):</span>
                            <span class="font-semibold"><?php echo $loan['term_months']; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Status:</span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getStatusBadgeClass($loan['status']); ?>">
                                <?php echo ucfirst($loan['status']); ?>
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-600">Purpose:</span>
                            <p class="mt-1"><?php echo htmlspecialchars($loan['purpose']); ?></p>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold mb-4">Borrower Information</h2>
                    <div class="space-y-3">
                        <div>
                            <span class="text-gray-600">Name:</span>
                            <span class="font-semibold"><?php echo htmlspecialchars($loan['full_name']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Username:</span>
                            <span class="font-semibold"><?php echo htmlspecialchars($loan['username']); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-600">Email:</span>
                            <span class="font-semibold"><?php echo htmlspecialchars($loan['email']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Payment History</h2>
            <div class="mb-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-green-100 p-4 rounded">
                        <span class="text-green-700">Total Paid:</span>
                        <span class="block text-2xl font-bold text-green-700"><?php echo formatCurrency($total_paid); ?></span>
                    </div>
                    <div class="bg-blue-100 p-4 rounded">
                        <span class="text-blue-700">Remaining Amount:</span>
                        <span class="block text-2xl font-bold text-blue-700"><?php echo formatCurrency($remaining_amount); ?></span>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($payments)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo date('M d, Y', strtotime($payment['payment_date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo formatCurrency($payment['amount_paid']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $payment['payment_status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo ucfirst($payment['payment_status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">No payments recorded yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
