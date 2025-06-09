<?php
  session_start();
  // Database connection data source name (DSN)
  $dsn = 'mysql:host=localhost;dbname=learning_pod';
  $username = 'root';
  $password = '';

  try {
      $db = new PDO($dsn, $username, $password);
  } catch (PDOException $e) {
      $_SESSION['database_error'] = $e->getMessage();
      $url = 'database_error.php';
      header('Location:' . $url);
      exit();
  }
?>