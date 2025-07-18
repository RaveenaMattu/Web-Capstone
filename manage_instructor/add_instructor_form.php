<?php
session_start();

if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['adminID'])) {
    echo "You are not authorized to access this page.";
    exit();
}

require('../database.php');

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
  <title>Learning Pod - Add Instructor</title>
  <script src="../scripts/app.js" defer></script>
  <link rel="stylesheet" href="../css/app.css"/>
</head>
<body>
  <?php include('../admin_details.php'); ?>
  <header class="header">
    <div class="logo"></div>
    <nav class="nav">
      <a href="../admin_dashboard.php">Dashboard</a>
      <a href="../manage_instructor.php" class="active">Manage Instructors</a>
      <a href="#">Manage Students</a>
      <a href="#">Manage Courses</a>
      <a href="#">Tasks</a>
    </nav>
    <div class="user-info">Hi, <?php echo $_SESSION['fullName']; ?>
      <div class="profile-wrapper">
          <div class="profile-circle">
            <img src="<?php echo htmlspecialchars('../images/' . $imageFile); ?>" width="40" height="40" alt="Profile Picture" id="profilePicture">
          </div>
          <div class="logOutBox">
            <a href="#" onclick="openUpdateAdmin();">Update Profile</a>
            <a href="../admin_logout.php">Log Out</a>
          </div>
      </div>      
    </div>  
  </header> 
  <main id="addInstructorMain">
    <h2>Add New Instructor</h2>
    <?php if (isset($_SESSION['error'])): ?>
      <p style="color: #C21807;"><?php echo $_SESSION['error']; ?></p>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <form action="add_instructor.php" method="post" enctype="multipart/form-data" id="instructorForm">
      <div class="form-group">
        <label for="image">Upload Image:</label>
        <input type="file" name="image">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="firstName">First Name:</label>
          <input type="text" id="firstName" name="firstName" placeholder="First Name" required>
        </div>
        <div class="form-group">
          <label for="lastName">Last Name:</label>
          <input type="text" id="lastName" name="lastName" placeholder="Last Name" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="doj">Date of Joining:</label>
          <input type="date" id="doj" name="doj" placeholder="Date of Joining" required>
        </div>
        <div class="form-group">
          <label for="phoneNumber">Phone Number:</label>
          <input type="tel" id="phoneNumber" name="phoneNumber" placeholder="Phone Number">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="emailAddress">Email Address:</label>
          <input type="email" id="emailAddress" name="emailAddress" placeholder="Email Address" required>
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" id="password" name="password" placeholder="Password" required>
        </div>
      </div>

      <div class="form-group">
        <label for="mailingAddress">Mailing Address:</label>
        <textarea id="mailingAddress" name="mailingAddress" rows="2" cols="20" placeholder="Mailing Address"></textarea>
      </div>

      <div class="form-row">
        <button type="submit" id="submit">Add Instructor</button>
        <button type="button" class="cancel" onclick="window.location.href='../admin_dashboard.php';">Back to dashboard</button>
      </div>
    </form>
  </main>

  <footer class="footer">
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
  </footer>
</body>
</html>
