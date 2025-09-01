<?php
session_start();

if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['adminID'])) {
    echo "You are not authorized to access this page.";
    exit();
}

require_once('../database.php');

// Get course ID
$courseID = filter_input(INPUT_POST, 'courseID', FILTER_VALIDATE_INT);
if (!$courseID) {
    echo "Invalid course ID.";
    exit();
}

// Fetch course details along with instructor name using LEFT JOIN
$query = "
    SELECT c.*, i.firstName AS instructorFirstName, i.lastName AS instructorLastName
    FROM courses AS c
    LEFT JOIN instructors AS i ON c.instructorID = i.instructorID
    WHERE c.courseID = :courseID
";
$statement = $db->prepare($query);
$statement->bindValue(':courseID', $courseID);
$statement->execute();
$course = $statement->fetch();
$statement->closeCursor();

if (!$course) {
    echo "Course not found.";
    exit();
}

// Assign variables
$courseName = htmlspecialchars($course['courseName']);
$courseDesc = htmlspecialchars($course['description']);
$courseCode = htmlspecialchars($course['courseCode']);
$imageName = htmlspecialchars($course['imageName']);
$instructorName = htmlspecialchars($course['instructorFirstName'] . ' ' . $course['instructorLastName']);

// Fetch admin details for header
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
  <title>Learning Pod - View Course Details</title>
  <link rel="stylesheet" href="../css/app.css">
  <script src="../scripts/app.js" defer></script>
</head>
<body>
  <?php include('../admin_details.php'); ?>
  <header class="header">
    <div class="logo"><img src="../images/logo.png" alt="Logo" height="100" width="100"></div>
    <nav class="nav">
      <a href="../admin_dashboard.php">Dashboard</a>
      <a href="../manage_instructor.php">Manage Instructors</a>
      <a href="../manage_students.php">Manage Students</a>
      <a href="../manage_courses.php" class="active">Manage Courses</a>
      <a href="../manage_tasks.php">Tasks</a>
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

  <main id="viewDetailsMain">
    <div id="container">
      <h1>Course Details</h1>
      <img src="<?php echo '../images/' . $imageName; ?>" alt="Course Image" width="100" height="100" style="object-fit: contain; border: none; border-radius: 0;">
      <p><strong>Course Code: </strong><?php echo $courseCode; ?></p>
      <p><strong>Course Name: </strong><?php echo $courseName; ?></p>
      <p><strong>Course Description: </strong><?php echo $courseDesc; ?></p>
      <p><strong>Assigned Instructor: </strong><?php echo $instructorName ?: 'Unassigned'; ?></p>

      <div class="form-row">
        <button type="button" class="cancel" onclick="window.location.href='../admin_dashboard.php';">Back to Dashboard</button>
        <button type="button" class="cancel" onclick="window.location.href='../manage_course.php';">Back to Course List</button>
      </div>
    </div>
  </main>
</body>
</html>
