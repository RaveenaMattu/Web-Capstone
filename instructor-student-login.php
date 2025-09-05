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
  $_SESSION['error'] = "Missing email, password, or role";
  $_SESSION['lastRole'] = $role;
  header('Location: /web-capstone/login_form.php');
  exit();
}

// Query based on role
if ($role === "Instructor") {
  $query = "SELECT instructorID AS id, firstName, lastName, password 
            FROM instructors WHERE email = :emailAddress";
} else {
  $query = "SELECT studentID AS id, firstName, lastName, password 
            FROM students WHERE email = :emailAddress";
}

$statement = $db->prepare($query);
$statement->bindValue(':emailAddress', $emailAddress);
$statement->execute();
$user = $statement->fetch(PDO::FETCH_ASSOC);
$statement->closeCursor();

if (!$user) {
  $_SESSION['error'] = ucfirst($role) . " not found.";
  $_SESSION['lastRole'] = $role;
  header('Location: /web-capstone/login_form.php');
  exit();
}

$hashedPassword = $user['password'];
$fullName = $user['firstName'] . ' ' . $user['lastName'];
$userID = $user['id'];

// If the stored password is plain text
if (!password_get_info($hashedPassword)['algo']) {
  if ($hashedPassword === $password) {
    // Hash it and update in DB
    $newHashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $updateQuery = $role === "Instructor"
      ? "UPDATE instructors SET password = :password WHERE instructorID = :id"
      : "UPDATE students SET password = :password WHERE studentID = :id";
    $updateStatement = $db->prepare($updateQuery);
    $updateStatement->bindValue(':password', $newHashedPassword);
    $updateStatement->bindValue(':id', $userID);
    $updateStatement->execute();
    $updateStatement->closeCursor();
    $hashedPassword = $newHashedPassword; // update variable so password_verify works
  } else {
    $_SESSION['error'] = "Incorrect password.";
    $_SESSION['lastRole'] = $role;
    header('Location: /web-capstone/login_form.php');
    exit();
  }
}

// Verify password (hashed)
if (password_verify($password, $hashedPassword)) {
  $_SESSION['isLoggedIn'] = true;
  $_SESSION['fullName'] = $fullName;
  $_SESSION['userID'] = $userID;
  $_SESSION['role'] = $role;

  if ($role === "Instructor") {
    header('Location: instructor/instructor_dashboard.php');
  } else {
    header('Location: student/student_dashboard.php');
  }
  exit();
} else {
  $_SESSION['error'] = "Incorrect password.";
  $_SESSION['lastRole'] = $role;
  header('Location: /web-capstone/login_form.php');
  exit();
}
