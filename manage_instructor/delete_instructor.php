<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require_once('../database.php');
$instructorID = filter_input(INPUT_POST, 'instructorID', FILTER_VALIDATE_INT);
if (!$instructorID) {
    echo "Invalid instructor ID.";
    exit();
}

// Delete the instructor record
$query = 'DELETE FROM instructors WHERE instructorID = :instructorID';  
$statement = $db->prepare($query);
$statement->bindValue(':instructorID', $instructorID);
$statement->execute();
$statement->closeCursor();
// Redirect to manage instructors page
header('Location: ../manage_instructor.php');
exit();
?>