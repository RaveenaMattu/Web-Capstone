<div class="user-info">
  Hi, <?php echo $_SESSION['fullName']; ?>
  <div class="profile-wrapper">
    <div class="profile-circle">
      <img src="<?php echo '/web-capstone/images/' . htmlspecialchars($imageFile); ?>" width="40" height="40" alt="Profile Picture" id="profilePicture">
    </div>
    <div class="logOutBox">
      <a href="#" onclick="openUpdateAdmin();">Update Profile</a>
      <a href="admin_logout.php" style="color:#C21807;">Log Out</a>
    </div>
  </div>
</div>