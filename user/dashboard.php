<?php
require_once '../includes/header.php';
requireLogin();

// Get user's loan statistics
try {
    // Total Loans
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $totalLoans = $stmt->fetchColumn();

    // Active Loans
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = ? AND status = 'approved'");
    $stmt->execute([$_SESSION['user_id']]);
    $activeLoans = $stmt->fetchColumn();

    // Total Amount Borrowed
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(loan_amount), 0) FROM loans WHERE user_id = ? AND status = 'approved'");
    $stmt->execute([$_SESSION['user_id']]);
    $totalBorrowed = $stmt->fetchColumn();

    // Total Amount Paid
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(p.amount_paid), 0)
        FROM payments p
        JOIN loans l ON p.loan_id = l.id
        WHERE l.user_id = ? AND p.payment_status = 'completed'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $totalPaid = $stmt->fetchColumn();

    // Recent Loans
    $stmt = $pdo->prepare("
        SELECT * FROM loans 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recentLoans = $stmt->fetchAll();

} catch (PDOException $e) {
    $_SESSION['alert'] = [
        'message' => 'Error fetching dashboard data.',
        'type' => 'error'
    ];
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h1>
        <a href="apply_loan.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>Apply for New Loan
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Loans Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-file-invoice-dollar text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Total Loans</p>
                    <p class="text-2xl font-semibold"><?php echo $totalLoans; ?></p>
                </div>
            </div>
        </div>

        <!-- Active Loans Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Active Loans</p>
                    <p class="text-2xl font-semibold"><?php echo $activeLoans; ?></p>
                </div>
            </div>
        </div>

        <!-- Total Borrowed Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-dollar-sign text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Total Borrowed</p>
                    <p class="text-2xl font-semibold"><?php echo formatCurrency($totalBorrowed); ?></p>
                </div>
            </div>
        </div>

        <!-- Total Paid Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-credit-card text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Total Paid</p>
                    <p class="text-2xl font-semibold"><?php echo formatCurrency($totalPaid); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-4">
                <a href="view_loans.php" class="bg-blue-600 text-white rounded-lg px-4 py-2 text-center hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-list mr-2"></i>View My Loans
                </a>
                <a href="apply_loan.php" class="bg-green-600 text-white rounded-lg px-4 py-2 text-center hover:bg-green-700 transition duration-200">
                    <i class="fas fa-plus-circle mr-2"></i>New Loan
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Account Status</h2>
            <div class="space-y-2">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span>Account in good standing</span>
                </div>
                <?php if ($activeLoans > 0): ?>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                    <span>You have active loans</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Loans -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Recent Loans</h2>
            <a href="view_loans.php" class="text-blue-600 hover:text-blue-800">View All</a>
        </div>
        <?php if (empty($recentLoans)): ?>
            <p class="text-gray-500 text-center py-4">No loans found. <a href="apply_loan.php" class="text-blue-600 hover:text-blue-800">Apply for your first loan</a></p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recentLoans as $loan): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo formatCurrency($loan['loan_amount']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo $loan['term_months']; ?> months</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getStatusBadgeClass($loan['status']); ?>">
                                    <?php echo ucfirst($loan['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($loan['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if ($loan['status'] === 'approved'): ?>
                                    <a href="make_payment.php?loan_id=<?php echo $loan['id']; ?>" class="text-green-600 hover:text-green-900 mr-3">Make Payment</a>
                                <?php endif; ?>
                                <a href="view_loan.php?id=<?php echo $loan['id']; ?>" class="text-blue-600 hover:text-blue-900">View Details</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
