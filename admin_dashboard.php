<?php
  session_start();
  require_once('database.php');
  $username = $_SESSION['fullName'];
  $role = $_SESSION['role'];
 

$queryAdmin = 'SELECT * FROM admins WHERE username = :username';
$statement1 = $db->prepare($queryAdmin);
$statement1->bindValue(':username', $username);
$statement1->execute();
$admin = $statement1->fetch();
$statement1->closeCursor();
$imageFile = (!empty($admin['imageName'])) ? $admin['imageName'] : 'placeholder.jpg';

$queryInstructors = 'SELECT * FROM instructors';
$statement = $db->prepare($queryInstructors);
$statement->execute();
$instructors = $statement->fetchAll();
$statement->closeCursor();

$queryStudents = 'SELECT * FROM students';
$statement = $db->prepare($queryStudents);
$statement->execute();
$students = $statement->fetchAll();
$statement->closeCursor();

$queryCourses = "SELECT c.courseID, c.courseName, c.imageName, i.firstName AS instructorFirstName, i.lastName AS instructorLastName 
                FROM courses c
                LEFT JOIN instructors i ON c.instructorID = i.instructorID
                ORDER BY c.courseID DESC";
$statement = $db->prepare($queryCourses);
$statement->execute();
$courses = $statement->fetchAll();
$statement->closeCursor();

$queryCoursesCount = "SELECT c.courseID, c.courseName, c.imageName, i.firstName AS instructorFirstName, i.lastName AS instructorLastName 
                FROM courses c
                LEFT JOIN instructors i ON c.instructorID = i.instructorID
                ORDER BY c.courseID DESC LIMIT 4";
$statement = $db->prepare($queryCoursesCount);
$statement->execute();
$courseCounts = $statement->fetchAll();
$statement->closeCursor();

$queryTasks = 'SELECT * FROM admin_tasks WHERE isComplete = 0 ORDER BY created_at DESC LIMIT 3';
$statement = $db->prepare($queryTasks);
$statement->execute();
$tasks = $statement->fetchAll();
$statement->closeCursor();


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Learning Pod - Admin Dashboard</title>
  <script src="scripts/app.js" defer></script>
  <link rel="stylesheet" href="css/app.css"/>
</head>
<body data-role="<?php echo htmlspecialchars($role); ?>">
  <?php include('admin_details.php'); ?>
  <?php include('header.php'); ?>
  <main class="main-content">
    <section class="stats">
      <div class="stat-box"><p>Total Courses</p><h2>
        <?php
          if (count($courses) > 0) {
            echo count($courses);
          } else {
            echo "-";
          }
        ?>
        </h2>
      </div>
      <div class="stat-box"><p>Total Students</p><h2>
        <?php
            if (count($students) > 0) {
              echo count($students);
            } else {
              echo "-";
            }
          ?>
        </h2>
      </div>
      <div class="stat-box"><p>Total Instructors</p>
        <h2>
          <?php
            if (count($instructors) > 0) {
              echo count($instructors);
            } else {
              echo "-";
            }
          ?>
        </h2>
      </div>
      <div class="stat-box"><p>Pending Tasks</p><h2>
        <?php          
          echo count($tasks) > 0 ? count($tasks) : "-";
        ?>
        </h2>
      </div>
    </section>

    <section class="tasks-box">
      <h4>Pending Tasks <a href="manage_tasks.php">View All >></a></h4>
      <ul class="task-list">
        <?php
        if ($tasks) {
          foreach ($tasks as $task) {
            echo '<li style="list-style-type: disc;">'.htmlspecialchars($task['taskDescription']).'</li>';
          }
        }
        ?>
      </ul>
    </section>

    <section class="active-courses">
      <h4>Recently Added Courses <a href="manage_course.php">View All >></a></h4>
      <div class="courses-grid">
        <?php
          if ($courseCounts) {
            foreach ($courseCounts as $courseCount) {
                $imagePath = !empty($courseCount['imageName']) 
                            ? 'images/' . htmlspecialchars($courseCount['imageName']) 
                            : 'images/default-course.png'; // fallback
                echo '
                <div class="course-card">
                  <img src="'.$imagePath.'" 
                      alt="'.htmlspecialchars($courseCount['courseName']).'" 
                      class="course-thumb">
                  <div class="course-info">
                    <h5>'.htmlspecialchars($courseCount['courseName']).'</h5>
                    <p class="instructor">Instructor: '.htmlspecialchars($courseCount['instructorFirstName'].' '.$courseCount['instructorLastName'] ?? "Unknown").'</p>
                  </div>
                </div>';
            }
          } else {
            echo "<p>No active courses found.</p>";
          }
        ?>
      </div>
    </section>
  </main>
  <footer class="footer">
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
  </footer>
</body>
</html>
