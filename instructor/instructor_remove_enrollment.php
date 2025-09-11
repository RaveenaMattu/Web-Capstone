<?php

  ini_set('display_errors','1');
  ini_set('display_startup_errors','1');
  error_reporting(E_ALL);

  session_start();
  require_once('../database.php');

  // Ensure user is logged in as Instructor
  if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'Instructor') {
    header('Location: ../login.php');
    exit();
  }
  $enrollmentID = filter_input(INPUT_POST, 'enrollmentID', FILTER_VALIDATE_INT);
  if (!$enrollmentID) {
    $_SESSION['error'] = "Error Deleting the Enrollement ...";
    echo "Invalid enrollment ID.";
    exit();
  }

  // Delete the instructor record
  $query = 'DELETE FROM course_enrollments WHERE enrollmentID = :enrollmentID';  
  $statement = $db->prepare($query);
  $statement->bindValue(':enrollmentID', $enrollmentID);
  $statement->execute();
  $statement->closeCursor();
  // Redirect to manage enrollments page
  $_SESSION['success'] = "Enrollment Deleted successfully ...";
  header('Location: instructor_manage_enrollments.php');
  exit();
?>