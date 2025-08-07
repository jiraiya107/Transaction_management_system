<?php
// config/db.php

$host = 'localhost'; // Or your database host
$db   = 'bank_simulation_db'; // Your database name
$user = 'root'; // Your database username (default for XAMPP/WAMP is 'root')
$pass = ''; // Your database password (default for XAMPP/WAMP is empty)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Disable emulation for better security/performance
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // echo "Database connection successful!"; // For testing, remove in production
} catch (\PDOException $e) {
    // Log the error (do NOT display full error to users in production)
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
?>