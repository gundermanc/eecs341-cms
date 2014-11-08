<?php

/**
 * Returns a box with login info.
 */
public function getLoginInfo(){
  return "<div id='loginInfo'>".(isLoggedIn() ? 
  "Logged in as ".getUserName()."
  <a href='logout.php'>Log out</a>"
  :
  "<a href='login.php'>Log In</a>").
  "</div>";
}

/**
 * Returns a series of links for things to do.
 * ex. view profile, write page, ect.
 */
public function getThingsToDo(){
  return "<div id='toDo'>
  <a href=''>blah</a>".(isLoggedIn() ? 
  "<a href=''>blah2</a>" : "").
  "</div>";
}

?>
