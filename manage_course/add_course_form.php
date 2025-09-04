<?php
  session_start();

  if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['adminID'])) {
      echo "You are not authorized to access this page.";
      exit();
  }

  require('../database.php');

  $queryInstructors = 'SELECT * FROM instructors';
  $statement = $db->prepare($queryInstructors);
  $statement->execute();
  $instructors = $statement->fetchAll();
  $statement->closeCursor();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Learning Pod - Add Course</title>
  <script src="../scripts/app.js" defer></script>
  <link rel="stylesheet" href="../css/app.css"/>
</head>
<body>
  <?php include('../admin_details.php'); ?>
  <?php include('../header.php'); ?> 
    <main id="addInstructorMain">
    <h2>Add New Course</h2>
    <?php if (isset($_SESSION['error'])): ?>
      <p style="color: #C21807;"><?php echo $_SESSION['error']; ?></p>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <form action="add_course.php" method="post" enctype="multipart/form-data" id="addUpdateForm">
      <div class="form-group">
        <label for="image">Upload Image:</label>
        <input type="file" name="courseImage">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="Course Code">Course Code:</label>
          <input type="text" id="courseCode" name="courseCode" placeholder="Course Code" required>
        </div>
        <div class="form-group">
          <label for="courseName">Course Name:</label>
          <input type="text" id="courseName" name="courseName" placeholder="Course Name" required>
        </div>
      </div>

      <div class="form-group">
        <label for="courseInstructor">Course Instructor:</label>
        <select id="courseInstructor" name="courseInstructor" required>
          <option value="">Select Instructor</option>
          <?php foreach ($instructors as $instructor): ?>
            <option value="<?php echo $instructor['instructorID']; ?>">
              <?php echo htmlspecialchars($instructor['firstName'] . ' ' . $instructor['lastName']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="courseDesc">Course Description:</label>
        <textarea id="courseDesc" name="courseDesc" rows="2" cols="20" placeholder="Course Desc"></textarea>
      </div>

      <div class="form-row">
        <button type="submit" id="submit">Add Course</button>
        <button type="button" class="cancel" onclick="window.location.href='../manage_course.php';">Back to Courses</button>
      </div>
    </form>
  </main>

  <footer class="footer">
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
  </footer>
</body>
</html>
