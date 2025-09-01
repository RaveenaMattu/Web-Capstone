<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL); 
session_start();

require_once('../database.php');

// Collect form data safely
$courseID        = filter_input(INPUT_POST, 'courseID', FILTER_VALIDATE_INT);
$courseCode      = filter_input(INPUT_POST, 'courseCode');
$courseName      = filter_input(INPUT_POST, 'courseName');
$courseDesc      = filter_input(INPUT_POST, 'courseDesc');
$courseInstructor = filter_input(INPUT_POST, 'courseInstructor'); // should be instructorID
$image           = $_FILES['image'] ?? null;

// Validate required fields
if (!$courseID || !$courseCode || !$courseName || !$courseDesc || !$courseInstructor) {
    $_SESSION['error'] = "Missing required fields.";
    header("Location: update_course_form.php?courseID=$courseID");
    exit();
}

// Fetch existing course
$query = 'SELECT * FROM courses WHERE courseID = :courseID';
$statement = $db->prepare($query);
$statement->bindValue(':courseID', $courseID);
$statement->execute();
$existingCourse = $statement->fetch();
$statement->closeCursor();

if (!$existingCourse) {
    $_SESSION['error'] = "Course not found.";
    header("Location: manage_course.php");
    exit();
}

// Store original values
$originalCourseCode = $existingCourse['courseCode'];
$originalImage      = $existingCourse['imageName'];

// Check if course code changed → must be unique
if ($courseCode !== $originalCourseCode) {
    $query = 'SELECT COUNT(*) FROM courses WHERE courseCode = :courseCode AND courseID != :courseID';
    $statement = $db->prepare($query);
    $statement->bindValue(':courseCode', $courseCode);
    $statement->bindValue(':courseID', $courseID);
    $statement->execute();
    $courseCodeCount = $statement->fetchColumn();
    $statement->closeCursor();

    if ($courseCodeCount > 0) {
        $_SESSION['error'] = 'Course Code already exists.';
        header("Location: update_course_form.php?courseID=$courseID");
        exit();
    }
}

// Handle image upload (keep old if none uploaded)
$imageName = $originalImage;
if ($image && $image['error'] === UPLOAD_ERR_OK) {
    $image_dir = '../images/';
    $filename = basename($image['name']);
    $target = $image_dir . $filename;

    if (move_uploaded_file($image['tmp_name'], $target)) {
        $imageName = $filename;
    } else {
        $_SESSION['error'] = "Failed to upload image.";
        header("Location: update_course_form.php?courseID=$courseID");
        exit();
    }
}

// ✅ Perform update
$query = 'UPDATE courses 
          SET courseCode = :courseCode, 
              courseName = :courseName, 
              description = :courseDesc, 
              instructorID = :courseInstructor, 
              imageName = :imageName
          WHERE courseID = :courseID';

$statement = $db->prepare($query);
$statement->bindValue(':courseCode', $courseCode);
$statement->bindValue(':courseName', $courseName);
$statement->bindValue(':courseDesc', $courseDesc);
$statement->bindValue(':courseInstructor', $courseInstructor, PDO::PARAM_INT);
$statement->bindValue(':imageName', $imageName);
$statement->bindValue(':courseID', $courseID, PDO::PARAM_INT);
$statement->execute();
$statement->closeCursor();

// Redirect back
$_SESSION['success'] = "Course updated successfully.";
header("Location: ../manage_course.php");
exit();
