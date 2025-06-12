<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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


// Step 3: Check stored password
$hashedPassword = $admin['password'];

// Step 4: Check if password needs rehash
if (password_needs_rehash($hashedPassword, PASSWORD_DEFAULT)) {

    // Check if stored password is plaintext and matches input
    if ($hashedPassword === $password) {

        // Hash and update
        $newHashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $updateQuery = "UPDATE admins SET password = :password WHERE adminID = :adminID";
        $updateStatement = $db->prepare($updateQuery);
        $updateStatement->bindValue(':password', $newHashedPassword);
        $updateStatement->bindValue(':adminID', $admin['adminID']);
        $updateStatement->execute();
        $updateStatement->closeCursor();

        echo "Password updated to hash.<br>";

        // Set session and redirect
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['fullName'] = $admin['username'];
        echo "Login successful. Redirecting...";
        header('Location: admin_dashboard.php');
        exit();

    } else {
        echo "Password does NOT match plaintext. Login failed.";
        exit();
    }

} else {

    // Check hashed password
    if (password_verify($password, $hashedPassword)) {
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['fullName'] = $admin['username'];
        echo "Login successful. Redirecting...";
        header('Location: admin_dashboard.php');
        exit();
    } else {
        echo "Password verification failed. Wrong password.<br>";
        exit();
    }
}
?>
