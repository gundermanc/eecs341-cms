<?php

/**
 *  Redirects the user to the login page if they are logged out.
 */
public function redirectIfLoggedOut(){
  if(!isset($_SESSION['userName'])){
    header("Location: login.php");
  }
}
?>
