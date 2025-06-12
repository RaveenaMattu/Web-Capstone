<?php
  session_start();

  $_SESSION = []; // clear the session data
  session_destroy();  //clean up the session id

  header('Location: index.php');
  exit();
?>