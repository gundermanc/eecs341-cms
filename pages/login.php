<?php
require_once('../inc/application.php');
require_once('../inc/style_engine.php');
require_once('../inc/util.php');

session_start();
redirectIfLoggedIn();

$message="";
$uname="";
$pass="";

// Get an application context.
$app = new Application();

//when a post is recieved
if(isset($_POST['uname'])){
  $uname=$_POST['uname'];
  $pass=$_POST['pass'];
  try{
    if(!$app->logIn($uname, $pass)) {
      $message = "Invalid username password combination.";
    }
  } catch(Exception $e){
    $message=$e->getMessage();
  }
}

// Insert the page HTML header with chosen title.
StyleEngine::insertHeader($app, "Page Title");

/* Begin page content: */ ?>

<h3>Login to <?=Config::APP_NAME?></h3>

<p>
  <span style="color:#FF0000;"> <?php echo $message ?> </span>
</p>
<p>
  Please enter your user name and password. If you do not yet have an account,
  register <a href="register.php">here</a>.
</p>
<form action="login.php" method="post">

  <table>
    <tr>
      <td>
        Username:
      </td>
      <td>
        <input type="text" name="uname" maxlength="20" value="<?php echo $uname ?>"></input>
      </td>
    </tr>
    <tr>
      <td>
        Password:
      </td>
      <td>
        <input type="password" name="pass" maxlength="20" />
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
