<?php
require_once '../includes/header.php';
requireAdmin();

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header('Location: manage_users.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $full_name = $_POST['full_name'];
    $new_password = $_POST['new_password'];
    
    try {
        if (!empty($new_password)) {
            // Update with new password
            $password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, email = ?, full_name = ? WHERE id = ? AND role != 'admin'");
            $stmt->execute([$username, $password, $email, $full_name, $user_id]);
        } else {
            // Update without changing password
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, full_name = ? WHERE id = ? AND role != 'admin'");
            $stmt->execute([$username, $email, $full_name, $user_id]);
        }
        
        $_SESSION['alert'] = [
            'message' => 'User updated successfully.',
            'type' => 'success'
        ];
        header('Location: manage_users.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['alert'] = [
            'message' => 'Error updating user. ' . ($e->getCode() === '23000' ? 'Username or email already exists.' : ''),
            'type' => 'error'
        ];
    }
}

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['alert'] = [
            'message' => 'User not found.',
            'type' => 'error'
        ];
        header('Location: manage_users.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['alert'] = [
        'message' => 'Error fetching user data.',
        'type' => 'error'
    ];
    header('Location: manage_users.php');
    exit();
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Edit User</h1>
            <a href="manage_users.php" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Users
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
            <form action="edit_user.php?id=<?php echo $user_id; ?>" method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" name="username" id="username" required
                           value="<?php echo htmlspecialchars($user['username']); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700">New Password (leave blank to keep current)</label>
                    <input type="password" name="new_password" id="new_password"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" required
                           value="<?php echo htmlspecialchars($user['email']); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" name="full_name" id="full_name" required
                           value="<?php echo htmlspecialchars($user['full_name']); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
