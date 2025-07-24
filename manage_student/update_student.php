<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    session_start();

    require_once('../database.php');

    $studentID = filter_input(INPUT_POST, 'studentID', FILTER_VALIDATE_INT);
    $firstName = filter_input(INPUT_POST, 'firstName');
    $lastName = filter_input(INPUT_POST, 'lastName');
    $dob = filter_input(INPUT_POST, 'dob');
    $contactNumber = filter_input(INPUT_POST, 'contactNumber');
    $email = filter_input(INPUT_POST, 'email');
    $password = filter_input(INPUT_POST, 'password');
    $mailingAddress = filter_input(INPUT_POST, 'mailingAddress');
    $status = filter_input(INPUT_POST, 'status');
    $image = $_FILES['image'] ?? null;

    // if (!$studentID || !$firstName || !$lastName || !$dob || !$emailAddress) {
    //     echo "Missing required fields.";
    //     exit();
    // }

    // Get existing student details
    $query = 'SELECT * FROM students WHERE studentID = :studentID';
    $statement = $db->prepare($query);
    $statement->bindValue(':studentID', $studentID);
    $statement->execute();
    $existingStudent = $statement->fetch();
    $statement->closeCursor();

    if (!$existingStudent) {
        echo "Student not found.";
        exit();
    }

    $originalEmail = $existingStudent['email'];
    $originalPassword = $existingStudent['password'];
    $originalImage = $existingStudent['imageName'];

    // Check if email is changed, then check if already exists
    if ($email !== $originalEmail) {
        $query = 'SELECT COUNT(*) FROM students WHERE email = :email AND studentID != :studentID';
        $statement = $db->prepare($query);
        $statement->bindValue(':email', $emailAddress);
        $statement->bindValue(':studentID', $studentID);
        $statement->execute();
        $emailCount = $statement->fetchColumn();
        $statement->closeCursor();

        if ($emailCount > 0) {
            $_SESSION['error'] = 'Email address already exists.';
            header("Location: update_instructor_form.php");
            exit();
        }
    }

    // use new password if entered, else keep existing
    $hashedPassword = $password ? password_hash($password, PASSWORD_DEFAULT) : $originalPassword;

    // use new image if uploaded, else keep existing
    $imageName = $originalImage;
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        $image_dir = '../images/';
        $filename = basename($image['name']);
        $target = $image_dir . $filename;
        if (move_uploaded_file($image['tmp_name'], $target)) {
            $imageName = $filename;
        } else {
            echo "Failed to upload image.";
            exit();
        }
    }

    //  Update query
    $query = 'UPDATE students 
            SET firstName = :firstName, lastName = :lastName, dob = :dob, 
                contactNumber = :contactNumber, email = :email, password = :password, 
                mailingAddress = :mailingAddress, status = :status, imageName = :imageName
            WHERE studentID = :studentID';

    $statement = $db->prepare($query);
    $statement->bindValue(':firstName', $firstName);
    $statement->bindValue(':lastName', $lastName);
    $statement->bindValue(':dob', $dob);
    $statement->bindValue(':contactNumber', $contactNumber);
    $statement->bindValue(':email', $email);
    $statement->bindValue(':password', $hashedPassword);
    $statement->bindValue(':mailingAddress', $mailingAddress);
    $statement->bindValue(':status', $status);
    $statement->bindValue(':imageName', $imageName);
    $statement->bindValue(':studentID', $studentID);
    $statement->execute();
    $statement->closeCursor();

    // Redirect back to the list
    header("Location: ../manage_student.php");
    exit();
?>
