<?php
session_start();
require_once('../database.php');

if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'Instructor') {
    header('Location: ../login.php');
    exit();
}

$instructorID = $_SESSION['userID'];
$fullName = $_SESSION['fullName'];

// Fetch instructor details
$queryInstructor = 'SELECT * FROM instructors WHERE instructorID = :id';
$statement = $db->prepare($queryInstructor);
$statement->bindValue(':id', $instructorID);
$statement->execute();
$instructor = $statement->fetch(PDO::FETCH_ASSOC);
$statement->closeCursor();
$imageFile = (!empty($instructor['imageName'])) ? $instructor['imageName'] : 'placeholder.jpg';

// Add new task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['taskTitle'])) {
    $taskTitle = filter_input(INPUT_POST, 'taskTitle', FILTER_SANITIZE_STRING);
    $taskDescription = filter_input(INPUT_POST, 'taskDescription', FILTER_SANITIZE_STRING);

    $insertQuery = "INSERT INTO instructor_tasks (instructorID, taskTitle, taskDescription) 
                    VALUES (:instructorID, :taskTitle, :taskDescription)";
    $stmt = $db->prepare($insertQuery);
    $stmt->bindValue(':instructorID', $instructorID);
    $stmt->bindValue(':taskTitle', $taskTitle);
    $stmt->bindValue(':taskDescription', $taskDescription);
    $stmt->execute();
    $stmt->closeCursor();

    header('Location: instructor_tasks.php');
    exit();
}

// Mark task complete
if (isset($_GET['complete'])) {
    $taskID = intval($_GET['complete']);
    $updateQuery = "UPDATE instructor_tasks SET isComplete = 1 WHERE taskID = :taskID AND instructorID = :instructorID";
    $stmt = $db->prepare($updateQuery);
    $stmt->bindValue(':taskID', $taskID);
    $stmt->bindValue(':instructorID', $instructorID);
    $stmt->execute();
    $stmt->closeCursor();

    header('Location: instructor_tasks.php');
    exit();
}

// Fetch tasks
$query = "SELECT * FROM instructor_tasks WHERE instructorID = :instructorID ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindValue(':instructorID', $instructorID);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Learning Pod - Instructor Dashboard</title>
  <script src="../scripts/app.js" defer></script>
  <link rel="stylesheet" href="../css/app.css"/>
</head>
<body>
<header class="header">
  <div class="logo"><img src="images/logo.png" alt="Logo" height="100" width="100"></div>
  <nav class="nav">
    <a href="instructor_dashboard.php">Dashboard</a>
    <a href="instructor_courses.php">My Courses</a>
    <a href="instructor_students.php">My Students</a>
    <a href="instructor_tasks.php" class="active">Tasks</a>
  </nav>
  <div class="user-info">Hi, <?php echo htmlspecialchars($fullName); ?>
    <div class="profile-wrapper">
      <div class="profile-circle">
        <img src="<?php echo htmlspecialchars('../images/' . $imageFile); ?>" width="40" height="40" style="border-radius:50%;" alt="Profile Picture">
      </div>
      <div class="logOutBox">
        <a href="#" onclick="openUpdateInstructor();">Update Profile</a>
        <a href="logout.php">Log Out</a>
      </div>
    </div>      
  </div>  
</header>

<main class="main-content">
  <section class="stats">
    <div class="stat-box"><p>Total Tasks</p><h2><?php echo count($tasks) > 0 ? count($tasks) : "-"; ?></h2></div>
    <div class="stat-box"><p>Pending Tasks</p><h2><?php echo count(array_filter($tasks, fn($t) => !$t['isComplete'])); ?></h2></div>
    <div class="stat-box"><p>Completed Tasks</p><h2><?php echo count(array_filter($tasks, fn($t) => $t['isComplete'])); ?></h2></div>
  </section>
<section class="tasks-box">
  <form method="POST" action="">
    <div class="form-row">
      <div class="form-group">
        <input type="text" name="taskTitle" placeholder="Task Title" required>
      </div>
      <div class="form-group">
        <input type="text" name="taskDescription" placeholder="Description (optional)">
      </div>
    </div>
    <button type="submit">Add Task</button>
  </form>
</section>


<section class="tasks-list">
  <?php if(count($tasks) > 0): ?>
    <ol>
      <?php foreach($tasks as $task): ?>
        <li class="task-item <?php echo $task['isComplete'] ? 'completed' : ''; ?>">
          <div class="task-content">
            <p><?php echo htmlspecialchars($task['taskDescription']); ?></p>
          </div>
          <div class="task-action">
            <?php if(!$task['isComplete']): ?>
              <a href="?complete=<?php echo $task['taskID']; ?>">Mark Complete</a>
            <?php else: ?>
              <span class="done">Completed</span>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php else: ?>
    <p>No tasks yet.</p>
  <?php endif; ?>
</section>

</main>

<footer class="footer">
  © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
</footer>
</body>
</html>
