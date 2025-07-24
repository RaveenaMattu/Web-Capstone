<?php

  session_start();

  if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['adminID'])) {
      echo "You are not authorized to access this page.";
      exit();
  }

  require('../database.php');

  // Get studentID from URL
  $studentID = filter_input(INPUT_POST, 'studentID', FILTER_VALIDATE_INT);
  if (!isset($studentID)) {
    echo "Invalid access.";
    exit();
  }

  // Get Admin Info
  $queryAdmin = 'SELECT * FROM admins WHERE adminID = :adminID';
  $statement = $db->prepare($queryAdmin);
  $statement->bindValue(':adminID', $_SESSION['adminID']);
  $statement->execute();
  $admin = $statement->fetch();
  $statement->closeCursor();
  $adminImage = (!empty($admin['imageName'])) ? $admin['imageName'] : 'placeholder.jpg';


  // Get Student Info
  $queryStudent = 'SELECT * FROM students WHERE studentID = :studentID';
  $statement = $db->prepare($queryStudent);
  $statement->bindValue(':studentID', $studentID);
  $statement->execute();
  $student = $statement->fetch();
  $statement->closeCursor();
  $imageFile = (!empty($student['imageName'])) ? $student['imageName'] : 'placeholder.jpg';


  if (!$student) {
      echo "Student not found.";
      exit();
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Learning Pod - Update Student</title>
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
  <div class="user-info">Hi, <?php echo htmlspecialchars($_SESSION['fullName']); ?>
    <div class="profile-wrapper">
      <div class="profile-circle">
        <img src="<?php echo htmlspecialchars('../images/' . $adminImage); ?>" width="40" height="40" alt="Profile Picture" id="profilePicture">
      </div>
      <div class="logOutBox">
        <a href="#" onclick="openUpdateAdmin();">Update Profile</a>
        <a href="../admin_logout.php">Log Out</a>
      </div>
    </div>      
  </div>  
</header>

<main id="addInstructorMain">
  <h2>Update Student</h2>

  <?php if (isset($_SESSION['error'])): ?>
    <p style="color: #C21807;"><?php echo $_SESSION['error']; ?></p>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <form action="update_student.php" method="post" enctype="multipart/form-data" id="addUpdateForm">
    <input type="hidden" name="studentID" value="<?php echo $studentID; ?>">

    <div class="form-group">
      <label for="image">Upload New Image (optional):</label>
      <input type="file" name="image">
      <p>Current Image:</p>
      <img src="<?php echo htmlspecialchars('../images/' . $imageFile); ?>" width="80" height="80" alt="Student Image">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="firstName">First Name:</label>
        <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($student['firstName']); ?>" required>
      </div>
      <div class="form-group">
        <label for="lastName">Last Name:</label>
        <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($student['lastName']); ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="dob">Date of Birth:</label>
        <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($student['dob']); ?>" required>
      </div>
      <div class="form-group">
        <label for="contactNumber">Contact Number:</label>
        <input type="tel" id="contactNumber" name="contactNumber" maxlength="10" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($student['contactNumber']); ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
      </div>
      <div class="form-group">
        <label for="password">Password (Leave blank to keep current):</label>
        <input type="password" id="password" name="password" placeholder="New Password">
      </div>
    </div>

    <div class="form-group">
      <label for="status">Status:</label>
      <select id="status" name="status" required>
        <option value="Active" <?php if ($student['status'] === 'Active') echo 'selected'; ?>>Active</option>
        <option value="Inactive" <?php if ($student['status'] === 'Inactive') echo 'selected'; ?>>Inactive</option>
        <option value="Graduated" <?php if ($student['status'] === 'Graduated') echo 'selected'; ?>>Graduated</option>
      </select>
    </div>

    <div class="form-group">
      <label for="mailingAddress">Mailing Address:</label>
      <textarea id="mailingAddress" name="mailingAddress" rows="2" cols="20" required><?php echo htmlspecialchars($student['mailingAddress']); ?></textarea>
    </div>

    <div class="form-row">
      <button type="submit" id="submit">Update Student</button>
      <button type="button" class="cancel" onclick="window.location.href='../manage_student.php';">Back to Student List</button>
    </div>
  </form>
</main>

<footer class="footer">
  © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
</footer>
</body>
</html>
