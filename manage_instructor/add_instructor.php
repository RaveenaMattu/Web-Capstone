<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1'); 
error_reporting(E_ALL);
  session_start();
  require_once('../database.php');

  $firstName = filter_input(INPUT_POST, 'firstName');
  $lastName = filter_input(INPUT_POST, 'lastName');
  $doj = filter_input(INPUT_POST, 'doj');
  $phoneNumber = filter_input(INPUT_POST, 'phoneNumber');
  $emailAddress = filter_input(INPUT_POST, 'emailAddress');
  $password = filter_input(INPUT_POST, 'password');
  $mailingAddress = filter_input(INPUT_POST, 'mailingAddress');
  $image = $_FILES['image'] ?? null;
  $image_dir = '../images/';
  $image_dir_path = getcwd() . DIRECTORY_SEPARATOR . $image_dir;
  $imageName = 'placeholder.jpg'; // Default image
  $imagePath = $image_dir . $imageName;
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
  // Handle image upload
  if ($image && $image['error'] === UPLOAD_ERR_OK) {
      $filename = basename($image['name']);
      $target = $image_dir_path . $filename;

      if (move_uploaded_file($image['tmp_name'], $target)) {
          $imageName = $filename;
      } else {
          echo "Failed to upload image to $target";
          exit();
      }
  } else {
      echo "No image uploaded or there was an error.";
      exit();
  }
  // Check for existing instructor with the same email
  $query = 'SELECT * FROM instructors WHERE email = :emailAddress';
  $statement = $db->prepare($query);
  $statement->bindValue(':emailAddress', $emailAddress);
  $statement->execute();
  $existingInstructor = $statement->fetch();
  $statement->closeCursor();    
  if ($existingInstructor) {
    session_start();
    $_SESSION['error'] = 'An instructor with this email already exists.';
    header('Location: add_instructor_form.php');
    exit();
}           

  if (!$firstName || !$lastName || !$doj || !$emailAddress || !$password) {
      echo "Missing required fields.";
      exit();
  }
  // Insert new instructor into the database
  $query = 'INSERT INTO instructors (firstName, lastName, doj, contactNumber, email, password, mailingAddress, imageName) 
            VALUES (:firstName, :lastName, :doj, :phoneNumber, :emailAddress, :password, :mailingAddress, :imageName)';
  $statement = $db->prepare($query);
  $statement->bindValue(':firstName', $firstName);
  $statement->bindValue(':lastName', $lastName);
  $statement->bindValue(':doj', $doj);
  $statement->bindValue(':phoneNumber', $phoneNumber);
  $statement->bindValue(':emailAddress', $emailAddress);
  $statement->bindValue(':password', $hashedPassword);
  $statement->bindValue(':mailingAddress', $mailingAddress);
  $statement->bindValue(':imageName', $imageName);
  $statement->execute();
  $statement->closeCursor();
  // Redirect to manage instructors page
  header('Location: ../manage_instructor.php');
  exit();
  
?>