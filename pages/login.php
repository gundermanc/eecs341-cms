<?php
require_once '../inc/application.php'
require_once '../inc/util.php';

sesssion_start();
redirectIfLoggedIn();

$message="";
$uname=""
$pass=""

//when a post is recieved
if(isset($_POST['uname'])){
  $uname=$_POST['uname'];
  $pass=$_POST['pass'];
  try{
    Application->logIn();
    redirectToIndex();
  } catch(Exception $e){
    $message=$e->getMessage();
  }
}
?>
<html>
  <body>
    <form action="login.php" method="post">
      Username: <input type=text name=uname maxlength=20><?php echo $uname ?></input>
      Password: <input type=password name=pass maxlength=20><?php echo $pass ?></input>
      <input type=submit></input>
    </form>
Welcome!</br><?php echo $message ?>
 </body>
</html>
