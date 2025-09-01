<?php
session_start();
require_once('database.php');

if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== "Instructor") {
    header("Location: login.php");
    exit();
}

$instructorID = $_SESSION['userID'];

// Fetch courses for this instructor
$queryCourses = 'SELECT * FROM courses WHERE instructorID = :id';
$statement = $db->prepare($queryCourses);
$statement->bindValue(':id', $instructorID);
$statement->execute();
$courses = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement->closeCursor();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>My Courses</title>
  <link rel="stylesheet" href="css/app.css"/>
</head>
<body>
<header>
  <h2>My Courses</h2>
  <a href="instructor_dashboard.php">Back to Dashboard</a>
</header>

<main>
  <table class="table">
    <thead>
      <tr>
        <th>Course ID</th>
        <th>Course Name</th>
        <th>Description</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($courses) > 0): ?>
        <?php foreach ($courses as $course): ?>
          <tr>
            <td><?php echo htmlspecialchars($course['courseID']); ?></td>
            <td><?php echo htmlspecialchars($course['courseName']); ?></td>
            <td><?php echo htmlspecialchars($course['courseDescription']); ?></td>
            <td>
              <a href="upload_materials.php?courseID=<?php echo $course['courseID']; ?>">Add Materials</a>
              <a href="edit_course.php?courseID=<?php echo $course['courseID']; ?>">Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4">No courses assigned.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</main>
</body>
</html>
