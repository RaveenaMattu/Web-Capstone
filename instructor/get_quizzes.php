<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

require_once('../database.php'); // uses PDO

$courseID = isset($_GET['courseID']) ? intval($_GET['courseID']) : 0;
$response = ['success' => false, 'quizzes' => []];

if ($courseID > 0) {
    try {
        // Fetch quizzes for the course
        $stmt = $db->prepare("SELECT quizID, title FROM quizzes WHERE courseID = ?");
        $stmt->execute([$courseID]);
        $quizzes = [];

        while ($quiz = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $quizID = $quiz['quizID'];

            // Fetch questions for this quiz
            $qStmt = $db->prepare("SELECT questionID, question_text, option_a, option_b, option_c, option_d, correct_option
                                   FROM quiz_questions WHERE quizID = ?");
            $qStmt->execute([$quizID]);

            $questions = $qStmt->fetchAll(PDO::FETCH_ASSOC);

            // Ensure consistent structure for JS
            foreach ($questions as &$q) {
                $q['id'] = $q['questionID'];
            }

            $quizzes[] = [
                'quizID' => $quizID,
                'title' => $quiz['title'],
                'questions' => $questions
            ];
        }

        $response['success'] = true;
        $response['quizzes'] = $quizzes;

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
?>