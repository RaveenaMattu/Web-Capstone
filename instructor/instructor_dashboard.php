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
  $role = $_SESSION['role'];

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

  // Fetch tasks
  $query = "SELECT * FROM instructor_tasks WHERE instructorID = :instructorID ORDER BY created_at DESC";
  $stmt = $db->prepare($query);
  $stmt->bindValue(':instructorID', $instructorID);
  $stmt->execute();
  $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $stmt->closeCursor();

  // Get total students in all courses assigned to this instructor
  $queryTotalStudents = "
      SELECT COUNT(DISTINCT studentID) 
      FROM course_enrollments 
      WHERE courseID IN (
          SELECT courseID 
          FROM courses 
          WHERE instructorID = :instructorID
      )
  ";
  $statement = $db->prepare($queryTotalStudents);
  $statement->bindValue(':instructorID', $instructorID, PDO::PARAM_INT);
  $statement->execute();
  $totalStudents = (int) $statement->fetchColumn();
  $statement->closeCursor();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Learning Pod - Instructor Dashboard</title>
  <script src="/web-capstone/scripts/app.js" defer></script>
  <link rel="stylesheet" href="/web-capstone/css/app.css"/>
</head>
<body data-role="<?php echo htmlspecialchars($role); ?>">
  <?php include('instructor_header.php'); ?> 

  <main class="main-content">
    <section class="stats">
      <div class="stat-box">
        <p>Total Courses</p>
        <h2><?php echo count($courses) > 0 ? count($courses) : "-"; ?></h2>
      </div>
      <div class="stat-box">
        <p>Total Students</p>
        <h2><?php echo $totalStudents ?></h2>
      </div>
      <div class="stat-box">
        <p>Pending Tasks</p>
        <h2><?php echo count($tasks) > 0 ? count($tasks) : "-"; ?></h2>
      </div>
      <div class="stat-box">
        <p>Active Quiz</p>
        <h2>2</h2>
      </div>
    </section>

    <section class="tasks-box">
      <h4>Pending Tasks <a href="instructor_tasks.php">View All >></a></h4>
      <ul class="task-list">
        <?php
        if (count($tasks) > 0) {
          foreach ($tasks as $task) {
            echo '<li>'.htmlspecialchars($task['taskDescription']).'</li>';
          }
        } else {
          echo '<p>No Pending tasks.</p>';
        }
        ?>
      </ul>
    </section>

<section class="active-courses">
  <h4>My Courses <a href="instructor_courses.php">View All >></a></h4>
  <div class="courses-grid">
    <?php if(count($courses) > 0): ?>
      <?php foreach($courses as $course): ?>
        <?php
          // Fetch number of students for this course
          $queryEnrollments = 'SELECT COUNT(*) FROM course_enrollments WHERE courseID = :courseID';
          $statement = $db->prepare($queryEnrollments);
          $statement->bindValue(':courseID', $course['courseID'], PDO::PARAM_INT);
          $statement->execute();
          $studentCount = (int) $statement->fetchColumn();
          $statement->closeCursor();
        ?>
        <div class="course-card">
          <div class="left-color">
            <img src="<?php echo htmlspecialchars('/web-capstone/images/' . $course['imageName']); ?>" alt="Course Image" class="course-image" width="60" height="80">
          </div>
          <div class="course-info">
            <span style="text-align: center; margin-left: 50px;"><?php echo htmlspecialchars($course['courseName']); ?></span>
            <br>
            <?php if($studentCount > 0): ?>
              <p style="margin: 8px 50px; color: #555; font-size: .8em;">
                Number of Students: <?php echo $studentCount; ?>
              </p>
            <?php else: ?>
              <p style="margin: 8px 50px; color: #555; font-size: .9em;">No students enrolled yet.</p>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
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
