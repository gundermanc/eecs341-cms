<?php

require_once('../inc/application.php');
require_once('../inc/style_engine.php');
require_once('../inc/util.php');

session_start();
redirectIfLoggedIn();

$message="";
$uname="";
$pass1="";
$pass2="";

$app = new Application();

// When a post is received.
if(isset($_POST['uname'])) {
  $uname=$_POST['uname'];
  $pass1=$_POST['pass1'];
  $pass2=$_POST['pass2'];
  if($pass1 != $pass2) {
    $message = "Your passwords don't match";
  } else {
    try{
      $app->registerUser($uname, $pass1);
      setLoginTrue($uname);
    } catch(Exception $e){
      $message = $e->getMessage();
    }
  }
}

// Insert the page HTML header with chosen title.
StyleEngine::insertHeader($app, Config::APP_NAME . " - Register");
/* Begin page content: */ ?>

<h3>Register for a <?=Config::APP_NAME?> account.</h3>

<p>
  <span style="color:#FF0000;"> <?php echo $message ?> </span>
</p>
<form action="register.php" method="post">
  <table>
    <tr>
      <td>
        Username:
      </td>
      <td>
        <input type=text name=uname maxlength=20 value="<?php echo $uname ?>"></input>
      </td>
    </tr>
    <tr>
      <td>
        Password:
      </td>
      <td>
        <input type=password name=pass1 maxlength=20 value="<?php echo $pass1 ?>" />
      </td>
    <tr>
      <td>
        Password again:
      </td>
      <td>
        <input type=password name=pass2 maxlength=20 value="<?php echo $pass2 ?>"></input>
      </td>
    </tr>
  </table>
  <br />
  <input type=submit></input>
</form>

<?php /* End page content. */
// Insert the page HTML footer.
StyleEngine::insertFooter($app);

?>
