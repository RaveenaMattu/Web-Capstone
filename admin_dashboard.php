<?php
session_start();
require('database.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Learning Pod - Admin Dashboard</title>
  <link rel="stylesheet" href="css/app.css"/>
</head>
<body>
  <header class="header">
    <div class="logo"></div>
    <nav class="nav">
      <a href="#" class="active">Dashboard</a>
      <a href="#">Manage Instructors</a>
      <a href="#">Manage Students</a>
      <a href="#">Manage Courses</a>
      <a href="#">Tasks</a>
    </nav>
    <div class="user-info">Hi, Admin <div class="profile-circle" href="login.php"></div></div>
  </header>

  <main class="main-content">
    <section class="stats">
      <div class="stat-box"><p>Total Courses</p><h2>4</h2></div>
      <div class="stat-box"><p>Total Students</p><h2>12</h2></div>
      <div class="stat-box"><p>Total Instructors</p><h2>5</h2></div>
      <div class="stat-box"><p>Pending Tasks</p><h2>8</h2></div>
    </section>

    <section class="tasks-box">
      <h4>Pending Tasks <a href="#">View All >></a></h4>
      <ul class="task-list">
        <li>- New course requests.</li>
        <li>- Add 3 new instructors.</li>
        <li>- Review students performance report.</li>
      </ul>
    </section>

    <section class="active-courses">
      <h4>Active Courses <a href="#">View All >></a></h4>
      <div class="courses-grid">
        <div class="course-card"><div class="left-color"></div><span>Course Name</span></div>
        <div class="course-card"><div class="left-color"></div><span>Course Name</span></div>
        <div class="course-card"><div class="left-color"></div><span>Course Name</span></div>
        <div class="course-card"><div class="left-color"></div><span>Course Name</span></div>
      </div>
    </section>
  </main>

  <footer class="footer">
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
  </footer>
</body>
</html>
