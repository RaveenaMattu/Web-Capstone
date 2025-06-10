<?php
session_start();

$email = filter_input(INPUT_POST, 'email');
$password = filter_input(INPUT_POST, 'password');

require_once('database.php');

$queryLogin = 'SELECT password, firstName, lastName FROM users WHERE email = :email';
$statement = $db->prepare($queryLogin);
$statement->bindValue(':email', $email);
$statement->execute();
$login = $statement->fetch();
$statement->closeCursor();

if ($login && password_verify($password, $login['password'])) {
    // Password is correct
    $_SESSION['isLoggedIn'] = true;
    $_SESSION['fullName'] = $login['firstName'] . ' ' . $login['lastName'];

    header('Location: admin_dashboard.php');
    exit();
} else {
    // Invalid email or password
    $_SESSION = [];
    session_destroy();

    header('Location: login_form.php?error=invalid_credentials');
    exit();
}
?>
