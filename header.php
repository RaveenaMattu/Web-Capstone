<?php
  $current_page = basename($_SERVER['PHP_SELF']); 
  $current_dir  = basename(dirname($_SERVER['PHP_SELF'])); 

  // Fetch admin details
  $queryAdmin = 'SELECT * FROM admins WHERE adminID = :adminID';
  $statement = $db->prepare($queryAdmin);
  $statement->bindValue(':adminID', $_SESSION['adminID']);
  $statement->execute();
  $admin = $statement->fetch();
  $statement->closeCursor();
  $imageFile = (!empty($admin['imageName'])) ? $admin['imageName'] : 'placeholder.jpg';
?>
<!-- header.php -->
<header class="header">
  <div class="logo">
    <img src="/web-capstone/images/logo.png" alt="Logo" height="100" width="100">
  </div>
  <nav class="nav">
    <a href="/web-capstone/admin_dashboard.php" class="<?= $current_page == 'admin_dashboard.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="/web-capstone/manage_instructor.php" class="<?= ($current_page == 'manage_instructor.php'|| $current_dir == 'manage_instructor') ? 'active' : '' ?>">Manage Instructors</a>
    <a href="/web-capstone/manage_student.php" class="<?= ($current_page == 'manage_student.php'|| $current_dir == 'manage_student') ? 'active' : '' ?>">Manage Students</a>
    <a href="/web-capstone/manage_course.php" class="<?= ($current_page == 'manage_course.php'|| $current_dir == 'manage_course') ? 'active' : '' ?>">Manage Courses</a>
    <a href="/web-capstone/manage_tasks.php" class="<?= ($current_page == 'manage_tasks.php'|| $current_dir == 'manage_task') ? 'active' : '' ?>">Tasks</a>
  </nav>
  <?php include('profile.php'); ?>
</header>
