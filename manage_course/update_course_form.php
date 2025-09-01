<?php
session_start();
if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['adminID'])) {
    echo "You are not authorized to access this page.";
    exit();
}

require('../database.php');

// Get courseID from POST
$courseID = filter_input(INPUT_POST, 'courseID', FILTER_VALIDATE_INT);
if (!$courseID) {
    echo "Invalid course ID.";
    exit();
}

// Fetch course details
$query = 'SELECT * FROM courses WHERE courseID = :courseID';
$statement = $db->prepare($query);
$statement->bindValue(':courseID', $courseID);
$statement->execute();
$course = $statement->fetch();
$statement->closeCursor();

if (!$course) {
    echo "Course not found.";
    exit();
}

// Fetch all instructors
$queryInstructors = 'SELECT * FROM instructors';
$statement = $db->prepare($queryInstructors);
$statement->execute();
$instructors = $statement->fetchAll();
$statement->closeCursor();

$imageFile = !empty($course['imageName']) ? $course['imageName'] : 'placeholder.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Learning Pod - Update Course</title>
<link rel="stylesheet" href="../css/app.css" />
</head>
<body>
<?php include('../admin_details.php'); ?>

<header class="header">
    <div class="logo"><img src="../images/logo.png" alt="Logo" height="100" width="100"></div>
    <nav class="nav">
        <a href="../admin_dashboard.php">Dashboard</a>
        <a href="../manage_instructor.php">Manage Instructors</a>
        <a href="../manage_student.php">Manage Students</a>
        <a href="../manage_course.php" class="active">Manage Courses</a>
        <a href="#">Tasks</a>
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

<main id="addInstructorMain">
<h2>Update Course</h2>
<?php if (isset($_SESSION['error'])): ?>
    <p style="color: #C21807;"><?php echo $_SESSION['error']; ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form action="update_course.php" method="post" enctype="multipart/form-data" id="addUpdateForm">
    <input type="hidden" name="courseID" value="<?php echo $courseID; ?>" />

    <div class="form-group">
        <label for="image">Upload New Image (optional):</label>
        <input type="file" name="image">
        <p>Current Image:</p>
        <img src="<?php echo htmlspecialchars('../images/' . $imageFile); ?>" width="80" height="80" alt="Course Image">
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="courseCode">Course Code:</label>
            <input type="text" name="courseCode" value="<?php echo htmlspecialchars($course['courseCode']); ?>" required>
        </div>
        <div class="form-group">
            <label for="courseName">Course Name:</label>
            <input type="text" name="courseName" value="<?php echo htmlspecialchars($course['courseName']); ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label for="courseInstructor">Course Instructor:</label>
        <select name="courseInstructor" required>
            <option value="">Select Instructor</option>
            <?php foreach ($instructors as $instructor): ?>
                <option value="<?php echo $instructor['instructorID']; ?>" <?php echo ($instructor['instructorID'] == $course['instructorID']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($instructor['firstName'] . ' ' . $instructor['lastName']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="courseDesc">Course Description:</label>
        <textarea name="courseDesc" rows="5"><?php echo htmlspecialchars($course['description']); ?></textarea>
    </div>

    <div class="form-row">
        <button type="submit" id="submit">Update Course</button>
        <button type="button" class="cancel" onclick="window.location.href='../manage_course.php';">Cancel</button>
    </div>
</form>
</main>

<footer class="footer">
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
</footer>
</body>
</html>
