<?php
session_start();
require_once('database.php');

$emailAddress = filter_input(INPUT_POST, 'email');
$password = filter_input(INPUT_POST, 'password');

if (!$emailAddress || !$password) {
    $_SESSION['error'] = "Missing email or password.";
    include("index.php");
    exit();
}

// Fetch user from database
$query = "SELECT adminID, password, username FROM admins WHERE emailAddress = :emailAddress";
$statement = $db->prepare($query);
$statement->bindValue(':emailAddress', $emailAddress);
$statement->execute();
$admin = $statement->fetch(PDO::FETCH_ASSOC);
$statement->closeCursor();

if (!$admin) {
    $_SESSION['error'] = "Admin not found.";
    include("index.php");
    exit();
}

$hashedPassword = $admin['password'];

// Case 1: Stored password might be plain text (rehash needed)
if (password_needs_rehash($hashedPassword, PASSWORD_DEFAULT)) {
    if ($hashedPassword === $password) {
        // Rehash and update
        $newHashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE admins SET password = :password WHERE adminID = :adminID";
        $updateStatement = $db->prepare($updateQuery);
        $updateStatement->bindValue(':password', $newHashedPassword);
        $updateStatement->bindValue(':adminID', $admin['adminID']);
        $updateStatement->execute();
        $updateStatement->closeCursor();

        $_SESSION['isLoggedIn'] = true;
        $_SESSION['fullName'] = $admin['username'];
        $_SESSION['adminID'] = $admin['adminID']; 

        header('Location: admin_dashboard.php');
        exit();
    } else {
        $_SESSION['error'] = "Password verification failed. Wrong password.";
        include("index.php");
        exit();
    }
}

// Case 2: Properly hashed password
else {
    if (password_verify($password, $hashedPassword)) {
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['fullName'] = $admin['username'];
        $_SESSION['adminID'] = $admin['adminID'];

        header('Location: admin_dashboard.php');
        exit();
    } else {
        $_SESSION['error'] = "Password verification failed. Wrong password.";
        include("index.php");
        exit();
    }
}
?>
