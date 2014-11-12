<?php
require_once '../inc/application.php';

$message="";
$reset="";

if(isset($_POST['reset'])){
    if($_POST['reset'] == "RESET"){
        Application::freshInstall();
        $message="done!";
    }
}
?>
<html>
  <body>
    <form action="reset.php" method="post">
      Type RESET: <input type=text name=reset maxlength=20><?php echo $reset ?></input>
      <input type=submit></input>
    </form>
    <?php echo $message ?>
  </body>
</html>