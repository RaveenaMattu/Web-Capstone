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
    <a href="/web-capstone/index.php"><img src="/web-capstone/images/logo.png" alt="Logo" height="100" width="100"></a>
  </div>
  <nav class="nav">
    <a href="/web-capstone/admin_dashboard.php" class="<?= $current_page == 'admin_dashboard.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="/web-capstone/manage_instructor.php" class="<?= ($current_page == 'manage_instructor.php'|| $current_dir == 'manage_instructor') ? 'active' : '' ?>">Manage Instructors</a>
    <a href="/web-capstone/manage_student.php" class="<?= ($current_page == 'manage_student.php'|| $current_dir == 'manage_student') ? 'active' : '' ?>">Manage Students</a>
    <!-- Academics dropdown -->
    <div class="dropdown">
      <a href="#" class="dropbtn <?= in_array($current_page, ['manage_course.php','pending_enrollments.php']) ? 'active' : '' ?>">Academics</a>
      <div class="dropdown-content">
        <a href="/web-capstone/manage_course.php">Manage Courses</a>
        <a href="/web-capstone/pending_enrollments.php">Manage Enrollments</a>
      </div>
    </div>
    <a href="/web-capstone/manage_tasks.php" class="<?= ($current_page == 'manage_tasks.php'|| $current_dir == 'manage_task') ? 'active' : '' ?>">Tasks</a>
  </nav>
  <?php include('profile.php'); ?>
</header>

<style>
.nav .dropdown {
  position: relative;
  display: inline-block;
}

.nav .dropbtn {
  cursor: pointer;
  text-decoration: none;
  padding: 10px 15px;
  display: inline-block;
}

/* Dropdown content */
.nav .dropdown-content {
  display: none;
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  background-color: #fff;
  min-width: 300px;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.02);
  z-index: 100;
  border-radius: 5px;
  text-align: center;
}

/* Dropdown links */
.nav .dropdown-content a {
  display: block;
  padding: 8px 50px;
  color: #333;
  text-decoration: none;
  margin: 5px auto;
  text-align: left;
}

.nav .dropdown-content a:hover {
  background-color: #f0f0f0;
}

/* Show dropdown on hover */
.nav .dropdown:hover .dropdown-content {
  display: block;
}

/* Active button styling */
.nav .dropdown.active > .dropbtn {
  font-weight: bold;
}

</style>
