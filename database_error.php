<?php
  session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Learning Pod - Database Error</title>
  <link rel="stylesheet" type="text/css" href="css/app.css">
</head>
<body>
  <div class="logo"></div>
  <main class="errorBox">
    <h2>Database Error</h2>
    <p>There was an error connecting to the database. Please try again later.</p>
    <p>The database must be installed.</p>
    <p>MySQL must be running.</p>
    <p>Error message: <?php echo $_SESSION['database_error']; ?></p>
    <p><a href='login_form.php'>Back to Login</a></p>
  </main>
  <footer>
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
  </footer>
</body>
</html>