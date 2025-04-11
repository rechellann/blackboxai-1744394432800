<?php
require_once '../includes/header.php';
requireAdmin();

// Handle loan deletion
if (isset($_POST['delete_loan'])) {
    $loan_id = $_POST['loan_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM loans WHERE id = ?");
        $stmt->execute([$loan_id]);
        $_SESSION['alert'] = [
            'message' => 'Loan deleted successfully.',
            'type' => 'success'
        ];
    } catch (PDOException $e) {
        $_SESSION['alert'] = [
            'message' => 'Error deleting loan.',
            'type' => 'error'
        ];
    }
    header('Location: manage_loans.php');
    exit();
}

// Handle status update
if (isset($_POST['update_status'])) {
    $loan_id = $_POST['loan_id'];
    $status = $_POST['status'];
    try {
        $stmt = $pdo->prepare("UPDATE loans SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$status, $loan_id]);
        $_SESSION['alert'] = [
            'message' => 'Loan status updated successfully.',
            'type' => 'success'
        ];
    } catch (PDOException $e) {
        $_SESSION['alert'] = [
            'message' => 'Error updating loan status.',
            'type' => 'error'
        ];
    }
    header('Location: manage_loans.php');
    exit();
}

// Get all loans with user information
try {
    $stmt = $pdo->query("
        SELECT l.*, u.username, u.full_name, u.email
        FROM loans l
        JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC
    ");
    $loans = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['alert'] = [
        'message' => 'Error fetching loans.',
        'type' => 'error'
    ];
    $loans = [];
}

// Calculate statistics
$totalAmount = array_sum(array_column($loans, 'loan_amount'));
$pendingLoans = count(array_filter($loans, fn($loan) => $loan['status'] === 'pending'));
$approvedLoans = count(array_filter($loans, fn($loan) => $loan['status'] === 'approved'));
$rejectedLoans = count(array_filter($loans, fn($loan) => $loan['status'] === 'rejected'));
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Manage Loans</h1>
        <a href="/admin/dashboard.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-dollar-sign text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Total Amount</p>
                    <p class="text-2xl font-semibold"><?php echo formatCurrency($totalAmount); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Pending</p>
                    <p class="text-2xl font-semibold"><?php echo $pendingLoans; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Approved</p>
                    <p class="text-2xl font-semibold"><?php echo $approvedLoans; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-times text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Rejected</p>
                    <p class="text-2xl font-semibold"><?php echo $rejectedLoans; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Loans Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <h2 class="text-xl font-semibold mb-4">All Loan Applications</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loan Details</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($loans as $loan): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($loan['full_name']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($loan['email']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">Amount: <?php echo formatCurrency($loan['loan_amount']); ?></div>
                                <div class="text-sm text-gray-500">
                                    Term: <?php echo htmlspecialchars($loan['term_months']); ?> months<br>
                                    Rate: <?php echo htmlspecialchars($loan['interest_rate']); ?>%
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getStatusBadgeClass($loan['status']); ?>">
                                    <?php echo ucfirst($loan['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($loan['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                <a href="view_loan.php?id=<?php echo $loan['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit_loan.php?id=<?php echo $loan['id']; ?>" class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="manage_loans.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this loan?');">
                                    <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                    <button type="submit" name="delete_loan" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
