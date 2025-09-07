<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require('database.php');

if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['adminID'])) {
    echo "You are not authorized to access this page.";
    exit();
}

$queryAdmin = 'SELECT * FROM admins WHERE adminID = :adminID';
$statement = $db->prepare($queryAdmin);
$statement->bindValue(':adminID', $_SESSION['adminID']);
$statement->execute();
$admin = $statement->fetch();
$statement->closeCursor();

$imageFile = (!empty($admin['imageName'])) ? $admin['imageName'] : 'placeholder.jpg';

$queryCourses = 'SELECT * FROM courses';
$statement = $db->prepare($queryCourses);
$statement->execute();
$courses= $statement->fetchAll();
$statement->closeCursor();
$courseImage = (!empty($courses['imageName'])) ? $courses['imageName'] : 'placeholder.jpg';

$queryCourses = "
  SELECT c.*, i.firstName AS instructorFirstName, i.lastName AS instructorLastName
  FROM courses c
  LEFT JOIN instructors i ON c.instructorID = i.instructorID
";
$statement = $db->prepare($queryCourses);
$statement->execute();
$courses = $statement->fetchAll();
$statement->closeCursor();
// echo count($instructors);
// die();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Learning Pod - Manage Course</title>
  <script src="scripts/app.js" defer></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="css/app.css"/>
</head>
<body data-role="<?= htmlspecialchars($role) ?>">
  <?php include('admin_details.php'); ?>
  <?php include('header.php'); ?>

  <main id="manageInstructorMain">

    <?php if (count($courses) > 0): ?>

  <div id="overlay" style="display:none;">
    <div id="deletePopup">
      <p>Are you sure you want to delete this course?</p>
      <form id="popupDeleteForm" action="manage_course/delete_course.php" method="post">
        <input type="hidden" name="courseID" id="popupRecordID" />
        <div class="popup-buttons">
          <button type="submit" id="delete" class="confirm">Yes, Delete</button>
          <button type="button" onclick="closePopup()" id="cancel" class="cancel">No, Cancel</button>
        </div>
      </form>
    </div>
  </div>

    <table>
      <tr>
        <th>Image</th>
        <th>Course Code</th>
        <th>Course Name</th>
        <!-- <th>Description</th> -->
        <th>Instructor</th>
        <th>Actions</th>
      </tr>
      <?php foreach ($courses as $course): ?>
        <tr>
          <td>
            <?php if (!empty($course['imageName'])): ?>
              <img src="<?php echo htmlspecialchars('./images/' . $course['imageName']); ?>" alt="Course Image" width="40" />
            <?php else: ?>
              <span>No Image</span>
            <?php endif; ?>
          </td>
          <td><?php echo htmlspecialchars($course['courseCode']); ?></td>
          <td><?php echo htmlspecialchars($course['courseName']); ?></td>
          <!-- <td><?php echo nl2br(htmlspecialchars($course['description'])); ?></td> -->
          <td>
            <?php 
              if (!empty($course['instructorFirstName']) || !empty($course['instructorLastName'])) {
                echo htmlspecialchars($course['instructorFirstName'] . ' ' . $course['instructorLastName']);
              } else {
                echo "Unassigned";
              }
            ?>
          </td>
          <td>    
          <form action="manage_course/view_details.php" method="post" class="inline-form">
            <input type="hidden" name="courseID" value="<?php echo $course['courseID']; ?>"/>
            <button type="submit" title="View">
              <i class="fas fa-eye"></i>
            </button>
          </form> <!-- View Details Button -->
          <form action="manage_course/update_course_form.php" method="post" class="inline-form">
            <input type="hidden" name="courseID" value="<?php echo $course['courseID']; ?>" />
              <button type="submit" title="Edit">
              <i class="fas fa-edit"></i>
            </button>
          </form> <!-- Edit Button -->
          <form class="inline-form deleteForm" method="post" >
          <input type="hidden" name="courseID" value="<?php echo $course['courseID']; ?>" />
          <button type="submit" title="Delete">
            <i class="fas fa-trash-alt"></i>
          </button>
        </form>  <!-- Delete Button -->
        </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
      <p>No Course records found.</p>
    <?php endif; ?>

    <p><a href="manage_course/add_course_form.php">Add New Course</a></p>
  </main>

  <footer class="footer">
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
  </footer>
</body>
</html>
