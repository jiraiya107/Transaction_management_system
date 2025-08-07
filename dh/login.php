<?php
// login.php
session_start(); // Start the session for user authentication

require_once 'config/db.php'; // Include your database connection

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_or_username = $_POST['email_or_username'] ?? '';
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? ''; // 'customer' or 'admin'

    if ($user_type === 'customer') {
        // Logic for customer login
        $stmt = $pdo->prepare("SELECT user_id, password_hash, status FROM users WHERE email = ?");
        $stmt->execute([$email_or_username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] === 'active') {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_type'] = 'customer';
                header("Location: customer/dashboard.php"); // Redirect to customer dashboard
                exit();
            } else {
                $message = "Your account is not active. Status: " . ucfirst($user['status']) . ".";
            }
        } else {
            $message = "Invalid customer email or password.";
        }
    } elseif ($user_type === 'admin') {
        // Logic for admin login
        $stmt = $pdo->prepare("SELECT admin_id, password_hash FROM admin_users WHERE username = ?");
        $stmt->execute([$email_or_username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['user_type'] = 'admin';
            header("Location: admin/dashboard.php"); // Redirect to admin dashboard
            exit();
        } else {
            $message = "Invalid admin username or password.";
        }
    } else {
        $message = "Please select a user type.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Transaction Management System</title> <!-- Updated Title -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6">Login to Transaction Management System</h2> <!-- Updated Heading -->

        <?php if ($message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-4">
                <label for="email_or_username" class="block text-gray-700 text-sm font-bold mb-2">Email (Customer) / Username (Admin):</label>
                <input type="text" id="email_or_username" name="email_or_username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Login As:</label>
                <div class="mt-2">
                    <label class="inline-flex items-center">
                        <input type="radio" class="form-radio" name="user_type" value="customer" checked>
                        <span class="ml-2">Customer</span>
                    </label>
                    <label class="inline-flex items-center ml-6">
                        <input type="radio" class="form-radio" name="user_type" value="admin">
                        <span class="ml-2">Admin</span>
                    </label>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Login
                </button>
                <a href="register.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Don't have an account? Register
                </a>
            </div>
        </form>
    </div>
</body>
</html>
