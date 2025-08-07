<?php
// register.php
require_once 'config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $transaction_pin = $_POST['transaction_pin'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $address = $_POST['address'] ?? '';

    // Basic validation
    if (empty($full_name) || empty($email) || empty($password) || empty($transaction_pin)) {
        $message = "All required fields must be filled.";
    } elseif (strlen($transaction_pin) !== 4 || !ctype_digit($transaction_pin)) {
        $message = "Transaction PIN must be a 4-digit number.";
    } else {
        // Hash passwords and PINs
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $transaction_pin_hash = password_hash($transaction_pin, PASSWORD_DEFAULT);

        try {
            // Insert into users table with 'pending' status
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, transaction_pin_hash, phone_number, address, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$full_name, $email, $password_hash, $transaction_pin_hash, $phone_number, $address]);
            $message = "Account request submitted successfully! Please wait for admin approval.";
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) { // SQLSTATE for integrity constraint violation (e.g., duplicate email)
                $message = "Email or phone number already registered. Please use a different one.";
            } else {
                error_log("Registration error: " . $e->getMessage());
                $message = "An error occurred during registration. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Transaction Management System</title> <!-- Updated Title -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6">Register for a New Account</h2>

        <?php if ($message): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="mb-4">
                <label for="full_name" class="block text-gray-700 text-sm font-bold mb-2">Full Name:</label>
                <input type="text" id="full_name" name="full_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                <input type="email" id="email" name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label for="transaction_pin" class="block text-gray-700 text-sm font-bold mb-2">4-Digit Transaction PIN:</label>
                <input type="text" id="transaction_pin" name="transaction_pin" maxlength="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label for="phone_number" class="block text-gray-700 text-sm font-bold mb-2">Phone Number (Optional):</label>
                <input type="text" id="phone_number" name="phone_number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-6">
                <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address (Optional):</label>
                <textarea id="address" name="address" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Submit Request
                </button>
                <a href="login.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Already have an account? Login
                </a>
            </div>
        </form>
    </div>
</body>
</html>
