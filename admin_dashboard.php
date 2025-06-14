<?php

 require_once('database.php');
 $username = $_SESSION['fullName'];

$queryAdmin = 'SELECT * FROM admins WHERE username = :username';
$statement1 = $db->prepare($queryAdmin);
$statement1->bindValue(':username', $username);
$statement1->execute();
$admin = $statement1->fetch();
$statement1->closeCursor();
$imageFile = (!empty($admin['imageName'])) ? $admin['imageName'] : 'placeholder.jpg';

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
<body>
  <div id="updateAdmin">
    <div class="updateAdminBox">
      <h2>Admin Profile</h2>
      <div class="adminProfile">
        <div class="adminImage">        
          <img src="images/<?php echo htmlspecialchars($imageFile); ?>" alt="Admin Photo" width="200" height="200" id="adminImage">
        </div>
        <div class="adminDetails">
          <form action="update_admin.php" method="post" enctype="multipart/form-data">

            <input type="hidden" name="adminID" value="<?php echo $admin['adminID']; ?>">

            <label>Photo:</label>
            <input type="file" name="image"><br>

            <label for="firstName">Username:</label>
            <input type="text" id="username" name="username" required value="<?php echo $admin['username']; ?>"><br>
            
            <label for="lastName">Email:</label>
            <input type="email" id="email" name="emailAddress" required value="<?php echo $admin['emailAddress']; ?>"><br>
            <button type="submit">Update Profile</button>
            <button onclick="closeUpdateAdmin();" id="cancel">Cancel</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <header class="header">
    <div class="logo"></div>
    <nav class="nav">
      <a href="#" class="active">Dashboard</a>
      <a href="#">Manage Instructors</a>
      <a href="#">Manage admins</a>
      <a href="#">Manage Courses</a>
      <a href="#">Tasks</a>
    </nav>
    <div class="user-info">Hi, <?php echo $_SESSION['fullName']; ?>
      <div class="profile-wrapper">
          <div class="profile-circle">
          <img src="<?php echo htmlspecialchars('./images/' . $imageFile); ?>" width="40" height="40" style="border: 50%;" alt="Profile Picture" id="profilePicture">
        </div>
        <div class="logOutBox">
        <a href="#" onclick="openUpdateAdmin();">Update Profile</a>
        <a href="admin_logout.php">Log Out</a>
      </div>
      </div>      
    </div>  
  </header>

  <main class="main-content">
    <section class="stats">
      <div class="stat-box"><p>Total Courses</p><h2>4</h2></div>
      <div class="stat-box"><p>Total admins</p><h2>12</h2></div>
      <div class="stat-box"><p>Total Instructors</p><h2>5</h2></div>
      <div class="stat-box"><p>Pending Tasks</p><h2>8</h2></div>
    </section>

    <section class="tasks-box">
      <h4>Pending Tasks <a href="#">View All >></a></h4>
      <ul class="task-list">
        <li>- New course requests.</li>
        <li>- Add 3 new instructors.</li>
        <li>- Review admins performance report.</li>
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
