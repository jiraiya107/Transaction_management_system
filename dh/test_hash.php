<?php
// test_hash.php
$password = '12345678'; // Choose a simple password for testing, like 'password123'
echo password_hash($password, PASSWORD_DEFAULT);
?>