<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require_once('../database.php');
$courseID = filter_input(INPUT_POST, 'courseID', FILTER_VALIDATE_INT);
if (!$courseID) {
    echo "Invalid course ID.";
    exit();
}

// Delete the course record
$query = 'DELETE FROM courses WHERE courseID = :courseID';  
$statement = $db->prepare($query);
$statement->bindValue(':courseID', $courseID);
$statement->execute();
$statement->closeCursor();
// Redirect to manage courses page
header('Location: ../manage_course.php');
exit();
?>