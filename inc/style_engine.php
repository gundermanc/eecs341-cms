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
  return "<div><a href='" . Config::APP_ROOT . "/pages/view_page.php?pid=$pid'>$title</a>By <a href='profile.php?u=$user'>$user</a> on $created_date</div>";
}

?>
