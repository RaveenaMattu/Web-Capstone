<?php
  $current_page = basename($_SERVER['PHP_SELF']); 
  $current_dir  = basename(dirname($_SERVER['PHP_SELF'])); 

  // Fetch instructor details
  $queryStudent = 'SELECT * FROM students WHERE studentID = :studentID';
  $statement = $db->prepare($queryStudent);
  $statement->bindValue(':studentID', $_SESSION['userID']);
  $statement->execute();
  $student = $statement->fetch();
  $statement->closeCursor();
  $imageFile = (!empty($student['imageName'])) ? $student['imageName'] : 'placeholder.jpg';
?>
<header class="header">
  <div class="logo"><img src="/web-capstone/images/logo.png" alt="Logo" height="100" width="100"></div>

  <nav class="nav">
    <a href="/web-capstone/student/student_dashboard.php" 
       class="<?= $current_page == 'student_dashboard.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="/web-capstone/student/student_courses.php" 
       class="<?= ($current_page == 'student_courses.php'|| $current_page == 'student_view_course.php') ? 'active' : '' ?>">My Courses</a>
    <a href="/web-capstone/student/student_tasks.php" 
       class="<?= $current_page == 'student_tasks.php' ? 'active' : '' ?>">Tasks</a>
  </nav>

  <?php include('../profile.php'); ?>   
</header>
