<?php
session_start();
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
  <?php include('../header.php'); ?>
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
