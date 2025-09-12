<?php
// instructor_manage_course.php
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

$instructorID = $_SESSION['userID'];
$role = $_SESSION['role'];

// Fetch all courses assigned to this instructor
$stmt = $db->prepare('SELECT * FROM courses WHERE instructorID = :id');
$stmt->bindValue(':id', $instructorID, PDO::PARAM_INT);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

// Get selected course
$course = null;
$courseID = null;
if (isset($_GET['courseID'])) {
  $courseID = intval($_GET['courseID']);
  $stmt = $db->prepare('SELECT * FROM courses WHERE courseID = :courseID AND instructorID = :instructorID');
  $stmt->bindValue(':courseID', $courseID, PDO::PARAM_INT);
  $stmt->bindValue(':instructorID', $instructorID, PDO::PARAM_INT);
  $stmt->execute();
  $course = $stmt->fetch(PDO::FETCH_ASSOC);
  $stmt->closeCursor();
}

// Fetch student list for all courses of this instructor
$queryStudents = "
  SELECT s.studentID, s.firstName, s.lastName, c.courseName, c.courseID
  FROM course_enrollments ce
  JOIN students s ON ce.studentID = s.studentID
  JOIN courses c ON ce.courseID = c.courseID
  WHERE c.instructorID = :instructorID AND ce.status = 'enrolled'
  ORDER BY c.courseName, s.firstName
";
$statement = $db->prepare($queryStudents);
$statement->bindValue(':instructorID', $instructorID, PDO::PARAM_INT);
$statement->execute();
$students = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement->closeCursor();

// Fetch materials for the selected course
$materials = [];
if ($courseID) {
  $stmt = $db->prepare("SELECT * FROM course_materials WHERE courseID = :courseID ORDER BY materialID ASC");
  $stmt->bindValue(':courseID', $courseID, PDO::PARAM_INT);
  $stmt->execute();
  $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $stmt->closeCursor();
}
?>