<?php
require_once '../inc/util.php';
require_once '../inc/styleEngine.php'
require_once '../inc/application.php'

sesssion_start();
redirectIfNotLoggedIn();

$text="";
$title="";
$message="";

if(isset($_POST['title'])){
  $title = $_POST['title'];
  $text = $_POST['text'];
  //call application func: save new page
}
?>
<html>
  <body>
<?php 
  echo getLoginInfo();
  echo getThingsToDo();
?>
  <form action="writePage.php" method="post">
    <input name="title" type="text"><?php echo $title ?></input>
    <input name="text" type='textArea'><?php echo $text ?></input>
    <input type=submit></input>
  </form>
  <?php echo $message ?>
 </body>
</html>
