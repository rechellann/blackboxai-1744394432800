<?php
require_once '../includes/header.php';
requireAdmin();

// Get statistics
try {
    // Total Users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $totalUsers = $stmt->fetchColumn();

    // Total Loans
    $stmt = $pdo->query("SELECT COUNT(*) FROM loans");
    $totalLoans = $stmt->fetchColumn();

    // Pending Loans
    $stmt = $pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'pending'");
    $pendingLoans = $stmt->fetchColumn();

    // Total Amount Loaned
    $stmt = $pdo->query("SELECT COALESCE(SUM(loan_amount), 0) FROM loans WHERE status = 'approved'");
    $totalAmount = $stmt->fetchColumn();

    // Recent Loans
    $stmt = $pdo->query("
        SELECT l.*, u.username, u.full_name 
        FROM loans l 
        JOIN users u ON l.user_id = u.id 
        ORDER BY l.created_at DESC 
        LIMIT 5
    ");
    $recentLoans = $stmt->fetchAll();

} catch (PDOException $e) {
    $_SESSION['alert'] = [
        'message' => 'Error fetching dashboard data.',
        'type' => 'error'
    ];
}
?>

<div class="container mx-auto px-4">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Admin Dashboard</h1>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Total Users</p>
                    <p class="text-2xl font-semibold"><?php echo $totalUsers; ?></p>
                </div>
            </div>
        </div>

        <!-- Total Loans Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-file-invoice-dollar text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Total Loans</p>
                    <p class="text-2xl font-semibold"><?php echo $totalLoans; ?></p>
                </div>
            </div>
        </div>

        <!-- Pending Loans Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Pending Loans</p>
                    <p class="text-2xl font-semibold"><?php echo $pendingLoans; ?></p>
                </div>
            </div>
        </div>

        <!-- Total Amount Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-dollar-sign text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Total Amount Loaned</p>
                    <p class="text-2xl font-semibold"><?php echo formatCurrency($totalAmount); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-4">
                <a href="/admin/manage_loans.php" class="bg-blue-600 text-white rounded-lg px-4 py-2 text-center hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-plus-circle mr-2"></i>Manage Loans
                </a>
                <a href="/admin/manage_users.php" class="bg-green-600 text-white rounded-lg px-4 py-2 text-center hover:bg-green-700 transition duration-200">
                    <i class="fas fa-user-plus mr-2"></i>Manage Users
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">System Status</h2>
            <div class="space-y-2">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span>System is running normally</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span>Database connection is stable</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Loans -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Recent Loan Applications</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($recentLoans as $loan): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($loan['full_name']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($loan['username']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900"><?php echo formatCurrency($loan['loan_amount']); ?></div>
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
                            <a href="/admin/view_loan.php?id=<?php echo $loan['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                            <a href="/admin/edit_loan.php?id=<?php echo $loan['id']; ?>" class="text-green-600 hover:text-green-900">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4 text-right">
            <a href="/admin/manage_loans.php" class="text-blue-600 hover:text-blue-800">View all loans →</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
