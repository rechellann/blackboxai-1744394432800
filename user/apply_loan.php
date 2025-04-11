<?php
require_once '../includes/header.php';
requireLogin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loan_amount = filter_var($_POST['loan_amount'], FILTER_VALIDATE_FLOAT);
    $term_months = filter_var($_POST['term_months'], FILTER_VALIDATE_INT);
    $purpose = sanitizeInput($_POST['purpose']);
    $interest_rate = 12.00; // Default interest rate for Philippine loans
    
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
    
    // If no errors, process the loan application
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO loans (user_id, loan_amount, interest_rate, term_months, purpose, status)
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $loan_amount,
                $interest_rate,
                $term_months,
                $purpose
            ]);
            
            $_SESSION['alert'] = [
                'message' => 'Loan application submitted successfully!',
                'type' => 'success'
            ];
            header('Location: /user/dashboard.php');
            exit();
            
        } catch (PDOException $e) {
            $errors[] = "Error submitting loan application. Please try again.";
        }
    }
}

// Get user's active loans count
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM loans 
        WHERE user_id = ? AND status IN ('pending', 'approved')
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $activeLoansCount = $stmt->fetchColumn();
} catch (PDOException $e) {
    $activeLoansCount = 0;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Apply for a Loan</h1>
            <a href="/user/dashboard.php" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>

        <?php if ($activeLoansCount >= 3): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6">
                <p class="font-bold">Maximum Active Loans Reached</p>
                <p>You currently have the maximum number of active loans allowed (3). Please wait for your existing loans to be completed before applying for a new one.</p>
            </div>
        <?php else: ?>
            <!-- Loan Calculator -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Loan Calculator</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Loan Amount (₱)</label>
                        <input type="number" id="calc-amount" min="5000" max="500000" value="25000" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Term (Months)</label>
                        <input type="number" id="calc-term" min="6" max="60" value="24" 
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Interest Rate</p>
                            <p class="text-lg font-semibold">5.99%</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Monthly Payment</p>
                            <p class="text-lg font-semibold" id="monthly-payment">₱0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Loan Application Form</h2>
                
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
                        <label for="loan_amount" class="block text-gray-700 text-sm font-bold mb-2">Loan Amount (₱)</label>
                        <input type="number" id="loan_amount" name="loan_amount" required min="5000" max="500000" 
                               step="1000" value="<?php echo isset($_POST['loan_amount']) ? htmlspecialchars($_POST['loan_amount']) : '25000'; ?>"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 mt-1">Min: ₱5,000 - Max: ₱500,000</p>
                    </div>

                    <div>
                        <label for="term_months" class="block text-gray-700 text-sm font-bold mb-2">Loan Term (Months)</label>
                        <select id="term_months" name="term_months" required 
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="12" <?php echo (isset($_POST['term_months']) && $_POST['term_months'] == 12) ? 'selected' : ''; ?>>12 months (1 year)</option>
                            <option value="24" <?php echo (isset($_POST['term_months']) && $_POST['term_months'] == 24) ? 'selected' : ''; ?>>24 months (2 years)</option>
                            <option value="36" <?php echo (isset($_POST['term_months']) && $_POST['term_months'] == 36) ? 'selected' : ''; ?>>36 months (3 years)</option>
                            <option value="48" <?php echo (isset($_POST['term_months']) && $_POST['term_months'] == 48) ? 'selected' : ''; ?>>48 months (4 years)</option>
                            <option value="60" <?php echo (isset($_POST['term_months']) && $_POST['term_months'] == 60) ? 'selected' : ''; ?>>60 months (5 years)</option>
                        </select>
                    </div>

                    <div>
                        <label for="purpose" class="block text-gray-700 text-sm font-bold mb-2">Loan Purpose</label>
                        <textarea id="purpose" name="purpose" required rows="4"
                                  class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Please describe the purpose of this loan..."><?php echo isset($_POST['purpose']) ? htmlspecialchars($_POST['purpose']) : ''; ?></textarea>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="font-semibold mb-2">Loan Terms</h3>
                        <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                            <li>Fixed interest rate of 12% APR</li>
                            <li>No early repayment fees</li>
                            <li>Monthly repayment schedule</li>
                            <li>Subject to approval</li>
                        </ul>
                    </div>

                    <button type="submit" 
                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                        Submit Application
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calculateMonthlyPayment = () => {
        const amount = parseFloat(document.getElementById('calc-amount').value);
        const months = parseInt(document.getElementById('calc-term').value);
        const rate = 12.00;
        
        const monthlyRate = (rate / 100) / 12;
        const payment = amount * (monthlyRate * Math.pow(1 + monthlyRate, months)) / (Math.pow(1 + monthlyRate, months) - 1);
        
        document.getElementById('monthly-payment').textContent = '₱' + payment.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    };

    // Add event listeners to calculator inputs
    document.getElementById('calc-amount').addEventListener('input', calculateMonthlyPayment);
    document.getElementById('calc-term').addEventListener('input', calculateMonthlyPayment);

    // Initial calculation
    calculateMonthlyPayment();
});
</script>

<?php require_once '../includes/footer.php'; ?>
