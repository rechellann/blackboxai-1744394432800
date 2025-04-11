<?php
require_once '../includes/header.php';
requireLogin();

$loan_id = $_GET['loan_id'] ?? null;
if (!$loan_id) {
    header('Location: view_loans.php');
    exit();
}

// Fetch loan details
try {
    $stmt = $pdo->prepare("
        SELECT l.*, 
               COALESCE((SELECT SUM(amount_paid) FROM payments WHERE loan_id = l.id AND payment_status = 'completed'), 0) as total_paid
        FROM loans l
        WHERE l.id = ? AND l.user_id = ? AND l.status = 'approved'
    ");
    $stmt->execute([$loan_id, $_SESSION['user_id']]);
    $loan = $stmt->fetch();

    if (!$loan) {
        $_SESSION['alert'] = [
            'message' => 'Invalid loan or unauthorized access.',
            'type' => 'error'
        ];
        header('Location: view_loans.php');
        exit();
    }

    $remaining_amount = $loan['loan_amount'] - $loan['total_paid'];

} catch (PDOException $e) {
    $_SESSION['alert'] = [
        'message' => 'Error fetching loan details.',
        'type' => 'error'
    ];
    header('Location: view_loans.php');
    exit();
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $payment_method = $_POST['payment_method'];

    if ($amount <= 0 || $amount > $remaining_amount) {
        $_SESSION['alert'] = [
            'message' => 'Invalid payment amount.',
            'type' => 'error'
        ];
    } else {
        try {
            $pdo->beginTransaction();

            // Record the payment
            $stmt = $pdo->prepare("
                INSERT INTO payments (loan_id, amount_paid, payment_method, payment_status)
                VALUES (?, ?, ?, 'completed')
            ");
            $stmt->execute([$loan_id, $amount, $payment_method]);

            // Check if loan is fully paid
            $new_total_paid = $loan['total_paid'] + $amount;
            if ($new_total_paid >= $loan['loan_amount']) {
                $stmt = $pdo->prepare("UPDATE loans SET status = 'paid' WHERE id = ?");
                $stmt->execute([$loan_id]);
            }

            // Get the payment ID
            $payment_id = $pdo->lastInsertId();
            
            $pdo->commit();

            $_SESSION['alert'] = [
                'message' => 'Payment processed successfully.',
                'type' => 'success'
            ];
            header('Location: receipt.php?payment_id=' . $payment_id);
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['alert'] = [
                'message' => 'Error processing payment.',
                'type' => 'error'
            ];
        }
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Make Payment</h1>
            <a href="view_loans.php" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Loans
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

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Loan Summary</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600">Loan Amount:</p>
                        <p class="text-xl font-semibold"><?php echo formatCurrency($loan['loan_amount']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Total Paid:</p>
                        <p class="text-xl font-semibold text-green-600"><?php echo formatCurrency($loan['total_paid']); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Remaining Amount:</p>
                        <p class="text-xl font-semibold text-blue-600"><?php echo formatCurrency($remaining_amount); ?></p>
                    </div>
                    <div>
                        <p class="text-gray-600">Status:</p>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getStatusBadgeClass($loan['status']); ?>">
                            <?php echo ucfirst($loan['status']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <form action="make_payment.php?loan_id=<?php echo $loan_id; ?>" method="POST" class="space-y-6">
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700">Payment Amount</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="amount" id="amount" step="0.01" max="<?php echo $remaining_amount; ?>"
                               required class="pl-7 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="0.00">
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Maximum payment: <?php echo formatCurrency($remaining_amount); ?></p>
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                    <select name="payment_method" id="payment_method" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select payment method</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        <i class="fas fa-credit-card mr-2"></i>Process Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
