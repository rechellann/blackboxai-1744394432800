<?php
require_once '../includes/header.php';
requireAdmin();

$loan_id = $_GET['id'] ?? null;
if (!$loan_id) {
    header('Location: manage_loans.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loan_amount = $_POST['loan_amount'];
    $interest_rate = $_POST['interest_rate'];
    $term_months = $_POST['term_months'];
    $purpose = $_POST['purpose'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("
            UPDATE loans 
            SET loan_amount = ?, interest_rate = ?, term_months = ?, purpose = ?, status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$loan_amount, $interest_rate, $term_months, $purpose, $status, $loan_id]);
        
        $_SESSION['alert'] = [
            'message' => 'Loan updated successfully.',
            'type' => 'success'
        ];
        header('Location: manage_loans.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['alert'] = [
            'message' => 'Error updating loan.',
            'type' => 'error'
        ];
    }
}

// Fetch loan data
try {
    $stmt = $pdo->prepare("
        SELECT l.*, u.username, u.full_name 
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
} catch (PDOException $e) {
    $_SESSION['alert'] = [
        'message' => 'Error fetching loan data.',
        'type' => 'error'
    ];
    header('Location: manage_loans.php');
    exit();
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Edit Loan</h1>
            <div class="space-x-4">
                <a href="view_loan.php?id=<?php echo $loan_id; ?>" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-eye mr-2"></i>View Details
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

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-700">Borrower Information</h2>
                <p class="text-gray-600">
                    Name: <span class="font-medium"><?php echo htmlspecialchars($loan['full_name']); ?></span><br>
                    Username: <span class="font-medium"><?php echo htmlspecialchars($loan['username']); ?></span>
                </p>
            </div>

            <form action="edit_loan.php?id=<?php echo $loan_id; ?>" method="POST" class="space-y-6">
                <div>
                    <label for="loan_amount" class="block text-sm font-medium text-gray-700">Loan Amount</label>
                    <input type="number" step="0.01" name="loan_amount" id="loan_amount" required
                           value="<?php echo $loan['loan_amount']; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="interest_rate" class="block text-sm font-medium text-gray-700">Interest Rate (%)</label>
                    <input type="number" step="0.01" name="interest_rate" id="interest_rate" required
                           value="<?php echo $loan['interest_rate']; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="term_months" class="block text-sm font-medium text-gray-700">Term (Months)</label>
                    <input type="number" name="term_months" id="term_months" required
                           value="<?php echo $loan['term_months']; ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose</label>
                    <textarea name="purpose" id="purpose" required rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($loan['purpose']); ?></textarea>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="pending" <?php echo $loan['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $loan['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $loan['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="paid" <?php echo $loan['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                    </select>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
