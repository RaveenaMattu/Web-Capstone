<?php
 session_start();

  //get data from the form
//   $imageName = $_FILES['file1']['name'];
  $email = filter_input(INPUT_POST, 'email');
  $password = filter_input(INPUT_POST, 'password');
  $_SESSION['pass'] = $password;

  //alternative way to get data from the form
  // $firstName = $_POST['firstName'];
  // $lastName = $_POST['lastName'];
  // $emailAddress = $_POST['emailAddress'];
  // $phoneNumber = $_POST['phoneNumber'];
  // $status = $_POST['status'];
  // $dob = $_POST['dob'];


  require_once('database.php');

  $queryLogin = 'SELECT password, firstName, lastName FROM users
                  WHERE email = :email';
  $statement = $db->prepare($queryLogin);
  $statement->bindValue(':email', $email);
  $statement->execute();
  $login = $statement->fetch();
  $hash = $login['password'];
  $statement->closeCursor();

  if($login) {
    // User exists and password is correct
    // Set session variables
    $_SESSION['isLoggedIn'] = true;
    $_SESSION['fullName'] = $login['firstName'] . ' ' . $login['lastName'];

    // redirect to student dashboard
    header('Location: student_dashboard.php');
    exit();
  } else {
    // Password is incorrect or user does not exist
    $_SESSION = [];
    session_destroy();

    // redirect to login form with error message
    header('Location: login_form.php?error=invalid_credentials');
    exit();
  }

  // $_SESSION['isLoggedIn'] = password_verify($_SESSION['pass'], $hash);

  // if ($_SESSION['isLoggedIn'] == true) {
  //   $_SESSION['email'] = $email;
  //   $_SESSION['password'] = $password;
  //   $_SESSION['hash'] = $hash;

  //   // redirect to login confirmation
  //   header('Location: student_dashboard.php');
  //   die();
  // } elseif ($_SESSION['isLoggedIn'] == false) {
  //     $_SESSION = [];
  //     session_destroy();

  //     // redirect to login
  //     header('Location: login_form.php');
  //     die();
  // } else {
  //   $_SESSION = [];
  //     session_destroy();

  //     // redirect to login
  //     header('Location: login_form.php');
  //     die();
  // }
?>