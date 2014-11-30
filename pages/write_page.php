<?php
require_once '../inc/util.php';
require_once '../inc/style_engine.php';
require_once '../inc/application.php';

session_start();
redirectIfNotLoggedIn();

$title="";
$message="";
$keywords="";

// Get an application context.
$app = new Application();

if(isset($_POST['title'])){
  $title = $_POST['title'];
  $keywords = $_POST['keywords'];
  try{
    $pageContext = $app->newPage($title, getUserName());
    $pageContext->addKeywords(explode(",", $keywords));
    redirectToEdit($pageContext->getId());
  } catch(Exception $e){
    $message = $e->getMessage();
  }
}

// Insert the page HTML header with chosen title.
StyleEngine::insertHeader($app, Config::APP_NAME . " - New Page");
/* Begin page content: */ ?>

<h3>Create new page</h3>
<p>
  <span style="color:#FF0000;"> <?php echo $message ?> </span>
</p>
<form action="write_page.php" method="post">
  <table>
    <tr>
      <td>
        Title:
      </td>
      <td>
        <input type="text" name="title" maxlength="250" value="<?php echo $title ?>"/>
      </td>
    </tr>
    <tr>
      <td>Keywords:</td>
      <td><input type="text" name="keywords" maxlength="250" value="<?php echo $keywords ?>"></td>
    </tr>
  </table>
  <br />
  <input type=submit></input>
</form>


<?php /* End page content. */
// Insert the page HTML footer.
StyleEngine::insertFooter($app);

?>
