<?php
  session_start();

  if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['adminID'])) {
    echo "You are not authorized to access this page.";
    exit();
  }
  require_once('../database.php');
  
  $instructorID = filter_input(INPUT_POST, 'instructorID', FILTER_VALIDATE_INT);
  if (!$instructorID) {
      echo "Invalid instructor ID.";
      exit();
  }

  $query = 'SELECT * FROM instructors WHERE instructorID = :instructorID';
  $statement = $db->prepare($query);
  $statement->bindValue(':instructorID', $instructorID);
  $statement->execute();
  $instructor = $statement->fetch();
  $statement->closeCursor();


  if ($instructor) {
    $firstName = htmlspecialchars($instructor['firstName']);
    $lastName = htmlspecialchars($instructor['lastName']);
    $doj = htmlspecialchars($instructor['doj']);
    $phoneNumber = htmlspecialchars($instructor['contactNumber']);
    $emailAddress = htmlspecialchars($instructor['email']);
    $mailingAddress = htmlspecialchars($instructor['mailingAddress']);
    $imageName = htmlspecialchars($instructor['imageName']);
  } else {
    echo "Instructor not found.";
    exit();
  }

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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Learning Pod - View Details</title>
  <link rel="stylesheet" href="../css/app.css">
  <script src="../scripts/app.js" defer></script>
</head>
<body>
  <?php include('../admin_details.php'); ?>
  <header class="header">
    <div class="logo"><img src="../images/logo.png" alt="Logo" height="100" width="100"></div>

    <nav class="nav">
      <a href="../admin_dashboard.php">Dashboard</a>
      <a href="../manage_instructor.php" class="active">Manage Instructors</a>
      <a href="../manage_student.php">Manage Students</a>
      <a href="../manage_course.php">Manage Courses</a>
      <a href="../manage_task.php">Tasks</a>
    </nav>
    <div class="user-info">Hi, <?php echo $_SESSION['fullName']; ?>
      <div class="profile-wrapper">
          <div class="profile-circle">
            <img src="<?php echo htmlspecialchars('../images/' . $imageFile); ?>" width="40" height="40" alt="Profile Picture" id="profilePicture">
          </div>
          <div class="logOutBox">
            <a href="#" onclick="openUpdateAdmin();">Update Profile</a>
            <a href="admin_logout.php" style="color:#C21807;">Log Out</a>
          </div>
      </div>      
    </div>  
  </header> 
  <main id="viewDetailsMain">
    <div id="container">
      <h1>Instructor Details</h1>
      <h2><?php echo $firstName . ' ' . $lastName; ?></h2>
      <img src="<?php echo htmlspecialchars('../images/' . $imageName); ?>" alt="Instructor Image" width="150" height="150">
      <p><strong>Date of Joining: &nbsp;</strong> <?php echo $doj; ?></p>
      <p><strong>Phone Number: &nbsp;</strong> <?php echo $phoneNumber; ?></p>
      <p><strong>Email Address: &nbsp;</strong> <?php echo $emailAddress; ?></p>
      <p><strong>Mailing Address: &nbsp;</strong> <?php echo $mailingAddress; ?></p>
      <div class="form-row">
        <button type="button" class="cancel" onclick="window.location.href='../admin_dashboard.php';">Back to dashboard</button>
        <button type="button" class="cancel" onclick="window.location.href='../manage_instructor.php';">Back to Instructor List</button>
      </div>
    </div>
    
  </main>
</body>
</html>