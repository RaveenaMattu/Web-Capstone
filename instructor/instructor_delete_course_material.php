<?php
// instructor_delete_course_material.php
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

session_start();
require_once('../database.php');

// Ensure user is logged in as Instructor
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'Instructor') {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access"
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materialID = isset($_POST['materialID']) ? intval($_POST['materialID']) : 0;
    $instructorID = $_SESSION['userID'];

    if ($materialID <= 0) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid material ID"
        ]);
        exit();
    }

    // Get material file path
    $stmt = $db->prepare("SELECT file_path, courseID FROM course_materials WHERE materialID = :materialID");
    $stmt->bindValue(':materialID', $materialID, PDO::PARAM_INT);
    $stmt->execute();
    $material = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if (!$material) {
        echo json_encode([
            "success" => false,
            "message" => "Material not found"
        ]);
        exit();
    }

    // Optionally, verify that the instructor owns this course
    $stmtCheck = $db->prepare("SELECT * FROM courses WHERE courseID = :courseID AND instructorID = :instructorID");
    $stmtCheck->bindValue(':courseID', $material['courseID'], PDO::PARAM_INT);
    $stmtCheck->bindValue(':instructorID', $instructorID, PDO::PARAM_INT);
    $stmtCheck->execute();
    $course = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    $stmtCheck->closeCursor();

    if (!$course) {
        echo json_encode([
            "success" => false,
            "message" => "You are not authorized to delete this material"
        ]);
        exit();
    }

    // Delete the file from server
    $fileFullPath = $_SERVER['DOCUMENT_ROOT'] . $material['file_path'];
    if (file_exists($fileFullPath)) {
        @unlink($fileFullPath);
    }

    // Delete the record from database
    $stmtDelete = $db->prepare("DELETE FROM course_materials WHERE materialID = :materialID");
    $stmtDelete->bindValue(':materialID', $materialID, PDO::PARAM_INT);
    $stmtDelete->execute();
    $stmtDelete->closeCursor();
    $courseID = $material['courseID'];
    header("Location: instructor_manage_course.php?courseID=$courseID");

    echo json_encode([
        "success" => true,
        "message" => "Material deleted successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
}
?>
