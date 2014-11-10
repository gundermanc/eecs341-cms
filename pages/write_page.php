<?php
require_once '../inc/util.php';
require_once '../inc/style_engine.php';
require_once '../inc/application.php';

session_start();
redirectIfNotLoggedIn();

$title="";
$message="";

if(isset($_POST['title'])){
  $title = $_POST['title'];
  try{
    $A = new Application();
    $id = $A->newPage($title);
    redirectToEdit($id);
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
  <form action="write_page.php" method="post">
    <input name="title" type="text" value="<?php echo $title ?>"></input>
    <input type=submit></input>
  </form>
  <?php echo $message ?>
 </body>
</html>
