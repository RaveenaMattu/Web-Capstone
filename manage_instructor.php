<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Learning Pod - Manage Instructor</title>
  <script src="scripts/app.js" defer></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="css/app.css"/>
</head>
<body>
<?php include('admin_details.php'); ?>
<header class="header">
  <div class="logo"></div>
  <nav class="nav">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_instructor.php" class="active">Manage Instructors</a>
    <a href="#">Manage Students</a>
    <a href="#">Manage Courses</a>
    <a href="#">Tasks</a>
  </nav>
  <div class="user-info">Hi, <?php echo $_SESSION['fullName']; ?>
    <div class="profile-wrapper">
      <div class="profile-circle">
        <img src="<?php echo htmlspecialchars('./images/' . $imageFile); ?>" width="40" height="40" alt="Profile Picture" id="profilePicture">
      </div>
      <div class="logOutBox">
        <a href="#" onclick="openUpdateAdmin();">Update Profile</a>
        <a href="admin_logout.php">Log Out</a>
      </div>
    </div>
  </div>
</header>

<main id="manageInstructorMain">

  <?php if (count($instructors) > 0): ?>
    <div id="overlay"></div>

  <!-- Delete Confirmation Popup -->
  <div id="deletePopup">
    <p id="deleteConfirm">Are you sure you want to delete this student?</p>
    <form id="popupDeleteForm" action="delete_student.php" method="post">
      <input type="hidden" name="studentID" id="popupStudentID" />
      <input type="submit" value="Yes, Delete" id="delete" />
      <button type="button" onclick="closePopup()" id="button">No, Cancel</button>
    </form>
  </div>
    <table>
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
          <form action="view_details.php" method="post" class="inline-form">
            <input type="hidden" name="studentID" value="<?php echo $instructor['instructorID']; ?>"/>
            <button type="submit" title="View" style="background:none; border:none; cursor:pointer;">
      <i class="fas fa-eye"></i>
    </button>
          </form>

          <form action="update_instructor_form.php" method="post" class="inline-form">
            <input type="hidden" name="studentID" value="<?php echo $instructor['instructorID']; ?>" />
             <button type="submit" title="Edit" style="background:none; border:none; cursor:pointer;">
      <i class="fas fa-edit"></i>
    </button>
          </form>

          <form class="inline-form" method="post">
            <input type="hidden" name="studentID" value="<?php echo $instructor['instructorID']; ?>" />
            <button type="submit" title="Delete" style="background:none; border:none; cursor:pointer;">
      <i class="fas fa-trash-alt"></i>
    </button>
          </form>
        </td>

      </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p>No instructor records found.</p>
  <?php endif; ?>

  <p><a href="add_instructor_form.php">Add New Instructor</a></p>
</main>

<footer class="footer">
  © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
</footer>
</body>
</html>
