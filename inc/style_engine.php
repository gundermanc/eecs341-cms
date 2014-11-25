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
    $context = PageContext::fromDb(new Database, $pid);
    $numPendingChanges = $context->numPendingChanges(getUserName());
    $display = "<tr><td>".makePageLink($pid, $title)."</td><td> Created $created_date</div></td><td>".
               ":<a href='".Config::APP_ROOT ."/pages/changes.php?pid=$pid'> $numPendingChanges pending </a></td></tr>";
    return $display;
}

function makePendingChangeEntry($id, $date, $user){
  return "<tr><td>".makeUserLink($user)."</td><td> on $date</td><td><a href='".Config::APP_ROOT."/pages/view_change.php?cid=$id'>review</a></td></tr>";
}

function makeUserLink($user){
    return "<a href='".Config::APP_ROOT."/pages/profile.php?u=$user'>$user</a>";
}

function makePageLink($pid, $title){
    return "<a href='" . Config::APP_ROOT . "/pages/view_page.php?pid=$pid'>$title</a>";
}

?>
