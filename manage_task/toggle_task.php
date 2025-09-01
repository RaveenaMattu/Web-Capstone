<?php
session_start();
require_once('../database.php');

if (!isset($_SESSION['adminID'])) {
    die("Error: Admin not logged in.");
}

// Get values from POST
$taskID = filter_input(INPUT_POST, 'taskID', FILTER_VALIDATE_INT);
$currentStatus = filter_input(INPUT_POST, 'isComplete', FILTER_VALIDATE_INT);

// Safety check
if (!$taskID) {
    die("Invalid task ID");
}

// Toggle the value
$newStatus = ($currentStatus == 1) ? 0 : 1;

// Update DB
$query = "UPDATE admin_tasks SET isComplete = :newStatus WHERE taskID = :taskID";
$statement = $db->prepare($query);
$statement->bindValue(':newStatus', $newStatus, PDO::PARAM_INT);
$statement->bindValue(':taskID', $taskID, PDO::PARAM_INT);
$statement->execute();
$statement->closeCursor();

// Redirect back
header("Location: ../manage_tasks.php");
exit();
?>
