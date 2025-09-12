<?php
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);
  session_start();
  require_once('../database.php');

  // Make sure user is logged in and is a student
  if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== "Student") {
    header("Location: ../login_form.php");
    exit();
  }

  $studentID = $_SESSION['userID'];
  $fullName = $_SESSION['fullName'];
  $role = $_SESSION['role'];

  // Fetch student details
  $queryStudent = 'SELECT * FROM students WHERE studentID = :id';
  $statement = $db->prepare($queryStudent);
  $statement->bindValue(':id', $studentID);
  $statement->execute();
  $student = $statement->fetch(PDO::FETCH_ASSOC);
  $statement->closeCursor();
  $imageFile = (!empty($student['imageName'])) ? $student['imageName'] : 'placeholder.jpg';

  //Fetch courses assigned to this student
  $queryCourses = '
  SELECT 
      c.courseID, 
      c.courseName, 
      c.courseCode, 
      c.imageName, 
      CONCAT(i.firstName, " ", i.lastName) AS instructorName
  FROM course_enrollments ce
  INNER JOIN courses c ON ce.courseID = c.courseID
  INNER JOIN instructors i ON c.instructorID = i.instructorID
  WHERE ce.studentID = :id AND ce.status = "enrolled"
';

  $statement = $db->prepare($queryCourses);
  $statement->bindValue(':id', $studentID, PDO::PARAM_INT);
  $statement->execute();
  $courses = $statement->fetchAll(PDO::FETCH_ASSOC);
  $statement->closeCursor();



  // Fetch tasks
  $query = "SELECT * FROM student_tasks WHERE studentID = :studentID ORDER BY created_at DESC";
  $stmt = $db->prepare($query);
  $stmt->bindValue(':studentID', $studentID);
  $stmt->execute();
  $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $stmt->closeCursor();

  // // Get total student courses in all course_enrollemnts assigned to this student
  // $queryTotalStudents = "
  //   SELECT COUNT(DISTINCT studentID) 
  //   FROM course_enrollments 
  //   WHERE courseID IN (
  //     SELECT courseID 
  //     FROM courses 
  //     WHERE instructorID = :instructorID
  //   )
  // ";
  // $statement = $db->prepare($queryTotalStudents);
  // $statement->bindValue(':instructorID', $instructorID, PDO::PARAM_INT);
  // $statement->execute();
  // $totalStudents = (int) $statement->fetchColumn();
  // $statement->closeCursor();
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
  <?php include('student_header.php'); ?> 

  <main class="main-content">
     <section class="stats">
      <div class="stat-box">
        <p>Total Courses</p>
        <h2><?php echo count($courses) > 0 ? count($courses) : "-"; ?></h2>
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
      <h4>Pending Tasks <a href="student_tasks.php">View All >></a></h4>
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
  <h4>My Courses <a href="student_courses.php">View All >></a></h4>
  <div class="courses-grid">
    <?php if(count($courses) > 0): ?>
      <?php foreach($courses as $course): ?>
        <div class="course-card">
          <div class="left-color">
            <img src="<?php echo htmlspecialchars('/web-capstone/images/' . $course['imageName']); ?>" alt="Course Image" class="course-image" width="60" height="80">
          </div>
          <div class="course-info">
            <span style="text-align: center; margin-left: 50px; font-weight: 500;"><?php echo htmlspecialchars($course['courseName']); ?></span><br>
            <small style="text-align: center; margin-left: 50px; color: #999;"><?php echo htmlspecialchars($course['courseCode']); ?></small><br>
            <span style="text-align: center; margin-left: 50px; font-weight: 300;"><?php echo htmlspecialchars($course['instructorName']); ?></span>
            <br>
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
