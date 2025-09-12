<?php
session_start();
require_once('../database.php');

// Ensure instructor
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'Instructor') {
    echo json_encode(["success"=>false,"message"=>"Unauthorized"]);
    exit();
}

$instructorID = $_SESSION['userID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseID = intval($_POST['courseID']);
    $quizTitle = trim($_POST['quizTitle']);

    if (empty($quizTitle) || $courseID <= 0) {
        echo json_encode(["success"=>false,"message"=>"Invalid input"]);
        exit();
    }

    // Insert quiz
    $stmt = $db->prepare("INSERT INTO quizzes (courseID, title) VALUES (:courseID, :title)");
    $stmt->bindValue(':courseID', $courseID, PDO::PARAM_INT);
    $stmt->bindValue(':title', $quizTitle);
    $stmt->execute();
    $quizID = $db->lastInsertId();
    $stmt->closeCursor();

    // Insert questions
    foreach ($_POST as $key => $value) {
        if (preg_match('/^question_(\d+)$/', $key, $matches)) {
            $qNum = $matches[1];
            $question_text = trim($value);
            $option_a = trim($_POST["question_{$qNum}_a"]);
            $option_b = trim($_POST["question_{$qNum}_b"]);
            $option_c = trim($_POST["question_{$qNum}_c"]);
            $option_d = trim($_POST["question_{$qNum}_d"]);
            $correct_option = $_POST["question_{$qNum}_correct"];

            $stmtQ = $db->prepare("INSERT INTO quiz_questions 
                (quizID, question_text, option_a, option_b, option_c, option_d, correct_option)
                VALUES (:quizID, :qtext, :a, :b, :c, :d, :correct)");
            $stmtQ->bindValue(':quizID', $quizID, PDO::PARAM_INT);
            $stmtQ->bindValue(':qtext', $question_text);
            $stmtQ->bindValue(':a', $option_a);
            $stmtQ->bindValue(':b', $option_b);
            $stmtQ->bindValue(':c', $option_c);
            $stmtQ->bindValue(':d', $option_d);
            $stmtQ->bindValue(':correct', $correct_option);
            $stmtQ->execute();
            $stmtQ->closeCursor();
        }
    }

    // Fetch updated quizzes for this course
    $stmtFetch = $db->prepare("SELECT quizID, title FROM quizzes WHERE courseID = :courseID ORDER BY quizID ASC");
    $stmtFetch->bindValue(':courseID', $courseID, PDO::PARAM_INT);
    $stmtFetch->execute();
    $quizzes = $stmtFetch->fetchAll(PDO::FETCH_ASSOC);
    $stmtFetch->closeCursor();

    echo json_encode([
        "success" => true,
        "message" => "Quiz created successfully",
        "quizzes" => $quizzes
    ]);
    exit();

} else {
    echo json_encode(["success"=>false,"message"=>"Invalid request"]);
    exit();
}
?>