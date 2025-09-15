<?php
require_once('../database.php');
header('Content-Type: application/json');

$studentID = $_GET['studentID'] ?? null;
$courseID = $_GET['courseID'] ?? null;

if (!$studentID || !$courseID) {
  echo json_encode(['success' => false, 'quizzes' => []]);
  exit;
}

try {
  // Fetch quizzes with submission info
  $stmt = $db->prepare("
    SELECT q.quizID, q.courseID, q.title, q.created_at,
           s.submissionID, s.score, s.total_questions, s.percentage
    FROM quizzes q
    INNER JOIN course_enrollments ce ON ce.courseID = q.courseID
    LEFT JOIN quiz_submissions s 
           ON s.quizID = q.quizID AND s.studentID = :studentID
    WHERE ce.studentID = :studentID
      AND ce.courseID = :courseID
      AND ce.status = 'enrolled'
      AND EXISTS (SELECT 1 FROM quiz_questions qq WHERE qq.quizID = q.quizID)
    ORDER BY q.quizID ASC
  ");
  $stmt->execute([':studentID' => $studentID, ':courseID' => $courseID]);
  $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Attach questions to each quiz
  foreach ($quizzes as &$quiz) {
    $qStmt = $db->prepare("
      SELECT questionID, question_text, option_a, option_b, option_c, option_d, correct_option
      FROM quiz_questions
      WHERE quizID = :quizID
      ORDER BY questionID ASC
    ");
    $qStmt->execute([':quizID' => $quiz['quizID']]);
    $quiz['questions'] = $qStmt->fetchAll(PDO::FETCH_ASSOC);
  }

  echo json_encode(['success' => true, 'quizzes' => $quizzes]);

} catch (PDOException $e) {
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>