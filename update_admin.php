<?php
require_once('database.php');

$adminID = filter_input(INPUT_POST, 'adminID', FILTER_VALIDATE_INT);
$username = filter_input(INPUT_POST, 'username');
$email = filter_input(INPUT_POST, 'emailAddress');
$image = $_FILES['image'] ?? null;

$image_dir = 'images/';
$image_dir_path = getcwd() . DIRECTORY_SEPARATOR . $image_dir;

// Fetch current imageName to possibly delete it later
$query = 'SELECT * FROM admins WHERE adminID = :adminID';
$statement = $db->prepare($query);
$statement->bindValue(':adminID', $adminID);
$statement->execute();
$admin = $statement->fetch();
$statement->closeCursor();

$oldImageName = $admin['imageName'] ?? '';
$imageName = $oldImageName;

// Upload new image if provided
if ($image && $image['error'] === UPLOAD_ERR_OK) {
    $filename = basename($image['name']);
    $target = $image_dir_path . $filename;

    if (move_uploaded_file($image['tmp_name'], $target)) {
        $imageName = $filename;

        // Delete old image if it exists and is not placeholder
        if (!empty($oldImageName) && $oldImageName !== 'placeholder.jpg') {
            $oldPath = $image_dir_path . $oldImageName;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }
    } else {
        echo "❌ Failed to upload image to $target";
        exit();
    }
}

// Update admin record
$updateQuery = 'UPDATE admins 
                SET imageName = :imageName, username = :username, emailAddress = :emailAddress 
                WHERE adminID = :adminID';
$statement = $db->prepare($updateQuery);
$statement->execute([
    ':imageName' => $imageName,
    ':username' => $username,
    ':emailAddress' => $email,
    ':adminID' => $adminID
]);
$statement->closeCursor();

// Redirect back
header('Location: admin_dashboard.php');
exit();
?>
