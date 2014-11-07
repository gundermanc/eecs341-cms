<?php
$root=__DIR__."/..";
require_once $root.'/inc/database.php';

sesssion_start();

$message="";
$uname=""
$pass=""

//redirect if logged in
if(isset($_SESSION['userName'])){
    header("Location: index.php");
}

//when a post is recieved
if(isset($_POST['uname'])){
  $uname=$_POST['uname'];
  $pass=$_POST['pass'];
  //authenticate
  $db = new Database();
  if($db->authenticateUser($uname,$pass)){
    //success
    $_SESSION['userName'] = $uname;
    header("Location: index.php");
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
