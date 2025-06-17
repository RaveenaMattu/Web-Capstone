  <div id="updateAdmin">
    <div class="updateAdminBox">
      <h2>Admin Profile</h2>
      <div class="adminProfile">
        <div class="adminImage">        
          <img src="images/<?php echo htmlspecialchars($imageFile); ?>" alt="Admin Photo" width="200" height="200" id="adminImage">
        </div>
        <div class="adminDetails">
          <form action="update_admin.php" method="post" enctype="multipart/form-data">

            <input type="hidden" name="adminID" value="<?php echo $admin['adminID']; ?>">

            <label>Photo:</label>
            <input type="file" name="image"><br>

            <label for="firstName">Username:</label>
            <input type="text" id="username" name="username" required value="<?php echo $admin['username']; ?>"><br>
            
            <label for="lastName">Email:</label>
            <input type="email" id="email" name="emailAddress" required value="<?php echo $admin['emailAddress']; ?>"><br>
            <button type="submit">Update Profile</button>
            <button onclick="closeUpdateAdmin();" class="cancel">Cancel</button>
          </form>
        </div>
      </div>
    </div>
  </div>