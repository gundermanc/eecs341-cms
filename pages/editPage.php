<?php
require_once '../inc/util.php';
require_once '../inc/styleEngine.php'
require_once '../inc/application.php'

sesssion_start();
redirectIfNotLoggedIn();

$message = "";
$title="";
$text="";
$pid="";

if(isset($_POST['pid'])){
  $title=$_POST['title'];
  $text=$_POST['text'];
  $pid= $_POST['pid'];
  try{
    Application->savePage($pid, $title, $text);
  } catch(Exception $e){
    $message=$e->getMessage();
  }
}

if(isset($_GET['pid'])){
  $title=$_GET['pid'];
  try{
    list($title, $text) = Application->loadPage();
  } catch(Exception $e){
    $message=$e->getMessage();
  }
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
    <input name="pid" type="hidden" value="<?php echo $pid ?>">
    <input type=submit></input>
  </form>
  <?php echo $message ?>
 </body>
</html>
