<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
require_once('../database.php');

// Collect form data
$firstName       = filter_input(INPUT_POST, 'firstName');
$lastName        = filter_input(INPUT_POST, 'lastName');
$dob             = filter_input(INPUT_POST, 'dob');
$contactNumber   = filter_input(INPUT_POST, 'contactNumber');
$email           = filter_input(INPUT_POST, 'email');
$password        = filter_input(INPUT_POST, 'password');
$mailingAddress  = filter_input(INPUT_POST, 'mailingAddress');
$status          = filter_input(INPUT_POST, 'status');

// Image setup
$image           = $_FILES['image'] ?? null;
$image_dir       = '../images/';
$image_dir_path  = getcwd() . DIRECTORY_SEPARATOR . $image_dir;
$imageName       = 'placeholder.jpg'; // default

// Validate required fields
if (!$firstName || !$lastName || !$dob || !$email || !$password || !$contactNumber || !$mailingAddress || !$status) {
    echo "Missing required fields.";
    exit();
}

// Image upload (optional)
if ($image && $image['error'] === UPLOAD_ERR_OK) {
    $filename = basename($image['name']);
    $target = $image_dir_path . $filename;

    if (move_uploaded_file($image['tmp_name'], $target)) {
        $imageName = $filename;
    } else {
        echo "Failed to upload image.";
        exit();
    }
}

// Check for duplicate email
$query = 'SELECT * FROM students WHERE email = :email';
$statement = $db->prepare($query);
$statement->bindValue(':email', $email);
$statement->execute();
$existingStudent = $statement->fetch();
$statement->closeCursor();

if ($existingStudent) {
    $_SESSION['error'] = 'A student with this email already exists.';
    header('Location: add_student_form.php');
    exit();
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert into database
$query = 'INSERT INTO students 
    (firstName, lastName, status, dob, email, password, contactNumber, mailingAddress, imageName)
    VALUES 
    (:firstName, :lastName, :status, :dob, :email, :password, :contactNumber, :mailingAddress, :imageName)';

$statement = $db->prepare($query);
$statement->bindValue(':firstName', $firstName);
$statement->bindValue(':lastName', $lastName);
$statement->bindValue(':status', $status);
$statement->bindValue(':dob', $dob);
$statement->bindValue(':email', $email);
$statement->bindValue(':password', $hashedPassword);
$statement->bindValue(':contactNumber', $contactNumber);
$statement->bindValue(':mailingAddress', $mailingAddress);
$statement->bindValue(':imageName', $imageName);
$statement->execute();
$statement->closeCursor();

// Redirect
header('Location: ../manage_student.php');
exit();
?>
