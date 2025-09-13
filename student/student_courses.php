<?php
 session_start();
  require_once('../database.php');

  // Make sure user is logged in and is an student
  if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== "Student") {
    header("Location: ../login_form.php");
    exit();
  }

  $studentID = $_SESSION['userID'];
  $fullName = $_SESSION['fullName'];
  $role = $_SESSION['role'];

  //Fetch courses assigned to this student
  $queryCourses = '
  SELECT 
      c.courseID,
      c.courseName,
      c.courseCode,
      c.description,
      c.imageName AS courseImage,
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Courses - student</title>
  <script src="../scripts/app.js" defer></script>
  <link rel="stylesheet" href="/web-capstone/css/app.css">
  <style>
    .courses-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
      margin-top: 30px;
    }

    .course-card {
      width: 480px;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
      background-color: #fff;
      transition: transform 0.2s ease;
    }

    .course-card:hover {
      transform: translateY(-5px);
    }

    .course-card img {
      width: 100%;
      height: 120px;
      object-fit: cover;
    }

    .course-body {
      padding: 10px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .course-title {
      display: flex;
      justify-content: space-between; 
      align-items: center;
      margin-bottom: 5px;
    }

    .course-name {
      font-weight: bold;
      font-size: 1.1em;
    }

    .course-code {
      font-size: 0.85em;
      font-weight: normal;
      color: #888;
      background: #f0f0f0;
      padding: 2px 6px;
      border-radius: 4px;
    }


    .course-description {
      font-size: 0.9em;
      color: #555;
      flex: 1;
      margin-bottom: 10px;
    }

    .course-actions {
      display: flex;
      justify-content: space-between;
    }

    .btn {
      padding: 5px 10px;
      font-size: 0.85em;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      color: #fff;
    }

    .btn-primary { background-color: #2f65f9; }
    .btn-secondary { background-color: #888; }
    .btn:hover { opacity: 0.9; }
  </style>
</head>
<body data-role="<?php echo htmlspecialchars($_SESSION['role']); ?>">
  <?php include('student_header.php'); ?>
<main class="main-content" style="display: block;">
  <div class="courses-grid">
    <?php if (count($courses) > 0): ?>
      <?php foreach ($courses as $course): ?>
        <div class="course-card">
          <img src="<?php echo !empty($course['courseImage']) ? '/web-capstone/images/' . htmlspecialchars($course['courseImage']) : '/web-capstone/images/book_placeholder.png'; ?>" alt="Course Image">

          <div class="course-body">
            <div>
              <div class="course-title">
                <span class="course-name"><?php echo htmlspecialchars($course['courseName']); ?></span>
                <span class="course-code"><?php echo htmlspecialchars($course['courseCode']); ?></span>
              </div>
              <div class="course-description"><?php echo htmlspecialchars($course['description']); ?></div>
            </div>
            <div class="course-actions">
              <a href="student_view_course.php?courseID=<?php echo $course['courseID']; ?>" class="btn btn-primary">Manage Course</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align: center; margin-top: 20px;">No courses assigned.</p>
    <?php endif; ?>
  </div>
    </main>
  <footer class="footer">
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
  </footer>
</body>
</html>
