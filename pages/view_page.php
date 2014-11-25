<?php
require_once '../inc/util.php';
require_once '../inc/style_engine.php';
require_once '../inc/application.php';

session_start();
//redirectIfNotLoggedIn();

$message = "";
$title="";
$text="";
$pid="";

if(isset($_GET['pid'])){
    $pid=$_GET['pid'];
  try{
    $A=new Application();
    $pageContext = $A->loadPage($pid);
    $title = $pageContext->getTitle();
    $text = $pageContext->queryContent();
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
</br>
<a href="edit_page.php?pid=<?= $pid?>">Edit this page</a></br></br>
Title:<div name="title" type="text"><?= $title?></div></br>
Text:<div name="text" type='textArea' id='input'><?= $text ?></div></br>
  </form>
  <?php echo $message ?>
 </body>
</html>