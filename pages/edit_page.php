<?php
require_once '../inc/util.php';
require_once '../inc/style_engine.php';
require_once '../inc/application.php';

session_start();
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
    $A=new Application();
    $pageContext = $A->loadPage($pid);
    // TODO: currently no way to update title.
    // TODO: currently auto accepts each change. Fix when approvals page is done.
    $pageContext->submitChange(getUserName(), $text, true);
  } catch(Exception $e){
    $message=$e->getMessage();
  }
}

else if(isset($_GET['pid'])){
  $pid=$_GET['pid'];
  try{
    $A=new Application();
    $pageContext = $A->loadPage($pid);
    $title = $pageContext->getTitle();
    $text = $pageContext->queryContent();
    $pid = $pageContext->getId();
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
  <form action="edit_page.php" method="post">
    Title:<input name="title" type="text" value="<?= $title?>"></input></br>
    Text:<input name="text" type='textArea' id='input' value="<?= $text ?>"></input></br>
    <input name="pid" type="hidden" value="<?= $pid ?>">
    <input type=submit></input>
  </form>
  <?php echo $message ?>
 </body>
</html>
