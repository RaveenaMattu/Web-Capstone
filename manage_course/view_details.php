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
  <?php include('../header.php'); ?>         
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
