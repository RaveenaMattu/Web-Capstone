<?php
session_start();
require_once('database.php');

$emailAddress = filter_input(INPUT_POST, 'email');
$password = filter_input(INPUT_POST, 'password');

if (!$emailAddress || !$password) {
    echo "Missing email or password";
    exit();
}

// Step 2: Fetch user from database
$query = "SELECT adminID, password, username FROM admins WHERE emailAddress = :emailAddress";
$statement = $db->prepare($query);
$statement->bindValue(':emailAddress', $emailAddress);
$statement->execute();
$admin = $statement->fetch(PDO::FETCH_ASSOC);
$statement->closeCursor();

if (!$admin) {
    echo "Admin not found.";
    exit();
}

// Step 3: Check stored password
$hashedPassword = $admin['password'];

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

        // ✅ Set session
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['fullName'] = $admin['username'];
        $_SESSION['adminID'] = $admin['adminID']; // ✅ Add this line

        header('Location: admin_dashboard.php');
        exit();

    } else {
        echo "Password does NOT match plaintext. Login failed.";
        exit();
    }

} else {
    if (password_verify($password, $hashedPassword)) {
        // ✅ Set session
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['fullName'] = $admin['username'];
        $_SESSION['adminID'] = $admin['adminID']; // ✅ Add this line

        header('Location: admin_dashboard.php');
        exit();

    } else {
        echo "Password verification failed. Wrong password.";
        exit();
    }
}
?>
