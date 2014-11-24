<?php

/**
 * Returns a box with login info.
 */
function getLoginInfo(){
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
function getThingsToDo(){
  return "<div id='toDo'>
  <a href='write_page.php'>Create</a></br>".(isLoggedIn() ? 
  "<a href='search.php'>search pages</a>" : "").
  "</div>";
}

function makeSearchResult($pid, $title, $user, $created_date){
  return "<div><a href='../pages/view_page.php?pid=$pid'>$title</a>By <a href='profile.php?u=$user'>$user</a> on $created_date</div>";
}

?>