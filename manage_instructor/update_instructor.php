<?php
session_start();

require_once('../database.php');

$instructorID = filter_input(INPUT_POST, 'instructorID', FILTER_VALIDATE_INT);
$firstName = filter_input(INPUT_POST, 'firstName');
$lastName = filter_input(INPUT_POST, 'lastName');
$doj = filter_input(INPUT_POST, 'doj');
$phoneNumber = filter_input(INPUT_POST, 'phoneNumber');
$emailAddress = filter_input(INPUT_POST, 'emailAddress');
$password = filter_input(INPUT_POST, 'password');
$mailingAddress = filter_input(INPUT_POST, 'mailingAddress');
$image = $_FILES['image'] ?? null;

if (!$instructorID || !$firstName || !$lastName || !$doj || !$emailAddress) {
    echo "Missing required fields.";
    exit();
}

// Get existing instructor details
$query = 'SELECT * FROM instructors WHERE instructorID = :instructorID';
$statement = $db->prepare($query);
$statement->bindValue(':instructorID', $instructorID);
$statement->execute();
$existingInstructor = $statement->fetch();
$statement->closeCursor();

if (!$existingInstructor) {
    echo "Instructor not found.";
    exit();
}

$originalEmail = $existingInstructor['email'];
$originalPassword = $existingInstructor['password'];
$originalImage = $existingInstructor['imageName'];

// Check if email is changed, then check if already exists
if ($emailAddress !== $originalEmail) {
    $query = 'SELECT COUNT(*) FROM instructors WHERE email = :email AND instructorID != :instructorID';
    $statement = $db->prepare($query);
    $statement->bindValue(':email', $emailAddress);
    $statement->bindValue(':instructorID', $instructorID);
    $statement->execute();
    $emailCount = $statement->fetchColumn();
    $statement->closeCursor();

    if ($emailCount > 0) {
        $_SESSION['error'] = 'Email address already exists.';
        header("Location: update_instructor_form.php");
        exit();
    }
}

// use new password if entered, else keep existing
$hashedPassword = $password ? password_hash($password, PASSWORD_DEFAULT) : $originalPassword;

// use new image if uploaded, else keep existing
$imageName = $originalImage;
if ($image && $image['error'] === UPLOAD_ERR_OK) {
    $image_dir = '../images/';
    $filename = basename($image['name']);
    $target = $image_dir . $filename;
    if (move_uploaded_file($image['tmp_name'], $target)) {
        $imageName = $filename;
    } else {
        echo "Failed to upload image.";
        exit();
    }
}

// ✅ Update query
$query = 'UPDATE instructors 
          SET firstName = :firstName, lastName = :lastName, doj = :doj, 
              contactNumber = :phoneNumber, email = :email, password = :password, 
              mailingAddress = :mailingAddress, imageName = :imageName
          WHERE instructorID = :instructorID';

$statement = $db->prepare($query);
$statement->bindValue(':firstName', $firstName);
$statement->bindValue(':lastName', $lastName);
$statement->bindValue(':doj', $doj);
$statement->bindValue(':phoneNumber', $phoneNumber);
$statement->bindValue(':email', $emailAddress);
$statement->bindValue(':password', $hashedPassword);
$statement->bindValue(':mailingAddress', $mailingAddress);
$statement->bindValue(':imageName', $imageName);
$statement->bindValue(':instructorID', $instructorID);
$statement->execute();
$statement->closeCursor();

// Redirect back to the list
header("Location: ../manage_instructor.php");
exit();
?>
