<?php

require_once('config.php');
require_once('util.php');

class StyleEngine {

 public static function insertHeader($app, $title) {
   global $styleTitle;
   global $styleApp;

   $styleTitle = $title;
   $styleApp = $app;
   include('header.inc');
 }

 public static function insertFooter($app) {
   global $styleApp;

   $styleApp = $app;
   include('footer.inc');
 }
}

function makeSearchResult($pid, $title, $user, $created_date){
  return "<tr><td><a href='" . Config::APP_ROOT . "/pages/view_page.php?pid=$pid'>$title</a></td><td>By ".makeUserLink($user)."</td><td> on $created_date</div></td></tr>";
}

function makeProfilePageEntry($pid, $title, $created_date){
    return "<tr><td>".makePageLink($pid, $title)."</td><td> Created $created_date</div></td><td>".
               ": <a href='".Config::APP_ROOT ."/pages/changes.php?pid=$pid'><span id='numPend$pid'>?</span> pending</a></td></tr>";
}

function makePendingChangeEntry($id, $date, $user, $pid){
  return "<tr><td>".makeUserLink($user)."</td><td> on $date</td><td><a href='".Config::APP_ROOT."/pages/view_change.php?cid=$id&pid=$pid'>review</a></td></tr>";
}

function makeUserLink($user){
    return "<a href='".Config::APP_ROOT."/pages/profile.php?u=$user'>$user</a>";
}

function makePageLink($pid, $title){
    return "<a href='" . Config::APP_ROOT . "/pages/view_page.php?pid=$pid'>$title</a>";
}

function makeNewsfeedPageEntry($pid, $title, $created_date){
    return "<tr><td>".makePageLink($pid, $title)."</td><td> Created $created_date</div></td><td></td></tr>";
}

function makeCommentEntry($user, $rating, $comment) {
  if (strlen($comment) > 0) {
    return "<tr><td><i>$user</i></td><td><div align='center'>$rating</div></td><td>$comment</td></tr>";
  }

  return "";
}

function makeProfileViewEntry($pid, $title, $rating, $comment) {
  if (strlen($comment) > 0) {
    return "<tr><td><a href='view_page.php?pid=$pid'>$title</a></td>"
      . "<td><div align='center'>Rated: $rating</div></td><td>Commented: <i>$comment</i></td></tr>";
  }

  return "";
}

function makeProfileChangeEntry($pid, $title, $approved, $date) {
  if ($approved == null) {
    $approved = "Pending";
  } else if ($approved) {
    $approved = "Approved";
  } else {
    $approved = "Denied";
  }

  return "<tr><td><a href='view_page.php?pid=$pid'>$title</a></td>"
    . "<td><div align='center'><i>$approved</i></div></td>"
    . "<td>on $date</td></tr>";
}

?>
