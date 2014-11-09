<?php
require_once '../inc/util.php';
require_once '../inc/styleEngine.php'
require_once '../inc/application.php'

sesssion_start();
redirectIfNotLoggedIn();

$title="";
$message="";

if(isset($_POST['title'])){
  $title = $_POST['title'];
  try{
    Application->newPage($title);
  } catch(Exception $e){
    $message = $e->getMessage();
  }
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
    <input type=submit></input>
  </form>
  <?php echo $message ?>
 </body>
</html>
