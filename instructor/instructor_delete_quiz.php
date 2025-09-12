<?php
session_start();
require_once('../database.php');

// Ensure instructor
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'Instructor') {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

$instructorID = $_SESSION['userID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $quizID = intval($input['quizID'] ?? 0);

    if ($quizID <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid quiz ID"]);
        exit();
    }

    // Check if quiz belongs to a course of this instructor
    $stmtCheck = $db->prepare("SELECT courseID FROM quizzes q
                               JOIN courses c ON q.courseID = c.courseID
                               WHERE q.quizID = :quizID AND c.instructorID = :instructorID");
    $stmtCheck->bindValue(':quizID', $quizID, PDO::PARAM_INT);
    $stmtCheck->bindValue(':instructorID', $instructorID, PDO::PARAM_INT);
    $stmtCheck->execute();
    $quiz = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    $stmtCheck->closeCursor();

    if (!$quiz) {
        echo json_encode(["success" => false, "message" => "Quiz not found or unauthorized"]);
        exit();
    }

    $courseID = $quiz['courseID'];

    // Delete quiz questions first
    $stmtDelQuestions = $db->prepare("DELETE FROM quiz_questions WHERE quizID = :quizID");
    $stmtDelQuestions->bindValue(':quizID', $quizID, PDO::PARAM_INT);
    $stmtDelQuestions->execute();
    $stmtDelQuestions->closeCursor();

    // Delete the quiz itself
    $stmtDelQuiz = $db->prepare("DELETE FROM quizzes WHERE quizID = :quizID");
    $stmtDelQuiz->bindValue(':quizID', $quizID, PDO::PARAM_INT);
    $stmtDelQuiz->execute();
    $stmtDelQuiz->closeCursor();

    // Fetch updated quizzes for this course
    $stmtFetch = $db->prepare("SELECT quizID, title FROM quizzes WHERE courseID = :courseID ORDER BY quizID ASC");
    $stmtFetch->bindValue(':courseID', $courseID, PDO::PARAM_INT);
    $stmtFetch->execute();
    $quizzes = $stmtFetch->fetchAll(PDO::FETCH_ASSOC);
    $stmtFetch->closeCursor();

    $courseID = $quizzes['courseID'];
    header("Location: instructor_manage_course.php?courseID=$courseID");

    echo json_encode([
        "success" => true,
        "message" => "Quiz deleted successfully",
        "quizzes" => $quizzes
    ]);
    exit();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit();
}
?>