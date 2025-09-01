<?php
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);
  require_once('../database.php');
  $taskID = filter_input(INPUT_POST, 'taskID', FILTER_VALIDATE_INT);
  if (!$taskID) {
      echo "Invalid task ID.";
      exit();
  }

  // Delete the instructor record
  $query = 'DELETE FROM admin_tasks WHERE taskID = :taskID';  
  $statement = $db->prepare($query);
  $statement->bindValue(':taskID', $taskID);
  $statement->execute();
  $statement->closeCursor();
  // Redirect to manage instructors page
  header('Location: ../manage_tasks.php');
  exit();
?>