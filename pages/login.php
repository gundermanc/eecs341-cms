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
  //authenticate
  $db = new Database();
  if($db->authenticateUser($uname,$pass)){
    //success
    logIn($uname);
    redirectToIndex();
  } else{
    $message="Could not log you in."
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
