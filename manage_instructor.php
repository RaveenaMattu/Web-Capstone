<?php
session_start();

if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['adminID'])) {
    echo "You are not authorized to access this page.";
    exit();
}

require('database.php');

$queryAdmin = 'SELECT * FROM admins WHERE adminID = :adminID';
$statement = $db->prepare($queryAdmin);
$statement->bindValue(':adminID', $_SESSION['adminID']);
$statement->execute();
$admin = $statement->fetch();
$statement->closeCursor();

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
  <?php include('admin_details.php'); ?>
  <header class="header">
    <div class="logo"></div>
    <nav class="nav">
      <a href="#">Dashboard</a>
      <a href="manage_instructor.php" class="active">Manage Instructors</a>
      <a href="#">Manage Students</a>
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
  <main id="manageInstructorMain">
    <table>
    <tr>
      <th>Photo</th>
      <th>First Name</th>
      <th>Last Name</th>
      <th>Department</th>
      <th>Date of Joining</th>
      <th>Email Address</th>
      <th>Phone Number</th>
      <th>Mailing Address</th>
      <th>Status</th>
      <th>Actions</th>
      <!-- <th>Delete</th> -->
    </tr>
    </table>
    <p><a href="add_instructor_form.php">Add New Instructor</a></p>
  </main>

  <footer class="footer">
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
  </footer>
</body>
</html>

