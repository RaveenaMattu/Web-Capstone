<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require_once('../database.php');
$studentID = filter_input(INPUT_POST, 'studentID', FILTER_VALIDATE_INT);
if (!$studentID) {
    echo "Invalid instructor ID.";
    exit();
}

// Delete the instructor record
$query = 'DELETE FROM students WHERE studentID = :studentID';  
$statement = $db->prepare($query);
$statement->bindValue(':studentID', $studentID);
$statement->execute();
$statement->closeCursor();
// Redirect to manage instructors page
header('Location: ../manage_student.php');
exit();
?>