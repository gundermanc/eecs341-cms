<?php
require_once '../inc/util.php';
require_once '../inc/styleEngine.php'
require_once '../inc/application.php'

sesssion_start();
redirectIfNotLoggedIn();

$message = "";
$title="";
$text="";

if(isset($_POST['title'])){
  $title=$_POST['title'];
  $text=$_POST['text'];
  Application->savePage();//TODO
}

if(isset($_GET['title'])){
  $title=$_POST['title'];
  Application->loadPage();//TODO
}

?>
<html>
  <body>
<?php 
  echo getLoginInfo();
  echo getThingsToDo();
?>
  <form action="editPage.php" method="post">
    <input name="title" type="text"><?php echo $title ?></input>
    <input name="text" type='textArea' id='input'><?php echo $text ?></input>
    <input type=submit></input>
  </form>
  <?php echo $message ?>
 </body>
</html>
