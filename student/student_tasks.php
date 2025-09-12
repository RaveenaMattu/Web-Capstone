<?php
  ini_set('display_errors','1');
  ini_set('display_startup_errors','1');
  error_reporting(E_ALL);

  session_start();
  require_once('../database.php');

  if (!isset($_SESSION['isLoggedIn']) || $_SESSION['role'] !== 'Student') {
    var_dump($_SESSION);
    exit;
 
  }

  $studentID = $_SESSION['userID'];
  $fullName = $_SESSION['fullName'];
  $role = $_SESSION['role'];

  // Fetch student details
  $queryStudent = 'SELECT * FROM students WHERE studentID = :id';
  $statement = $db->prepare($queryStudent);
  $statement->bindValue(':id', $studentID);
  $statement->execute();
  $student = $statement->fetch(PDO::FETCH_ASSOC);
  $statement->closeCursor();
  $imageFile = (!empty($student['imageName'])) ? $student['imageName'] : 'placeholder.jpg';

  // Add new task
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['taskDescription'])) {
    $taskDescription = filter_input(INPUT_POST, 'taskDescription', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $insertQuery = "INSERT INTO student_tasks (studentID, taskDescription) 
                    VALUES (:studentID, :taskDescription)";
    $stmt = $db->prepare($insertQuery);
    $stmt->bindValue(':studentID', $studentID);
    $stmt->bindValue(':taskDescription', $taskDescription);
    $stmt->execute();
    $stmt->closeCursor();

    header('Location: student_tasks.php');
    exit();
  }

  // Toggle task completion
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['taskID'], $_POST['isComplete'])) {
    $taskID = intval($_POST['taskID']);
    $currentStatus = intval($_POST['isComplete']);
    $newStatus = ($currentStatus === 1) ? 0 : 1;

    $updateQuery = "UPDATE student_tasks SET isComplete = :newStatus 
                    WHERE taskID = :taskID AND studentID = :studentID";
    $stmt = $db->prepare($updateQuery);
    $stmt->bindValue(':newStatus', $newStatus, PDO::PARAM_INT);
    $stmt->bindValue(':taskID', $taskID, PDO::PARAM_INT);
    $stmt->bindValue(':studentID', $studentID, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();

    header('Location: student_tasks.php');
    exit();
  }

  // Fetch tasks
  $query = "SELECT * FROM student_tasks WHERE studentID = :studentID ORDER BY created_at DESC";
  $stmt = $db->prepare($query);
  $stmt->bindValue(':studentID', $studentID);
  $stmt->execute();
  $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $stmt->closeCursor();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Learning Pod - student Dashboard</title>
  <script src="../scripts/app.js" defer></script>
  <link rel="stylesheet" href="../css/app.css"/>
</head>
<body data-role="<?php echo htmlspecialchars($role); ?>">
<?php include('student_header.php'); ?>

  <main class="main-content">

    <div id="overlay">
      <div id="deletePopup">
        <p>Are you sure you want to delete this task?</p>
        <form id="popupDeleteForm" action="manage_task/delete_task.php" method="post">
          <input type="hidden" name="taskID" id="popupRecordID" />
          <div class="popup-buttons">
            <button type="submit" id="delete" class="confirm">Yes, Delete</button>
            <button type="button" onclick="closePopup()" id="cancel" class="cancel">No, Cancel</button>
          </div>
        </form>
      </div>
    </div>
    <section class="tasks-box">
      <form action="" method="post" id="addUpdateForm" style="border:none; box-shadow:none; padding:0; margin:0;">
        <?php if (isset($_SESSION['error'])): ?>
          <p style="color: #C21807;"><?php echo $_SESSION['error']; ?></p>
          <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <h3>Add New Task</h3>
        <input type="hidden" name="studentID" value="<?php echo $studentID; ?>" />
        <table style="border-collapse: collapse; width: 100%;">
          <tr>
            <td style="border-bottom: none;">
              <input type="text" name="taskDescription" placeholder="Task Description" 
                    style="width: 400px; padding: 8px 20px;" required>
            </td>
            <td style="border-bottom: none;">
              <button type="submit" class="btn" style="width: 300px; padding: 8px 20px; cursor: pointer; margin:0;">Add Task</button>
            </td>
          </tr>
        </table>
      </form>
    </section>

    <section class="tasks-box">
      <div class="all-tasks">
        <h3>All Tasks (<?php echo count($tasks); ?>)</h3>
        <?php if (empty($tasks)): ?>
          <p>No tasks found. Add a new task above.</p>
        <?php else: ?>
          <table style="width: 100%; border-collapse: separate; border-spacing: 0 15px;">
            <?php foreach ($tasks as $task): ?>
              <tr>
                <td style="padding: 0; border-bottom: none;">
                  <div style="background: #edf4fb; border-radius: 8px; padding: 1px 20px; display: table; width: 100%;">
                    <div style="display: table-row;">
                      <div style="display: table-cell; text-align: left; vertical-align: middle;<?php echo ($task['isComplete'] == 1 ? 'text-decoration: line-through; color: gray;' : ''); ?>">
                        <p style="margin: 0;"><?php echo htmlspecialchars($task['taskDescription']); ?></p>
                      </div>
                      <div style="display: table-cell; width: 350px; text-align: right; vertical-align: middle;">
                        <div style="display: inline-block;">
                          <!-- Toggle Complete Form -->
                          <form action="" method="post" style="display:inline;">
                            <input type="hidden" name="taskID" value="<?php echo $task['taskID']; ?>">
                            <input type="hidden" name="isComplete" value="<?php echo $task['isComplete']; ?>">
                            <button type="submit" style="width: 125px; padding:5px 10px; cursor:pointer; margin:0 10px;background-color: <?php echo ($task['isComplete'] == 0 ? '#eca726ff' : '#389a4dff'); ?>;">
                              <?php echo $task['isComplete'] == 1 ? 'Completed' : 'Pending'; ?>
                            </button>
                          </form>

                          <!-- Delete Task Form -->
                          <form class="inline-form deleteForm" method="post">
                            <input type="hidden" name="taskID" value="<?php echo $task['taskID']; ?>">
                            <button type="submit" style="font-size: 15px;padding:5px 10px; cursor:pointer; background:#bbb; color:#fff; border:none; margin:0 10px;">
                              Remove
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
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
</body>
</html>
