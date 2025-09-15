<?php
require_once('../database.php');
header('Content-Type: application/json');

$instructorID = $_GET['instructorID'] ?? null;
$courseID = $_GET['courseID'] ?? null;

if (!$instructorID || !$courseID) {
    echo json_encode(['success'=>false, 'quizzes'=>[]]);
    exit;
}

try {
    // Get quizzes for this course by instructor
    $stmt = $db->prepare("
        SELECT q.quizID, q.title
        FROM quizzes q
        INNER JOIN courses c ON c.courseID = q.courseID
        WHERE q.courseID = :courseID AND c.instructorID = :instructorID
        ORDER BY q.quizID ASC
    ");
    $stmt->execute([':courseID'=>$courseID, ':instructorID'=>$instructorID]);
    $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($quizzes as &$quiz) {
        // Get submissions for each quiz
        $stmt2 = $db->prepare("
            SELECT s.score, s.total_questions, s.percentage, s.submitted_at, CONCAT(st.firstName,' ',st.lastName) as student_name
            FROM quiz_submissions s
            JOIN students st ON st.studentID = s.studentID
            WHERE s.quizID = :quizID
        ");
        $stmt2->execute([':quizID'=>$quiz['quizID']]);
        $quiz['submissions'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $quiz['submissions_count'] = count($quiz['submissions']);
    }

    echo json_encode(['success'=>true, 'quizzes'=>$quizzes]);

} catch (PDOException $e) {
    echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
}
?>