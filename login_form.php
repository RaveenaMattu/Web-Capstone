
<?php
session_start();

require('database.php');

$lastRole = $_SESSION['lastRole'] ?? 'Instructor'; 
unset($_SESSION['lastRole']); 

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
  <div class="logo"><img src="images/logo.png" alt="Logo" height="100" width="100"></div>

  <main>
    <div class="loginBox">
      <div class="loginForm">
        <h4>Login As</h4>
        <?php if (isset($_SESSION['error'])): ?>
          <p style="color: #C21807; margin-top: 0; text-align: center;"><?php echo $_SESSION['error']; ?></p>
          <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <div class="roleToggle">
          <span class="role <?php echo $lastRole === 'Instructor' ? 'active' : ''; ?>">Instructor</span>
          <span class="role <?php echo $lastRole === 'Student' ? 'active' : ''; ?>">Student</span>
        </div>
        <form action="instructor-student-login.php" method="POST" class="login-form">
          <input type="hidden" name="role" id="roleInput" value="Instructor">          
          <input type="email" name="email" placeholder="Enter your email" required>
          <input type="password" name="password" placeholder="Enter your password" required>
          <button type="submit">Login</button>
        </form>
      </div>
      <div class="blueSection"><img src="images/adminLogin.jpg" alt="user login"></div>
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
        document.getElementById('roleInput').value = this.innerText; // set hidden input
      });
    });
</script>
</body>
</html>