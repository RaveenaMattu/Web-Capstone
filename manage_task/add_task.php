<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
require_once('../database.php');

// Get form input
$taskDescription = filter_input(INPUT_POST, 'taskDescription');


// Check if admin is logged in
if (!isset($_SESSION['adminID'])) {
  die("Error: Admin not logged in.");
}
$adminID = $_SESSION['adminID'];

// Insert into database
$query = 'INSERT INTO admin_tasks (taskDescription) 
          VALUES (:taskDescription)';
$statement = $db->prepare($query);
$statement->bindValue(':taskDescription', $taskDescription);
$statement->execute();
$statement->closeCursor();

// Redirect back
header('Location: ../manage_tasks.php');
exit();
?>
