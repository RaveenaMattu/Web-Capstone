<?php

  session_start();
  require_once('database.php');

  if (!isset($_SESSION['isLoggedIn']) || !isset($_SESSION['adminID'])) {
      echo "You are not authorized to access this page.";
      exit();
  }

  $adminID = $_SESSION['adminID'];

  // Fetch admin info
  $queryAdmin = 'SELECT * FROM admins WHERE adminID = :adminID';
  $statement = $db->prepare($queryAdmin);
  $statement->bindValue(':adminID', $adminID);
  $statement->execute();
  $admin = $statement->fetch();
  $statement->closeCursor();

  $imageFile = (!empty($admin['imageName'])) ? $admin['imageName'] : 'placeholder.jpg';

  // Fetch tasks for this admin
  $queryTasks = 'SELECT * FROM admin_tasks WHERE adminID = :adminID ORDER BY created_at DESC';
  $statement = $db->prepare($queryTasks);
  $statement->bindValue(':adminID', $adminID);
  $statement->execute();
  $tasks = $statement->fetchAll();
  $statement->closeCursor();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Learning Pod - Manage Tasks</title>
  <script src="scripts/app.js" defer></script>
<link rel="stylesheet" href="css/app.css">
</head>
<body>
  <?php include('admin_details.php'); ?>
  <header class="header">
    <div class="logo"><img src="images/logo.png" alt="Logo" height="100" width="100"></div>
    <nav class="nav">
      <a href="admin_dashboard.php">Dashboard</a>
      <a href="manage_instructor.php">Manage Instructors</a>
      <a href="manage_student.php">Manage Students</a>
      <a href="manage_course.php">Manage Courses</a>
      <a href="manage_tasks.php" class="active">Tasks</a>
    </nav>
    <div class="user-info">Hi, <?php echo $_SESSION['fullName']; ?>
      <div class="profile-wrapper">
        <div class="profile-circle">
          <img src="<?php echo htmlspecialchars('./images/' . $imageFile); ?>" width="40" height="40" style="border-radius:50%;" alt="Profile Picture" id="profilePicture">
        </div>
        <div class="logOutBox">
          <a href="#" onclick="openUpdateAdmin();">Update Profile</a>
          <a href="admin_logout.php">Log Out</a>
        </div>
      </div>
    </div>
  </header>

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
      <form action="manage_task/add_task.php" method="post" id="addUpdateForm" style="border:none; box-shadow:none; padding:0; margin:0;">
        <?php if (isset($_SESSION['error'])): ?>
          <p style="color: #C21807;"><?php echo $_SESSION['error']; ?></p>
          <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <h3>Add New Task</h3>
        <input type="hidden" name="adminID" value="<?php echo $adminID; ?>" />
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
                  <div style="background: #fbf8f8ff; border-radius: 20px; padding: 1px 20px; display: table; width: 100%; box-shadow: 0 2px 4px #0000001a;">
                    <div style="display: table-row;">
                      <div style="display: table-cell; text-align: left; vertical-align: middle;<?php echo ($task['isComplete'] == 1 ? 'text-decoration: line-through; color: gray;' : ''); ?>">
                        <p style="margin: 0;"><?php echo htmlspecialchars($task['taskDescription']); ?></p>
                      </div>
                      <div style="display: table-cell; width: 350px; text-align: right; vertical-align: middle;">
                        <div style="display: inline-block;">
                          <!-- Toggle Complete Form -->
                          <form action="manage_task/toggle_task.php" method="post" style="display:inline;">
                            <input type="hidden" name="taskID" value="<?php echo $task['taskID']; ?>">
                            <input type="hidden" name="isComplete" value="<?php echo $task['isComplete']; ?>">
                            <button type="submit" style="padding:5px 10px; cursor:pointer; margin:0 10px;background-color: <?php echo ($task['isComplete'] == 0 ? '#eca726ff' : '#389a4dff'); ?>;">
                              <?php echo $task['isComplete'] == 1 ? 'Completed' : 'Pending'; ?>
                            </button>
                          </form>

                          <!-- Delete Task Form -->
                          <form class="inline-form deleteForm" method="post">
                            <input type="hidden" name="taskID" value="<?php echo $task['taskID']; ?>">
                            <button type="submit" style="font-size: 14px;padding:5px 10px; cursor:pointer; background:#C21807; color:#fff; border:none; margin:0 10px;">
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
