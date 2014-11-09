<?php
require_once '../inc/application.php'
require_once '../inc/util.php';

sesssion_start();
redirectIfLoggedIn();

$message="";
$uname="";
$pass1="";
$pass2="";

//when a post is recieved
if(isset($_POST['uname'])){
  $uname=$_POST['uname'];
  $pass1=$_POST['pass1'];
  $pass2=$_POST['pass2'];
  if($pass1 != $pass2){
    $message = "Your passwords don't match";
    return;
  }
  try{
    Application->registerUser($uname, $pass);
  } catch(Exception $e){
    $message = $e->getMessage();
  }
}
?>
<html>
  <body>
    <form action="register.php" method="post">
      Username: <input type=text name=uname maxlength=20><?php echo $uname ?></input>
      Password: <input type=password name=pass maxlength=20><?php echo $pass1 ?></input>
      Password again: <input type=password name=pass maxlength=20><?php echo $pass2 ?></input>
      <input type=submit></input>
    </form>
Register an account</br><?php echo $message ?>
 </body>
</html>
