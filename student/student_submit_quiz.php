<?php
require_once('../database.php');
header('Content-Type: application/json');

// Decode JSON body
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['quizID'], $data['studentID'], $data['score'], $data['total'])) {
  echo json_encode(['success' => false, 'message' => 'Invalid request']);
  exit;
}

$quizID = (int)$data['quizID'];
$studentID = (int)$data['studentID'];
$score = (int)$data['score'];
$total = (int)$data['total'];

// Avoid division by zero
$percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0;

try {
  // Check if already submitted
  $stmt = $db->prepare("SELECT 1 FROM quiz_submissions WHERE quizID = :quizID AND studentID = :studentID");
  $stmt->execute([':quizID' => $quizID, ':studentID' => $studentID]);

  if ($stmt->rowCount() > 0) {
    echo json_encode(["success" => false, "message" => "Quiz already submitted"]);
    exit;
  }

  // Insert submission
  $stmt = $db->prepare("INSERT INTO quiz_submissions 
    (quizID, studentID, score, total_questions, percentage) 
    VALUES (:quizID, :studentID, :score, :total, :percentage)");
  $stmt->execute([
    ':quizID' => $quizID,
    ':studentID' => $studentID,
    ':score' => $score,
    ':total' => $total,
    ':percentage' => $percentage
  ]);

  echo json_encode([
    "success" => true,
    "message" => "Quiz submitted successfully",
    "score" => $score,
    "total" => $total,
    "percentage" => $percentage
  ]);

} catch (PDOException $e) {
  echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>