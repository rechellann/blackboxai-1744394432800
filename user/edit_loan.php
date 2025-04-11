<?php
require_once '../includes/header.php';
requireLogin();

// Get loan ID from URL
$loan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch loan details
try {
    $stmt = $pdo->prepare("
        SELECT * FROM loans 
        WHERE id = ? AND user_id = ? AND status = 'pending'
    ");
    $stmt->execute([$loan_id, $_SESSION['user_id']]);
    $loan = $stmt->fetch();

    if (!$loan) {
        $_SESSION['alert'] = [
            'message' => 'Loan not found or cannot be edited.',
            'type' => 'error'
        ];
        header('Location: /user/dashboard.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['alert'] = [
        'message' => 'Error fetching loan details.',
        'type' => 'error'
    ];
    header('Location: /user/dashboard.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loan_amount = filter_var($_POST['loan_amount'], FILTER_VALIDATE_FLOAT);
    $term_months = filter_var($_POST['term_months'], FILTER_VALIDATE_INT);
    $purpose = sanitizeInput($_POST['purpose']);
    
    $errors = [];
    
    // Validate input
    if (!$loan_amount || $loan_amount <= 0) {
        $errors[] = "Please enter a valid loan amount";
    }
    if (!$term_months || $term_months <= 0) {
        $errors[] = "Please enter a valid loan term";
    }
    if (empty($purpose)) {
        $errors[] = "Please specify the purpose of the loan";
    }
    
    // If no errors, update the loan application
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE loans 
                SET loan_amount = ?, term_months = ?, purpose = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND user_id = ? AND status = 'pending'
            ");
            
            $stmt->execute([
                $loan_amount,
                $term_months,
                $purpose,
                $loan_id,
                $_SESSION['user_id']
            ]);
            
            $_SESSION['alert'] = [
                'message' => 'Loan application updated successfully!',
                'type' => 'success'
            ];
            header('Location: /user/view_loan.php?id=' . $loan_id);
            exit();
            
        } catch (PDOException $e) {
            $errors[] = "Error updating loan application. Please try again.";
        }
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Edit Loan Application</h1>
            <a href="/user/view_loan.php?id=<?php echo $loan_id; ?>" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Loan Details
            </a>
        </div>

        <!-- Loan Calculator -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Loan Calculator</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Loan Amount ($)</label>
                    <input type="number" id="calc-amount" min="1000" max="50000" value="<?php echo $loan['loan_amount']; ?>" 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Term (Months)</label>
                    <input type="number" id="calc-term" min="6" max="60" value="<?php echo $loan['term_months']; ?>" 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Interest Rate</p>
                        <p class="text-lg font-semibold"><?php echo $loan['interest_rate']; ?>%</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Monthly Payment</p>
                        <p class="text-lg font-semibold" id="monthly-payment">$0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Edit Loan Details</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <p class="font-bold">Please correct the following errors:</p>
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="loan_amount" class="block text-gray-700 text-sm font-bold mb-2">Loan Amount ($)</label>
                    <input type="number" id="loan_amount" name="loan_amount" required min="1000" max="50000" 
                           step="100" value="<?php echo $loan['loan_amount']; ?>"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-sm text-gray-500 mt-1">Min: $1,000 - Max: $50,000</p>
                </div>

                <div>
                    <label for="term_months" class="block text-gray-700 text-sm font-bold mb-2">Loan Term (Months)</label>
                    <select id="term_months" name="term_months" required 
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php
                        $terms = [12, 24, 36, 48, 60];
                        foreach ($terms as $term) {
                            $selected = $term == $loan['term_months'] ? 'selected' : '';
                            echo "<option value=\"$term\" $selected>$term months (" . ($term/12) . " years)</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="purpose" class="block text-gray-700 text-sm font-bold mb-2">Loan Purpose</label>
                    <textarea id="purpose" name="purpose" required rows="4"
                              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Please describe the purpose of this loan..."><?php echo htmlspecialchars($loan['purpose']); ?></textarea>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <h3 class="font-semibold mb-2">Important Notes</h3>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li>Your loan application will be reviewed again after editing</li>
                        <li>Current interest rate: <?php echo $loan['interest_rate']; ?>% APR</li>
                        <li>You can only edit pending applications</li>
                    </ul>
                </div>

                <div class="flex space-x-4">
                    <button type="submit" 
                            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                    <a href="/user/view_loan.php?id=<?php echo $loan_id; ?>" 
                       class="flex-1 bg-gray-500 text-white py-2 px-4 rounded-lg hover:bg-gray-600 transition duration-200 text-center">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calculateMonthlyPayment = () => {
        const amount = parseFloat(document.getElementById('calc-amount').value);
        const months = parseInt(document.getElementById('calc-term').value);
        const rate = <?php echo $loan['interest_rate']; ?>;
        
        const monthlyRate = (rate / 100) / 12;
        const payment = amount * (monthlyRate * Math.pow(1 + monthlyRate, months)) / (Math.pow(1 + monthlyRate, months) - 1);
        
        document.getElementById('monthly-payment').textContent = '$' + payment.toFixed(2);
    };

    // Add event listeners to calculator inputs
    document.getElementById('calc-amount').addEventListener('input', calculateMonthlyPayment);
    document.getElementById('calc-term').addEventListener('input', calculateMonthlyPayment);

    // Initial calculation
    calculateMonthlyPayment();
});
</script>

<?php require_once '../includes/footer.php'; ?>
