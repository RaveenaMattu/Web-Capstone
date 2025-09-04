<?php
session_start();

if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['adminID'])) {
    echo "You are not authorized to access this page.";
    exit();
}

require('../database.php');

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
  <?php include('../header.php'); ?>  
  <main id="addInstructorMain">
    <h2>Add New Instructor</h2>
    <?php if (isset($_SESSION['error'])): ?>
      <p style="color: #C21807;"><?php echo $_SESSION['error']; ?></p>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <form action="add_instructor.php" method="post" enctype="multipart/form-data" id="addUpdateForm">
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
        <button type="button" class="cancel" onclick="window.location.href='../manage_instructor.php';">Back to Instructor List</button>
      </div>
    </form>
  </main>

  <footer class="footer">
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
  </footer>
</body>
</html>
