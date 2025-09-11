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

$queryInstructors = 'SELECT * FROM instructors';
$statement = $db->prepare($queryInstructors);
$statement->execute();
$instructors = $statement->fetchAll();
$statement->closeCursor();
// echo count($instructors);
// die();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Learning Pod - Manage Instructors</title>
  <script src="scripts/app.js" defer></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="css/app.css"/>
</head>
<body data-role="<?= htmlspecialchars($role) ?>">
  <?php include('admin_details.php'); ?>
  <?php include('header.php'); ?>

  <main id="manageInstructorMain">

    <?php if (count($instructors) > 0): ?>

    <div id="overlay">
      <div id="deletePopup">
        <p>Are you sure you want to delete this instructor?</p>
        <form id="popupDeleteForm" action="manage_instructor/delete_instructor.php" method="post">
          <input type="hidden" name="instructorID" id="popupRecordID" />
          <div class="popup-buttons">
            <button type="submit" id="delete" class="confirm">Yes, Delete</button>
            <button type="button" onclick="closePopup()" id="cancel" class="cancel">No, Cancel</button>
          </div>
        </form>
      </div>
    </div> 

    <table>
      <thead>
      <tr>
        <th>Photo</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Date of Joining</th>
        <th>Email Address</th>
        <th>Phone Number</th>
        <th>Mailing Address</th>
        <th>Actions</th>
      </tr>
    </thead>
      <?php foreach ($instructors as $instructor): ?>
      <tr>
        <td><img src="<?php echo htmlspecialchars('./images/' . $instructor['imageName']); ?>" alt="Instructor Image" width="50" height="50"></td>
        <td><?php echo htmlspecialchars($instructor['firstName']); ?></td>
        <td><?php echo htmlspecialchars($instructor['lastName']); ?></td>
        <td><?php echo htmlspecialchars($instructor['doj']); ?></td>
        <td><?php echo htmlspecialchars($instructor['email']); ?></td>
        <td><?php echo htmlspecialchars($instructor['contactNumber']); ?></td>
        <td><?php echo htmlspecialchars($instructor['mailingAddress']); ?></td>
        <td>    
          <form action="manage_instructor/view_details.php" method="post" class="inline-form">
            <input type="hidden" name="instructorID" value="<?php echo $instructor['instructorID']; ?>"/>
            <button type="submit" title="View">
              <i class="fas fa-eye"></i>
            </button>
          </form> <!-- View Details Button -->
          <form action="manage_instructor/update_instructor_form.php" method="post" class="inline-form">
            <input type="hidden" name="instructorID" value="<?php echo $instructor['instructorID']; ?>" />
              <button type="submit" title="Edit">
              <i class="fas fa-edit"></i>
            </button>
          </form> <!-- Edit Button -->
          <form class="inline-form deleteForm" method="post" >
            <input type="hidden" name="instructorID" value="<?php echo $instructor['instructorID']; ?>" />
            <button type="submit" title="Delete">
              <i class="fas fa-trash-alt"></i>
              </button>
          </form> <!-- Delete Button -->
        </td>

      </tr>
      <?php endforeach; ?>
    </table>
    <?php else: ?>
      <p>No instructor records found.</p>
    <?php endif; ?>

    <p><a href="manage_instructor/add_instructor_form.php">Add New Instructor</a></p>
  </main>

  <footer class="footer">
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
  </footer>
</body>
</html>
