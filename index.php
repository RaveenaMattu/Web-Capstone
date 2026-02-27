<?php
session_start();
if (isset($_SESSION['error'])): ?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      if (typeof openAdminLogin === "function") {
        openAdminLogin();
      }
    });
  </script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Learning Pod - Login Form</title>
  <link rel="stylesheet" href="css/app.css" />
</head>
<body>
  <div id="adminLightbox">
    <div class="loginBox">
      <div class="loginForm">
        <h4>Admin Login</h4>
        <form action="login.php" method="POST" class="login-form">
            <?php if (isset($_SESSION['error'])): ?>
            <p style="color: #C21807;"><?php echo $_SESSION['error']; ?></p>
            <?php unset($_SESSION['error']); ?>
          <?php endif; ?>
          <input type="hidden" name="role" value="admin">
          <input type="email" name="email" placeholder="Enter your email">
          <input type="password" name="password" placeholder="Enter your password">
          <button type="submit">Login</button>
        </form>
        <button onclick="location.href='/web-capstone/index.php'" id="back">Back To Home Page</button>        
      </div>
      <div class="blueSection"><img src="images/adminLogin.jpg" alt="admin login"></div>
    </div>
  </div>
  <header class="indexHeader">
    <div class="logo"><img src="images/logo.png" alt="Logo" height="100" width="100"></div>
    <div id="indexHeaderRight">
      <a href="#" onclick="openAdminLogin();">Login as Admin</a>
      <a href="login_form.php" id="userLogin">Login</a></div>
  </header>
  <main id="index">
      <div class="indexText">
        <p><span class="big blue">Empower<p> 
        <p>Your Path with <span class="blue">AI</span>, <span class="blue">Goals</span>, and <span class="blue">Guidance</span>.</p>
        <p id='intro'>Built with smart technology and human connection at its core, Smart Learning Pod combines AI-assisted evaluation, weekly goal tracking, and peer-powered learning to create a truly personalized experience. Whether you’re mastering new skills, preparing for a career leap, or simply levelling up, Smart Learning Pod gives you the tools, feedback, and coaching you need — every step of the way. Join a platform where learning meets ambition and support meets strategy.</p>
      </div>
      <div class="introImg"><img src="images/index.png" alt="Logo" style=" width: auto; height: 400px; object-fit: contain; object-position: center; border-radius: 8px;"></div>
    </div>
  </main>
  <footer>
    © 2025 SMART Learning Pod by Raveena Mattu. All Rights Reserved.
  </footer>
  <script src="scripts/app.js" defer></script>
</body>
</html>