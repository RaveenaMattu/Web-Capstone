<?php
require_once('../database.php');

$studentID = isset($_GET['studentID']) ? intval($_GET['studentID']) : 0;
$courseID  = isset($_GET['courseID']) ? intval($_GET['courseID']) : 0;

if (!$studentID || !$courseID) {
    echo json_encode(['success' => false, 'quizzes' => []]);
    exit;
}

$sql = "
    SELECT q.quizID, q.courseID, q.title, q.created_at
    FROM quizzes q
    INNER JOIN course_enrollments ce ON ce.courseID = q.courseID
    WHERE ce.studentID = :studentID
      AND ce.courseID = :courseID
      AND ce.status = 'enrolled'
      AND EXISTS (SELECT 1 FROM quiz_questions qq WHERE qq.quizID = q.quizID)
    ORDER BY q.quizID ASC
";

$stmt = $db->prepare($sql);
$stmt->bindValue(':studentID', $studentID, PDO::PARAM_INT);
$stmt->bindValue(':courseID', $courseID, PDO::PARAM_INT);
$stmt->execute();
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch questions for each quiz
foreach ($quizzes as &$quiz) {
    $qStmt = $db->prepare("SELECT * 
                           FROM quiz_questions WHERE quizID = :quizID");
    $qStmt->bindValue(':quizID', $quiz['quizID'], PDO::PARAM_INT);
    $qStmt->execute();
    $quiz['questions'] = $qStmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode(['success' => true, 'quizzes' => $quizzes]);
?>