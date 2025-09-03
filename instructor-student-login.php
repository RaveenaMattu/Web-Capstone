<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('database.php');

$emailAddress = filter_input(INPUT_POST, 'email');
$password = filter_input(INPUT_POST, 'password');
$role = filter_input(INPUT_POST, 'role'); // "Instructor" or "Student"

if (!$emailAddress || !$password || !$role) {
    echo "Missing email, password, or role";
    exit();
}

// Query based on role
if ($role === "Instructor") {
    $query = "SELECT instructorID AS id, firstName, lastName, password 
              FROM instructors WHERE email = :emailAddress";
} else { // Student
    $query = "SELECT studentID AS id, firstName, lastName, password 
              FROM students WHERE email = :emailAddress";
}

$statement = $db->prepare($query);
$statement->bindValue(':emailAddress', $emailAddress);
$statement->execute();
$user = $statement->fetch(PDO::FETCH_ASSOC);
$statement->closeCursor();

if (!$user) {
    echo ucfirst($role) . " not found.";
    exit();
}

$hashedPassword = $user['password'];
$fullName = $user['firstName'] . ' ' . $user['lastName'];
$userID = $user['id'];

// Handle old plain-text passwords
if (password_needs_rehash($hashedPassword, PASSWORD_DEFAULT)) {
    if ($hashedPassword === $password) {
        $newHashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateQuery = $role === "Instructor"
            ? "UPDATE instructors SET password = :password WHERE instructorID = :id"
            : "UPDATE students SET password = :password WHERE studentID = :id";

        $updateStatement = $db->prepare($updateQuery);
        $updateStatement->bindValue(':password', $newHashedPassword);
        $updateStatement->bindValue(':id', $userID);
        $updateStatement->execute();
        $updateStatement->closeCursor();
    } else {
        echo "Wrong password.";
        exit();
    }
}

// Verify password
if (password_verify($password, $hashedPassword)) {
    $_SESSION['isLoggedIn'] = true;
    $_SESSION['fullName'] = $fullName;
    $_SESSION['userID'] = $userID;
    $_SESSION['role'] = $role;

    // Redirect to folder-based dashboard
    if ($role === "Instructor") {
        header('Location: instructor/instructor_dashboard.php');
    } else {
        header('Location: student_dashboard.php');
    }
    exit();
} else {
    echo "Password verification failed. Wrong password.";
    exit();
}
