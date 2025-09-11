<?php
  $current_page = basename($_SERVER['PHP_SELF']); 
  $current_dir  = basename(dirname($_SERVER['PHP_SELF'])); 

  // Fetch instructor details
  $queryInstructor = 'SELECT * FROM instructors WHERE instructorID = :instructorID';
  $statement = $db->prepare($queryInstructor);
  $statement->bindValue(':instructorID', $_SESSION['userID']);
  $statement->execute();
  $instructor = $statement->fetch();
  $statement->closeCursor();
  $imageFile = (!empty($instructor['imageName'])) ? $instructor['imageName'] : 'placeholder.jpg';
?>
<header class="header">
  <div class="logo"><img src="/web-capstone/images/logo.png" alt="Logo" height="100" width="100"></div>

  <nav class="nav">
    <a href="/web-capstone/instructor/instructor_dashboard.php" 
       class="<?= $current_page == 'instructor_dashboard.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="/web-capstone/instructor/instructor_courses.php" 
       class="<?= ($current_page == 'instructor_courses.php'|| $current_page == 'instructor_manage_course.php') ? 'active' : '' ?>">My Courses</a>
    <a href="/web-capstone/instructor/instructor_manage_enrollments.php" 
       class="<?= $current_page == 'instructor_manage_enrollments.php' ? 'active' : '' ?>">Manage Enrollments</a>
    <a href="/web-capstone/instructor/instructor_tasks.php" 
       class="<?= $current_page == 'instructor_tasks.php' ? 'active' : '' ?>">Tasks</a>
  </nav>

  <?php include('../profile.php'); ?>   
</header>
