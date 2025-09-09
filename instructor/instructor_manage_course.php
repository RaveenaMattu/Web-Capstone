<?php
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

$instructorID = $_SESSION['userID'];
$fullName = $_SESSION['fullName'];
$role = $_SESSION['role'];

// Fetch all courses assigned to this instructor
$queryCourses = 'SELECT * FROM courses WHERE instructorID = :id';
$statement    = $db->prepare($queryCourses);
$statement->bindValue(':id', $instructorID, PDO::PARAM_INT);
$statement->execute();
$courses = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement->closeCursor();

// If a courseID is passed via GET (for managing a single course)
$course = null;
if (isset($_GET['courseID'])) {
  $courseID = intval($_GET['courseID']);
  $queryCourse = 'SELECT * FROM courses WHERE courseID = :courseID AND instructorID = :instructorID';
  $stmtCourse  = $db->prepare($queryCourse);
  $stmtCourse->bindValue(':courseID', $courseID, PDO::PARAM_INT);
  $stmtCourse->bindValue(':instructorID', $instructorID, PDO::PARAM_INT);
  $stmtCourse->execute();
  $course = $stmtCourse->fetch(PDO::FETCH_ASSOC);
  $stmtCourse->closeCursor();

  if (!$course) {
    die('Course not found or you do not have access to it.');
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Course - Instructor</title>
  <script src="/web-capstone/scripts/app.js" defer></script>
  <link rel="stylesheet" href="/web-capstone/css/app.css"/>
</head>
<body data-role="<?php echo htmlspecialchars($role); ?>">
  <?php include('instructor_header.php'); ?> 
  <main class="main-content" style="display: block;">
    <?php if ($course): ?>
      <div class="course-card" style="display: flex; overflow: hidden;padding: 20px 50px; margin: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); background: #fff; width: 60%">
        
        <!-- Course details on the left -->
        <div class="course-body" style="flex: 1;display: flex; flex-direction: column; justify-content: space-between;">
          <div>
            <h2 style="margin: 0;"><?php echo htmlspecialchars($course['courseName']); ?></h2>
            <span style="color: #888; font-size: 0.9em;"><?php echo htmlspecialchars($course['courseCode']); ?></span>
            <p style="margin: 10px 40px  0 0; color: #555; width: 550px;"><?php echo htmlspecialchars($course['description']); ?></p>
          </div>
        </div>

        <!-- Course image on the right -->
        <div class="course-image" style="width: 200px; flex-shrink: 0;">
          <img src="<?php echo !empty($course['imageName']) ? '/web-capstone/images/' . htmlspecialchars($course['imageName']) : '/web-capstone/images/placeholder.jpg'; ?>" 
               alt="Course Image" 
               style="width: 100%; height: 100%; object-fit: cover;">
        </div>

      </div>
    <?php else: ?>
      <p style="text-align:center; margin-top:20px;">No course selected.</p>
    <?php endif; ?>
</main>

  <footer class="footer">
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
  </footer>
</body>
</html>
