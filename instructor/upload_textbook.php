<?php
// upload_textbook.php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instructorID = $_SESSION['userID'];
    $courseID = intval($_POST['courseID']);

    // Check if file is uploaded
    if (isset($_FILES['textbookFile']) && $_FILES['textbookFile']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['textbookFile']['tmp_name'];
        $fileName = basename($_FILES['textbookFile']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file type
        if ($fileExt !== 'pdf') {
            die("Error: Only PDF files are allowed.");
        }

        // Create uploads directory if not exists
        $uploadDir = '../uploads/textbooks/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Create unique filename
        $newFileName = 'course_'.$courseID.'_textbook_'.time().'.'.$fileExt;
        $destination = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmp, $destination)) {
            // Update course table
            $stmt = $db->prepare("UPDATE courses SET textbook_pdf_path = :path WHERE courseID = :courseID AND instructorID = :instructorID");
            $stmt->bindValue(':path', '/web-capstone/uploads/textbooks/' . $newFileName);
            $stmt->bindValue(':courseID', $courseID, PDO::PARAM_INT);
            $stmt->bindValue(':instructorID', $instructorID, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();

            // Redirect back to manage course page
            header("Location: instructor_manage_course.php?courseID=$courseID");
            exit();
        } else {
            die("Error uploading file.");
        }
    } else {
        die("No file uploaded or upload error.");
    }
} else {
    die("Invalid request method.");
}
?>