<?php
require_once('../database.php');

if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['adminID'])) {
  echo "You are not authorized to access this page.";
  exit();
}


// Get studentID from POST
$studentID = filter_input(INPUT_POST, 'studentID', FILTER_VALIDATE_INT);
if (!$studentID) {
    echo $studentID;
    exit();
}

// Fetch student details
$query = 'SELECT * FROM students WHERE studentID = :studentID';
$statement = $db->prepare($query);
$statement->bindValue(':studentID', $studentID);
$statement->execute();
$student = $statement->fetch();
$statement->closeCursor();

if ($student) {
  $firstName = htmlspecialchars($student['firstName']);
  $lastName = htmlspecialchars($student['lastName']);
  $dob = htmlspecialchars($student['dob']);
  $phoneNumber = htmlspecialchars($student['contactNumber']);
  $emailAddress = htmlspecialchars($student['email']);
  $mailingAddress = htmlspecialchars($student['mailingAddress']);
  $status = htmlspecialchars($student['status']);
  $imageName = htmlspecialchars($student['imageName']);
} else {
  echo "Student not found.";
  exit();
}

// Fetch admin info
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
  <title>Learning Pod - View Student</title>
  <link rel="stylesheet" href="../css/app.css"/>
  <script src="../scripts/app.js" defer></script>
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
        <img src="<?php echo htmlspecialchars('../images/' . $imageFile); ?>" width="40" height="40" alt="Profile Picture" id="profilePicture" />
      </div>
      <div class="logOutBox">
        <a href="#" onclick="openUpdateAdmin();">Update Profile</a>
        <a href="../admin_logout.php">Log Out</a>
      </div>
    </div>
  </div>
</header>

<main id="viewDetailsMain">
  <div id="container">
    <h1>Student Details</h1>
    <h2><?php echo $firstName . ' ' . $lastName; ?></h2>
    <img src="<?php echo htmlspecialchars('../images/' . $imageName); ?>" alt="Student Image" width="150" height="150">
    <p><strong>Date of Birth: &nbsp;</strong> <?php echo $dob; ?></p>
    <p><strong>Status: &nbsp;</strong> <?php echo $status; ?></p>
    <p><strong>Phone Number: &nbsp;</strong> <?php echo $phoneNumber; ?></p>
    <p><strong>Email Address: &nbsp;</strong> <?php echo $emailAddress; ?></p>
    <p><strong>Mailing Address: &nbsp;</strong> <?php echo $mailingAddress; ?></p>
    <div class="form-row">
      <button type="button" class="cancel" onclick="window.location.href='../admin_dashboard.php';">Back to Dashboard</button>
      <button type="button" class="cancel" onclick="window.location.href='../manage_student.php';">Back to Student List</button>
    </div>
  </div>
</main>
</body>
</html>
