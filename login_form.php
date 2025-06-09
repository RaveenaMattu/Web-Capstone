
<?php
session_start();

require('database.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Learning Pod - Login Form</title>
  <link rel="stylesheet" href="css/app.css" />
</head>
<body>
  <div class="logo"></div>
  <main>
    <div class="loginBox">
      <div class="loginForm">
        <h4>Login As</h4>
        <div class="roleToggle">
          <span class="role active">Instructor</span>
          <span class="role">Student</span>
        </div>
        <form action="login.php" method="POST" class="login-form">
          <input type="email" name="email" placeholder="Enter your email" required>
          <input type="password" name="password" placeholder="Enter your password" required>
          <button type="submit">Login</button>
        </form>
      </div>
      <div class="blueSection"></div>
    </div>
  </main>
  <footer>
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
  </footer>
    <script>
    document.querySelectorAll('.role').forEach(role => {
      role.addEventListener('click', function () {
        document.querySelectorAll('.role').forEach(r => r.classList.remove('active'));
        this.classList.add('active');
      });
    });
  </script>
</body>
</html>