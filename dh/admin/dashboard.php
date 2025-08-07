<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Transaction Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-2xl text-center">
        <h1 class="text-3xl font-bold text-green-800 mb-4">Welcome, Administrator!</h1>
        <p class="text-lg text-gray-700 mb-6">You are logged in as Admin ID: <span class="font-semibold"><?php echo $_SESSION['admin_id']; ?></span></p>
        <p class="text-gray-600">This is your admin panel for the Transaction Management System. Manage users, transactions, and system settings here.</p>
        <div class="mt-8 space-y-4">
            <a href="manage_users.php" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Manage User Accounts
            </a>
            <!-- Add other admin links here later, e.g., View All Transactions -->
            <a href="../logout.php" class="inline-block bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Logout
            </a>
        </div>
    </div>
</body>
</html>