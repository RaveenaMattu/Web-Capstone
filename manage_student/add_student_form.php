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
  <title>Learning Pod - Add Student</title>
  <script src="../scripts/app.js" defer></script>
  <link rel="stylesheet" href="../css/app.css"/>
</head>
<body>
  <?php include('../admin_details.php'); ?>
  <header class="header">
    <div class="logo"></div>
    <nav class="nav">
      <a href="../admin_dashboard.php">Dashboard</a>
      <a href="../manage_instructor.php">Manage Instructors</a>
      <a href="../manage_student.php" class="active">Manage Students</a>
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
    <h2>Add New Student</h2>
    <?php if (isset($_SESSION['error'])): ?>
      <p style="color: #C21807;"><?php echo $_SESSION['error']; ?></p>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <form action="add_student.php" method="post" enctype="multipart/form-data" id="addUpdateForm">
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
          <label for="dob">Date of Birth:</label>
          <input type="date" id="dob" name="dob" required>
        </div>
        <div class="form-group">
          <label for="contactNumber">Contact Number:</label>
          <input type="tel" id="contactNumber" name="contactNumber" maxlength="10" placeholder="Contact Number" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="email">Email Address:</label>
          <input type="email" id="email" name="email" placeholder="Email Address" required>
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" id="password" name="password" placeholder="Password" required>
        </div>
      </div>

      <div class="form-group">
        <label for="status">Status:</label>
        <select id="status" name="status" required>
          <option value="">-- Select Status --</option>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
          <option value="Graduated">Graduated</option>
        </select>
      </div>

      <div class="form-group">
        <label for="mailingAddress">Mailing Address:</label>
        <textarea id="mailingAddress" name="mailingAddress" rows="2" cols="20" placeholder="Mailing Address" required></textarea>
      </div>

      <div class="form-row">
        <button type="submit" id="submit">Add Student</button>
        <button type="button" class="cancel" onclick="window.location.href='../manage_student.php';">Back to Student List</button>
      </div>
    </form>
  </main>

  <footer class="footer">
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
  </footer>
</body>
</html>
