<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Transaction Management System</title> <!-- Updated Title -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-2xl text-center">
        <h1 class="text-3xl font-bold text-blue-800 mb-4">Welcome to Your Dashboard!</h1>
        <p class="text-lg text-gray-700 mb-6">You are logged in as Customer ID: <span class="font-semibold"><?php echo $_SESSION['user_id']; ?></span></p>
        <p class="text-gray-600">This is your customer panel for the Transaction Management System. More features coming soon!</p>
        <div class="mt-8">
            <a href="../logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Logout
            </a>
        </div>
    </div>
</body>
</html>
