<?php
ini_set('display_errors','1');
ini_set('display_startup_errors','1');
error_reporting(E_ALL);

session_start();
require_once('database.php');

// Only admin access
if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'admin') {
    die("You are not authorized to access this page.");
}

$role = $_SESSION['role'];
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch students
if (!empty($searchQuery)) {
    $stmt = $db->prepare("SELECT studentID, firstName, lastName, email 
                          FROM students 
                          WHERE status='Active' 
                          AND (firstName LIKE :search OR lastName LIKE :search OR email LIKE :search)");
    $stmt->bindValue(':search', "%$searchQuery%");
} else {
    $stmt = $db->prepare("SELECT studentID, firstName, lastName, email 
                          FROM students 
                          WHERE status='Active'");
}
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch courses
$coursesQuery = $db->query("SELECT courseID, courseName, instructorID FROM courses");
$courses = $coursesQuery->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission (optimized insert)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['courseID'], $_POST['studentIDs'])) {
    $courseID = intval($_POST['courseID']);
    $studentIDs = $_POST['studentIDs'];

    foreach ($studentIDs as $studentID) {
        $stmtInsert = $db->prepare("
            INSERT INTO course_enrollments (courseID, studentID, instructorID, status, addedBy)
            SELECT c.courseID, :studentID, c.instructorID, 'pending', 'admin'
            FROM courses c
            WHERE c.courseID = :courseID
        ");
        $stmtInsert->bindValue(':courseID', $courseID, PDO::PARAM_INT);
        $stmtInsert->bindValue(':studentID', intval($studentID), PDO::PARAM_INT);

        try {
            $stmtInsert->execute();
        } catch (PDOException $e) {
            continue; // Skip duplicates/errors
        }
    }

    header('Location: pending_enrollments.php?success=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Enrollments</title>
  <script src="scripts/app.js" defer></script>
  <link rel="stylesheet" href="css/app.css">
  <style>
    .students-table {
      width: 100%;
      max-width: 800px;
      border-collapse: collapse;
      margin: 20px auto;
    }

    .students-table td {
      padding: 10px 12px;
      text-align: left;
      border-bottom: none;  
    }

    .students-table input[type="checkbox"] {
      cursor: pointer;
      transform: scale(1.2);
    }
  </style>
</head>
<body data-role="<?= htmlspecialchars($role) ?>">

<?php include('admin_details.php'); ?> 
<?php include('header.php'); ?>

<main class="main-content" style=" display:block;">
  <h2 style="margin-top: 10px;">Manage Enrollments</h2>

  <?php if(isset($_GET['success']) && $_GET['success'] == 1): ?>
  <div id="successMessage" style="color:#4CAF50; padding:10px 20px; border-radius:4px; text-align:center; margin:15px 0;">
      Students successfully assigned to the course!
  </div>
  <?php endif; ?>

<section class="tasks-box" style="background: transparent; padding: 0; box-shadow: none;">
  <form id="searchForm" method="get" style="width: 100%;">
      <div class="form-row">
        <div class="form-group">
          <input type="text" name="search" placeholder="Search students..." value="<?= htmlspecialchars($searchQuery) ?>" style="width: 700px;">
        </div>
        <div class="form-group" style="margin: 0;">
          <button type="submit" style="margin-top: 0;">Search</button>
        </div>
      </div>
    </form>
</section>

<section class="tasks-box" style="background: transparent; padding: 0; box-shadow: none;">
    <form id="addUpdateForm" method="post" style="max-width: 100%;">
      <select name="courseID" id="courseID" required>
        <option value="">-- Choose Course --</option>
        <?php foreach ($courses as $course): ?>
          <option value="<?= $course['courseID'] ?>"><?= htmlspecialchars($course['courseName']) ?></option>
        <?php endforeach; ?>
      </select>
      <div id="studentsContainer">
        <?php if (!empty($students)): ?>
          <table class="students-table">
            <tbody>
              <?php foreach ($students as $student): ?>
                <tr>
                  <td>
                    <input type="checkbox" name="studentIDs[]" value="<?= $student['studentID'] ?>">
                  </td>
                  <td><?= htmlspecialchars($student['firstName'].' '.$student['lastName']) ?></td>
                  <td style="color: #888;"><?= htmlspecialchars($student['email']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <?php if ($searchQuery): ?>
            <p>No students found.</p>
          <?php else: ?>
            <p>Search for name or email to enroll.</p>
          <?php endif; ?>
        <?php endif; ?>
      </div>
      <button type="submit" class="assign-btn">Assign Students</button>
    </form>
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
