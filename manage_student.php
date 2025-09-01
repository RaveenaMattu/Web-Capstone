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

$queryStudents = 'SELECT * FROM students';
$statement = $db->prepare($queryStudents);
$statement->execute();
$students = $statement->fetchAll();
$statement->closeCursor();
// echo count($students);
// die();
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
  <div class="logo"><img src="images/logo.png" alt="Logo" height="100" width="100"></div>
  <nav class="nav">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_instructor.php">Manage Instructors</a>
    <a href="manage_student.php" class="active">Manage Students</a>
    <a href="manage_course.php">Manage Courses</a>
    <a href="manage_tasks.php">Tasks</a>
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

  <?php if (count($students) > 0): ?>

<div id="overlay">
  <div id="deletePopup">
    <p>Are you sure you want to delete this student?</p>
    <form id="popupDeleteForm" action="manage_student/delete_student.php" method="post">
      <input type="hidden" name="studentID" id="popupRecordID" />
      <div class="popup-buttons">
        <button type="submit" id="delete" class="confirm">Yes, Delete</button>
        <button type="button" onclick="closePopup()" id="cancel" class="cancel">No, Cancel</button>
      </div>
    </form>
  </div>
</div> 
<!-- <div id="overlay">
  <div id="deletePopup">

  </div> 
 </div>-->

 
  <table>
    <tr>
      <th>Photo</th>
      <th>First Name</th>
      <th>Last Name</th>
      <th>Status</th>
      <th>Date of Birth</th>
      <th>Email Address</th>
      <th>Phone Number</th>
      <th>Mailing Address</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($students as $student): ?>
    <tr>
      <td><img src="<?php echo htmlspecialchars('./images/' . $student['imageName']); ?>" alt="Student Image" width="50" height="50"></td>
      <td><?php echo htmlspecialchars($student['firstName']); ?></td>
      <td><?php echo htmlspecialchars($student['lastName']); ?></td>
      <td>
        <?php
          $status = $student['status'] ?? 'Unknown';
          $statusClass = '';
          switch ($status) {
            case 'Active':
              $statusClass = 'status-active';
              break;
            case 'Inactive':
              $statusClass = 'status-inactive';
              break;
            case 'Graduated':
              $statusClass = 'status-graduated';
              break;
            default:
              $statusClass = 'status-unknown';
          }
        ?>
        <p><span class="<?php echo $statusClass; ?>"><?php echo $status; ?></span></p>
      </td>
      <td><?php echo htmlspecialchars($student['dob']); ?></td>
      <td><?php echo htmlspecialchars($student['email']); ?></td>
      <td><?php echo htmlspecialchars($student['contactNumber']); ?></td>
      <td><?php echo htmlspecialchars($student['mailingAddress']); ?></td>
      <td>    
        <form action="manage_student/view_details.php" method="post" class="inline-form">
          <input type="hidden" name="studentID" value="<?php echo $student['studentID']; ?>"/>
          <button type="submit" title="View">
            <i class="fas fa-eye"></i>
          </button>
        </form> <!-- View Details Button -->
        <form action="manage_student/update_student_form.php" method="post" class="inline-form">
          <input type="hidden" name="studentID" value="<?php echo $student['studentID']; ?>" />
            <button type="submit" title="Edit">
            <i class="fas fa-edit"></i>
          </button>
        </form> <!-- Edit Button -->
        <form class="inline-form deleteForm" method="post" >
          <input type="hidden" name="studentID" value="<?php echo $student['studentID']; ?>" />
          <button type="submit" title="Delete">
            <i class="fas fa-trash-alt"></i>
            </button>
        </form> <!-- Delete Button -->
      </td>

    </tr>
    <?php endforeach; ?>
  </table>
  <?php else: ?>
    <p>No student records found.</p>
  <?php endif; ?>

  <p><a href="manage_student/add_student_form.php">Add New Student</a></p>
</main>

<footer class="footer">
  © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
</footer>
</body>
</html>
