
<?php
session_start();

require('database.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Learning Pod - Student Dashboard</title>
  <link rel="stylesheet" href="css/app.css" />
</head>
<body>
  <div class="logo"></div>
  <main>
    <p>Welcome, <?php echo $_SESSION['fullName'];?>!</p>
  </main>
  <footer>
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
  </footer>
</body>
</html>