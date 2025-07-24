<?php
session_start();

if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['adminID'])) {
    echo "You are not authorized to access this page.";
    exit();
}

require('../database.php');

// Get instructorID from POST
$instructorID = filter_input(INPUT_POST, 'instructorID', FILTER_VALIDATE_INT);
if (!$instructorID) {
    echo "Invalid instructor ID.";
    exit();
}

// Fetch instructor details
$query = 'SELECT * FROM instructors WHERE instructorID = :instructorID';
$statement = $db->prepare($query);
$statement->bindValue(':instructorID', $instructorID);
$statement->execute();
$instructor = $statement->fetch();
$statement->closeCursor();

if (!$instructor) {
    echo "Instructor not found.";
    exit();
}

$imageFile = (!empty($instructor['imageName'])) ? $instructor['imageName'] : 'placeholder.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Learning Pod - Update Instructor</title>
  <link rel="stylesheet" href="../css/app.css" />
</head>
<body>
<?php include('../admin_details.php'); ?>

<header class="header">
  <div class="logo"></div>
  <nav class="nav">
    <a href="../admin_dashboard.php">Dashboard</a>
    <a href="../manage_instructor.php" class="active">Manage Instructors</a>
    <a href="../manage_student.php">Manage Students</a>
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
  <h2>Update Instructor</h2>
  <?php if (isset($_SESSION['error'])): ?>
    <p style="color: #C21807;"><?php echo $_SESSION['error']; ?></p>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <form action="update_instructor.php" method="post" enctype="multipart/form-data" id="addUpdateForm">
    <input type="hidden" name="instructorID" value="<?php echo $instructorID; ?>" />

    <div class="form-group">
      <label for="image">Upload New Image (optional):</label>
      <input type="file" name="image">
      <p>Current Image:</p>
      <img src="<?php echo htmlspecialchars('../images/' . $imageFile); ?>" width="80" height="80" alt="Instructor Image">
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="firstName">First Name:</label>
        <input type="text" name="firstName" value="<?php echo htmlspecialchars($instructor['firstName']); ?>" required>
      </div>
      <div class="form-group">
        <label for="lastName">Last Name:</label>
        <input type="text" name="lastName" value="<?php echo htmlspecialchars($instructor['lastName']); ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="doj">Date of Joining:</label>
        <input type="date" name="doj" value="<?php echo htmlspecialchars($instructor['doj']); ?>" required>
      </div>
      <div class="form-group">
        <label for="phoneNumber">Phone Number:</label>
        <input type="tel" name="phoneNumber" value="<?php echo htmlspecialchars($instructor['contactNumber']); ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="emailAddress">Email Address:</label>
        <input type="email" name="emailAddress" value="<?php echo htmlspecialchars($instructor['email']); ?>" required>
      </div>
      <div class="form-group">
        <label for="password">New Password (leave blank to keep current):</label>
        <input type="password" name="password" placeholder="New Password">
      </div>
    </div>

    <div class="form-group">
      <label for="mailingAddress">Mailing Address:</label>
      <textarea name="mailingAddress" rows="2"><?php echo htmlspecialchars($instructor['mailingAddress']); ?></textarea>
    </div>

    <div class="form-row">
      <button type="submit" id="submit">Update Instructor</button>
      <button type="button" class="cancel" onclick="window.location.href='../manage_instructor.php';">Cancel</button>
    </div>
  </form>
</main>

<footer class="footer">
  © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
</footer>
</body>
</html>
