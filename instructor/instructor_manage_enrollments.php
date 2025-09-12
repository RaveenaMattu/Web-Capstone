<?php
// instructor_manage_enrollments.php
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

 session_start();
  require_once('../database.php');

  // Make sure user is logged in and is an instructor
  if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== "Instructor") {
    header("Location: ../login_form.php");
    exit();
  }

  $instructorID = $_SESSION['userID'];
  $fullName = $_SESSION['fullName'];
  $role = $_SESSION['role'];

// Toggle enrollment status if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enrollmentID'], $_POST['status'])) {
    $enrollmentID = intval($_POST['enrollmentID']);
    $currentStatus = $_POST['status'];
    
    // Toggle status: only between pending and enrolled
    $newStatus = ($currentStatus === 'pending') ? 'enrolled' : 'pending';

    $updateQuery = "UPDATE course_enrollments SET status = :newStatus WHERE enrollmentID = :enrollmentID";
    $stmt = $db->prepare($updateQuery);
    $stmt->bindValue(':newStatus', $newStatus);
    $stmt->bindValue(':enrollmentID', $enrollmentID, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();

    header('Location: instructor_manage_enrollments.php');
    exit();
}

// Fetch all enrollments for this instructor
$queryStudents = "
    SELECT ce.*, s.firstName, s.lastName, c.courseName
    FROM course_enrollments ce
    JOIN students s ON ce.studentID = s.studentID
    JOIN courses c ON ce.courseID = c.courseID
    WHERE c.instructorID = :instructorID
    ORDER BY c.courseName, s.firstName
";
$statement = $db->prepare($queryStudents);
$statement->bindValue(':instructorID', $instructorID, PDO::PARAM_INT);
$statement->execute();
$students = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement->closeCursor();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instructor - Manage Enrollments</title>
<script src="/web-capstone/scripts/app.js" defer></script>
<link rel="stylesheet" href="../css/app.css"/>
<style>
/* Main container */
.main-content {
    padding: 0;
    width: 100%;
    margin: auto;
}

/* Tasks box */
.tasks-box {
    background: #f9f9f9;
    border-radius: 10px;
    padding: 20px;
    margin-top: 0;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

/* Heading */
h2 {
  text-align: center;
  color: #333;
  margin-bottom: 20px;
  font-weight: 500;
}
/* Table base */
.all-tasks table {
  width: 100%;
  border-collapse: separate; 
  border-spacing: 0 10px;    
}

/* Thead styling */
.all-tasks table thead {
  background-color: #0053c8;
  color: #fff;
}

.all-tasks table thead th {
  padding: 12px 15px;
  text-align: center;
}

.all-tasks table thead th:first-child {
  border-top-left-radius: 8px;
  border-bottom-left-radius: 8px;
}

.all-tasks table thead th:last-child {
  border-top-right-radius: 8px;
  border-bottom-right-radius: 8px;
}

.all-tasks table .enrollment-row {
  background-color: #edf4fb;
}

/* Rounded corners for each row */
.all-tasks table .enrollment-row td:first-child {
  border-top-left-radius: 8px;
  border-bottom-left-radius: 8px;
}

.all-tasks table .enrollment-row td {
  text-align: center;
}

.all-tasks table .enrollment-row td:last-child {
  border-top-right-radius: 8px;
  border-bottom-right-radius: 8px;
}

/* Status button */
.status-btn {
  padding: 4px 14px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  color: #fff;
  font-weight: 300;
  transition: all 0.3s ease;
  margin-top: 0;
}

.status-pending {
  background-color: #eca726ff;
}
.status-pending:hover {
  background-color: #e59400;
}

.status-enrolled {
  background-color: #5ac070ff;
}
.status-enrolled:hover {
  background-color: #2e7d3b;
}

/* Remove button */
.remove-btn {
  padding: 4px 14px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  font-size: 0.9em;
  background-color: #bbb;
  color: #fff;
  transition: background 0.3s;
  margin-top: 0;
}
.remove-btn:hover {
  background-color: #999;
}

</style>
</head>
<body data-role="<?= htmlspecialchars($role); ?>">
<?php include('instructor_header.php'); ?>
      <!-- <h2>All Enrollments (<?= count($students); ?>)</h2> -->
<main class="main-content">
  <?php if (isset($_SESSION['success'])): ?>
    <p id="successMessage" style="color: #2e7d3b; margin-top: 0; text-align: center;"><?php echo $_SESSION['success']; ?></p>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>
  <section class="tasks-box">
    <div class="all-tasks">
      <h2>All Enrollments (<?= count($students); ?>)</h2>

      <?php if (empty($students)): ?>
        <p>No enrollments found for your courses.</p>
      <?php else: ?>
<table>
  <thead>
      <th>Course Name</th>
      <th>Student Name</th>
      <th>Status</th>
      <th>Action</th>
  </thead>

    <?php foreach ($students as $enrollment): ?>
      <tr class="enrollment-row">
        <!-- Course Name -->
        <td>
          <div class="enrollment-card">
            <p class="course-name"><?= htmlspecialchars($enrollment['courseName']); ?></p>
          </div>
        </td>

        <!-- Student Name -->
        <td>
          <div class="enrollment-card">
            <p class="student-name"><?= htmlspecialchars($enrollment['firstName'] . ' ' . $enrollment['lastName']); ?></p>
          </div>
        </td>

        <!-- Status -->
        <td>
          <form method="POST" style="display:inline-block;">
            <input type="hidden" name="enrollmentID" value="<?= $enrollment['enrollmentID']; ?>">
            <input type="hidden" name="status" value="<?= htmlspecialchars($enrollment['status']); ?>">
            <button type="submit" class="status-btn <?= $enrollment['status'] === 'pending' ? 'status-pending' : 'status-enrolled'; ?>">
              <?= $enrollment['status'] === 'pending' ? 'Pending' : 'Enrolled'; ?>
            </button>
          </form>
        </td>

        <!-- Actions -->
        <td>
          <form method="POST" style="display:inline-block;" action="instructor_remove_enrollment.php">
            <input type="hidden" name="enrollmentID" value="<?= $enrollment['enrollmentID']; ?>">
            <button type="submit" class="remove-btn">Remove</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
</table>
      <?php endif; ?>
    </div>
  </section>
</main>

<footer class="footer">
  © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
</footer>
<script>
  const msg = document.getElementById('successMessage');
  if(msg){
    setTimeout(() => {
      msg.style.display = 'none';
    }, 4000);
  }
</script>
</body>
</html>
