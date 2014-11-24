<?php

function redirectIfNotLoggedIn(){
  if(!isLoggedIn()){
    redirectToLogin();
  }
}

function redirectIfLoggedIn(){
  if(isLoggedIn()){
    redirectToProfile();
  }
}

function setLoginTrue($uname){
  if(session_status() !== PHP_SESSION_ACTIVE){
    throw new AppException("Cannot log in without an active session.");
  }
  $_SESSION['userName'] = $uname;
}

/**
 * Make sure a session has been started before calling.
 * A closed session could mean they logged out, or you forgot to open one
 */
function isLoggedIn(){
  if(session_status() === PHP_SESSION_ACTIVE){
    return isset($_SESSION['userName']);
  }
  return false;
}

function getUserName(){
  if(!isLoggedIn()){
    throw new AppException("Attempted to get user name while not logged in.");
  }

  return $_SESSION['userName'];
}

function logout(){
  session_start();
  session_unset();
  session_destroy();
}

function redirectToLogin(){
  header("Location: login.php");
}

function redirectToIndex(){
  header("Location: index.php");
}

function redirectToEdit($pid){
  header("Location: edit_page.php?pid=$pid");
}

function redirectToProfile() {
  header("Location: profile.php");
}
?>
