<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
session_start();
require_once('../database.php');

// Make sure user is logged in and is an instructor
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== "Instructor") {
  header("Location: ../login_form.php");
  exit();
}

$instructorID = $_SESSION['userID'];
$fullName = $_SESSION['fullName'];

// Fetch instructor details
$queryInstructor = 'SELECT * FROM instructors WHERE instructorID = :id';
$statement = $db->prepare($queryInstructor);
$statement->bindValue(':id', $instructorID);
$statement->execute();
$instructor = $statement->fetch(PDO::FETCH_ASSOC);
$statement->closeCursor();
$imageFile = (!empty($instructor['imageName'])) ? $instructor['imageName'] : 'placeholder.jpg';

// Fetch courses assigned to this instructor
$queryCourses = 'SELECT * FROM courses WHERE instructorID = :id';
$statement = $db->prepare($queryCourses);
$statement->bindValue(':id', $instructorID);
$statement->execute();
$courses = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement->closeCursor();

// Count total students in their courses
// $totalStudents = 0;
// foreach ($courses as $course) {
//     $queryStudents = 'SELECT COUNT(*) AS studentCount FROM enrollments WHERE courseID = :courseID';
//     $stmt = $db->prepare($queryStudents);
//     $stmt->bindValue(':courseID', $course['courseID']);
//     $stmt->execute();
//     $count = $stmt->fetch(PDO::FETCH_ASSOC);
//     $totalStudents += $count['studentCount'];
//     $stmt->closeCursor();
// }

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Learning Pod - Instructor Dashboard</title>
  <script src="../scripts/app.js" defer></script>
  <link rel="stylesheet" href="../css/app.css"/>
</head>
<body>
<header class="header">
  <div class="logo"><img src="images/logo.png" alt="Logo" height="100" width="100"></div>

  <nav class="nav">
    <a href="#" class="active">Dashboard</a>
    <a href="instructor_courses.php">My Courses</a>
    <a href="instructor_students.php">My Students</a>
    <a href="instructor_tasks.php">Tasks</a>
  </nav>
  <div class="user-info">Hi, <?php echo htmlspecialchars($fullName); ?>
    <div class="profile-wrapper">
      <div class="profile-circle">
        <img src="<?php echo htmlspecialchars('../images/' . $imageFile); ?>" width="40" height="40" style="border-radius:50%;" alt="Profile Picture">
      </div>
      <div class="logOutBox">
        <a href="#" onclick="openUpdateInstructor();">Update Profile</a>
        <a href="logout.php">Log Out</a>
      </div>
    </div>      
  </div>  
</header>

<main class="main-content">
  <section class="stats">
    <div class="stat-box">
      <p>Total Courses</p>
      <h2><?php echo count($courses) > 0 ? count($courses) : "-"; ?></h2>
    </div>
    <div class="stat-box">
      <p>Total Students</p>
      <h2><?php echo  "-"; ?></h2>
    </div>
    <div class="stat-box">
      <p>Pending Tasks</p>
      <h2>5</h2>
    </div>
    <div class="stat-box">
      <p>Active Quiz</p>
      <h2>2</h2>
    </div>
  </section>

  <section class="tasks-box">
    <h4>Pending Tasks <a href="#">View All >></a></h4>
    <ul class="task-list">
      <li>- Review assignment submissions.</li>
      <li>- Prepare new lecture material.</li>
      <li>- Grade quizzes for course(s).</li>
    </ul>
  </section>

  <section class="active-courses">
    <h4>My Courses <a href="instructor_courses.php">View All >></a></h4>
    <div class="courses-grid">
      <?php if(count($courses) > 0): ?>
        <?php foreach($courses as $course): ?>
          <div class="course-card">
            <div class="left-color">
              <img src="<?php echo htmlspecialchars('../images/' . $course['imageName']); ?>" alt="Course Image" class="course-image">
            </div>
            <span><?php echo htmlspecialchars($course['courseName']); ?></span>
          </div>
        <?php endforeach; 
      ?>
      <?php else: ?>
        <p>No courses assigned yet.</p>
      <?php endif; ?>
    </div>
  </section>
</main>

<footer class="footer">
  © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
</footer>
</body>
</html>
