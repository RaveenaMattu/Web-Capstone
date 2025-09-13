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

$courseID = $_GET['courseID'] ?? null;
$response = ["success" => false, "quizzes" => []];

if ($courseID) {
    $stmt = $db->prepare("SELECT quizID, title FROM quizzes WHERE courseID = :courseID");
    $stmt->bindValue(':courseID', $courseID, PDO::PARAM_INT);
    $stmt->execute();
    $response["quizzes"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response["success"] = true;
}

header('Content-Type: application/json');
echo json_encode($response);
?>