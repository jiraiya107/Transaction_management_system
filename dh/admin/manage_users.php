<?php
// admin/manage_users.php
session_start();
require_once '../config/db.php'; // Adjust path as necessary

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
$success_message = '';
$account_details_for_sms = []; // To store details for simulated SMS

// --- Handle Account Approval ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'approve') {
    $user_id_to_approve = $_POST['user_id'] ?? null;

    if ($user_id_to_approve) {
        try {
            // Start a database transaction for atomicity
            $pdo->beginTransaction();

            // 1. Get user details to ensure they are 'pending'
            // FIX: Added 'phone_number' to the SELECT query
            $stmt = $pdo->prepare("SELECT email, full_name, phone_number FROM users WHERE user_id = ? AND status = 'pending'");
            $stmt->execute([$user_id_to_approve]);
            $user_to_approve = $stmt->fetch();

            if ($user_to_approve) {
                // 2. Generate a unique 10-digit account number
                // This is a simple example; for a real system, ensure true uniqueness and format
                $account_number = 'ACC' . str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
                // In a real system, you'd loop and check if account_number exists in DB before assigning

                // 3. Update user status to 'active'
                $stmt = $pdo->prepare("UPDATE users SET status = 'active', updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
                $stmt->execute([$user_id_to_approve]);

                // 4. Create a new account for the user (e.g., a Savings account with 0 balance)
                $stmt = $pdo->prepare("INSERT INTO accounts (user_id, account_number, account_type, balance, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id_to_approve, $account_number, 'Savings', 0.00, 'active']);

                // Commit the transaction if all operations succeed
                $pdo->commit();
                $success_message = "Account for " . htmlspecialchars($user_to_approve['full_name']) . " approved successfully!";
                $account_details_for_sms = [
                    'name' => $user_to_approve['full_name'],
                    'email' => $user_to_approve['email'],
                    'phone_number' => $user_to_approve['phone_number'], // Now correctly populated
                    'account_number' => $account_number,
                    // In a real system, you'd also send their initial password or a temporary one.
                    // For now, assume they use the password they registered with.
                ];

            } else {
                $message = "User not found or not in 'pending' status.";
            }

        } catch (\PDOException $e) {
            $pdo->rollBack(); // Rollback on error
            error_log("Account approval error: " . $e->getMessage());
            $message = "Error approving account: " . $e->getMessage();
        }
    } else {
        $message = "Invalid user ID provided for approval.";
    }
}

// --- Handle Account Rejection ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reject') {
    $user_id_to_reject = $_POST['user_id'] ?? null;

    if ($user_id_to_reject) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET status = 'rejected', updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND status = 'pending'");
            $stmt->execute([$user_id_to_reject]);
            if ($stmt->rowCount() > 0) {
                $success_message = "Account request rejected successfully.";
            } else {
                $message = "User not found or not in 'pending' status.";
            }
        } catch (\PDOException $e) {
            error_log("Account rejection error: " . $e->getMessage());
            $message = "Error rejecting account: " . $e->getMessage();
        }
    } else {
        $message = "Invalid user ID provided for rejection.";
    }
}

// --- Fetch All Users (for display) ---
try {
    $stmt = $pdo->query("SELECT user_id, full_name, email, phone_number, status, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (\PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    $message = "Could not load user data.";
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Transaction Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4">
    <div class="container mx-auto bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-3xl font-bold text-green-800 mb-6 text-center">Manage User Accounts</h1>

        <?php if ($message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($account_details_for_sms)): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded relative mb-6">
                <h3 class="font-bold mb-2">Simulated SMS for New Account:</h3>
                <p>To: <?php echo htmlspecialchars($account_details_for_sms['phone_number'] ?: $account_details_for_sms['email']); ?></p>
                <p>Message: Dear <?php echo htmlspecialchars($account_details_for_sms['name']); ?>, your bank account is now active! Your Account Number is: <span class="font-bold text-blue-700 text-xl"><?php echo htmlspecialchars($account_details_for_sms['account_number']); ?></span>. You can now log in with your registered email/password.</p>
                <p class="text-sm mt-2">Please note: In a real system, this would be sent via an SMS gateway.</p>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b text-left">ID</th>
                        <th class="py-2 px-4 border-b text-left">Full Name</th>
                        <th class="py-2 px-4 border-b text-left">Email</th>
                        <th class="py-2 px-4 border-b text-left">Phone</th>
                        <th class="py-2 px-4 border-b text-left">Status</th>
                        <th class="py-2 px-4 border-b text-left">Registered On</th>
                        <th class="py-2 px-4 border-b text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="py-4 px-4 text-center text-gray-500">No user accounts found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="<?php echo $user['status'] === 'pending' ? 'bg-yellow-50' : ''; ?>">
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($user['user_id']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($user['phone_number'] ?: 'N/A'); ?></td>
                                <td class="py-2 px-4 border-b">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        <?php
                                            if ($user['status'] === 'active') echo 'bg-green-200 text-green-800';
                                            elseif ($user['status'] === 'pending') echo 'bg-yellow-200 text-yellow-800';
                                            elseif ($user['status'] === 'rejected') echo 'bg-red-200 text-red-800';
                                            else echo 'bg-gray-200 text-gray-800';
                                        ?>">
                                        <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                                    </span>
                                </td>
                                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($user['created_at']))); ?></td>
                                <td class="py-2 px-4 border-b">
                                    <?php if ($user['status'] === 'pending'): ?>
                                        <form action="manage_users.php" method="POST" class="inline-block">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                                            <button type="submit" name="action" value="approve" class="bg-green-500 hover:bg-green-600 text-white text-sm py-1 px-2 rounded-md mr-2">Approve</button>
                                        </form>
                                        <form action="manage_users.php" method="POST" class="inline-block">
                                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                                            <button type="submit" name="action" value="reject" class="bg-red-500 hover:bg-red-600 text-white text-sm py-1 px-2 rounded-md">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-gray-500 text-sm">No actions</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6 text-center">
            <a href="dashboard.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
