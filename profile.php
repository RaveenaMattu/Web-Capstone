<body data-role="<?php echo htmlspecialchars($role); ?>">
  <div class="user-info">
    Hi, <?php echo $_SESSION['fullName']; ?>
    <div class="profile-wrapper">
      <div class="profile-circle">
        <img src="<?php echo '/web-capstone/images/' . htmlspecialchars($imageFile); ?>" 
            width="40" height="40" alt="Profile Picture" id="profilePicture">
      </div>
      <div class="logOutBox">
        <a href="#" onclick="openUpdateProfile();">Update Profile</a>
        <a href="<?php echo $role === 'admin' ? 'admin_logout.php' : ($role === 'Instructor' ? '../logout.php' : '../logout.php'); ?>" 
          style="color: #C21807;">Log Out</a>
      </div>
    </div>
  </div>
</body>
<script src="scripts/app.js" defer></script>
