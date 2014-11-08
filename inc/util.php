<?php

public function redirectIfNotLoggedIn(){
  if(!isLoggedIn()){
    _redirectToLogin();
  }
}

public function redirectIfLoggedIn(){
  if(isLoggedIn()){
    _redirectToIndex();
  }
}

/**
 * Make sure a session has been started before calling
 */
public function isLoggedIn(){
  if(session_status() === PHP_SESSION_ACTIVE){
    return isset($_SESSION['userName']);
  }
  return false;
}

public function getUserName(){
  if(!isLoggedIn()){
    throw new AppException("Attempted to get user name while not logged in.");
  }
  return $_SESSION['userName'];
}

public function logout(){
  session_start();
  session_unset();
  session_destroy();
}

public function redirectToLogin(){
  header("Location: login.php");
}

public function redirectToIndex(){
  header("Location: index.php");
}
?>
