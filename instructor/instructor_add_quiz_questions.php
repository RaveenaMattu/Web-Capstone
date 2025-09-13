<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

require_once('../database.php'); // uses PDO

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $quizID = isset($_POST['quizID']) ? intval($_POST['quizID']) : 0;

        if ($quizID <= 0) {
            throw new Exception("Invalid quiz ID.");
        }

        // Extract all posted questions
        $questions = [];
        foreach ($_POST as $key => $value) {
            if (preg_match('/^question_(\d+)$/', $key, $matches)) {
                $idx = $matches[1];
                $questions[$idx]['question_text'] = trim($value);
            }
            if (preg_match('/^question_(\d+)_([a-d])$/', $key, $matches)) {
                $idx = $matches[1];
                $opt = $matches[2];
                $questions[$idx]['option_' . $opt] = trim($value);
            }
            if (preg_match('/^question_(\d+)_correct$/', $key, $matches)) {
                $idx = $matches[1];
                $questions[$idx]['correct_option'] = trim($value);
            }
            if (preg_match('/^question_(\d+)_id$/', $key, $matches)) {
                $idx = $matches[1];
                $questions[$idx]['questionID'] = intval($value);
            }
        }

        if (empty($questions)) {
            throw new Exception("No questions submitted.");
        }

        // Prepare insert/update statements
        $insertStmt = $db->prepare("
            INSERT INTO quiz_questions 
            (quizID, question_text, option_a, option_b, option_c, option_d, correct_option)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $updateStmt = $db->prepare("
            UPDATE quiz_questions 
            SET question_text = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_option = ?
            WHERE questionID = ? AND quizID = ?
        ");

        foreach ($questions as $q) {
            if (!empty($q['questionID'])) {
                // Update existing question
                $updateStmt->execute([
                    $q['question_text'] ?? '',
                    $q['option_a'] ?? '',
                    $q['option_b'] ?? '',
                    $q['option_c'] ?? '',
                    $q['option_d'] ?? '',
                    $q['correct_option'] ?? 'a',
                    $q['questionID'],
                    $quizID
                ]);
            } else {
                // Insert new question
                $insertStmt->execute([
                    $quizID,
                    $q['question_text'] ?? '',
                    $q['option_a'] ?? '',
                    $q['option_b'] ?? '',
                    $q['option_c'] ?? '',
                    $q['option_d'] ?? '',
                    $q['correct_option'] ?? 'a'
                ]);
            }
        }

        $response['success'] = true;
        $response['message'] = "Questions saved successfully.";
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>