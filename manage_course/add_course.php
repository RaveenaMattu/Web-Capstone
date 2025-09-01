<?php 

  session_start();
  if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['adminID'])) {
      echo "You are not authorized to access this page.";
      exit();
  }
  require('../database.php');

  $courseCode = filter_input(INPUT_POST, 'courseCode');
  $courseName = filter_input(INPUT_POST, 'courseName');
  $courseDesc = filter_input(INPUT_POST, 'courseDesc');
  $courseInstructor = filter_input(INPUT_POST, 'courseInstructor');
  $courseImage = $_FILES['courseImage'] ?? null;
  $image_dir = '../images/';
  $image_dir_path = getcwd() . DIRECTORY_SEPARATOR . $image_dir;
  $imageName = 'book_placeholder.png'; // Default image
  $imagePath = $image_dir . $imageName; 
  // Handle image upload
  if ($courseImage && $courseImage['error'] === UPLOAD_ERR_OK) {
      $filename = basename($courseImage['name']);
      $target = $image_dir_path . $filename;  
      if (move_uploaded_file($courseImage['tmp_name'], $target)) {
          $imageName = $filename;
      } else {
          echo "Failed to upload image to $target";
          exit();
      }
  }
  // Check for existing course with the same code
  $query = 'SELECT * FROM courses WHERE courseCode = :courseCode';
  $statement = $db->prepare($query);
  $statement->bindValue(':courseCode', $courseCode);
  $statement->execute();
  $existingCourse = $statement->fetch();
  $statement->closeCursor();
  if ($existingCourse) {
    session_start();
    $_SESSION['error'] = 'A course with this code already exists.';
    header('Location: add_course_form.php');
    exit();
  } 
  if (!$courseCode || !$courseName || !$courseDesc || !$courseInstructor) {
      echo "Missing required fields.";
      exit();
  } 
  // Insert new course into the database
  $query = 'INSERT INTO courses (courseCode, courseName, description, instructorID, imageName) 
            VALUES (:courseCode, :courseName, :description, :courseInstructor, :imageName)';
  $statement = $db->prepare($query);
  $statement->bindValue(':courseCode', $courseCode);
  $statement->bindValue(':courseName', $courseName);
  $statement->bindValue(':description', $courseDesc);
  $statement->bindValue(':courseInstructor', $courseInstructor);
  $statement->bindValue(':imageName', $imageName);
  $statement->execute();
  $statement->closeCursor();

  // Redirect to the manage courses page
    header('Location: ../manage_course.php');
    exit();

?>