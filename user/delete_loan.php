<?php
require_once '../includes/header.php';
requireLogin();

// Get loan ID from URL
$loan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify the loan exists and belongs to the user
try {
    $stmt = $pdo->prepare("
        SELECT id, status 
        FROM loans 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$loan_id, $_SESSION['user_id']]);
    $loan = $stmt->fetch();

    if (!$loan) {
        $_SESSION['alert'] = [
            'message' => 'Loan not found or access denied.',
            'type' => 'error'
        ];
        header('Location: /user/dashboard.php');
        exit();
    }

    // Only allow deletion of pending loans
    if ($loan['status'] !== 'pending') {
        $_SESSION['alert'] = [
            'message' => 'Only pending loans can be deleted.',
            'type' => 'error'
        ];
        header('Location: /user/view_loan.php?id=' . $loan_id);
        exit();
    }

    // Handle deletion confirmation
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
        $stmt = $pdo->prepare("DELETE FROM loans WHERE id = ? AND user_id = ? AND status = 'pending'");
        $stmt->execute([$loan_id, $_SESSION['user_id']]);

        $_SESSION['alert'] = [
            'message' => 'Loan application has been successfully deleted.',
            'type' => 'success'
        ];
        header('Location: /user/dashboard.php');
        exit();
    }

} catch (PDOException $e) {
    $_SESSION['alert'] = [
        'message' => 'Error processing your request.',
        'type' => 'error'
    ];
    header('Location: /user/dashboard.php');
    exit();
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="text-center mb-6">
                <div class="text-red-600 mb-4">
                    <i class="fas fa-exclamation-triangle text-5xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Delete Loan Application</h1>
                <p class="text-gray-600">Are you sure you want to delete this loan application? This action cannot be undone.</p>
            </div>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="confirm_delete" value="yes">
                
                <div class="flex space-x-4">
                    <button type="submit" 
                            class="flex-1 bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 transition duration-200">
                        <i class="fas fa-trash mr-2"></i>Yes, Delete Application
                    </button>
                    <a href="/user/view_loan.php?id=<?php echo $loan_id; ?>" 
                       class="flex-1 bg-gray-500 text-white py-3 px-4 rounded-lg hover:bg-gray-600 transition duration-200 text-center">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </form>

            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-semibold mb-2">Important Notes:</h3>
                <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                    <li>This will permanently delete your loan application</li>
                    <li>You can only delete pending applications</li>
                    <li>You can submit a new application after deletion</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
