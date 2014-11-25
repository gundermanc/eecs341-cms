<?php
require_once '../inc/util.php';
require_once '../inc/style_engine.php';
require_once '../inc/application.php';

session_start();
redirectIfNotLoggedIn();

$message = "";
$title="";
$text="";
$pid="";

// Get an application context.
$app = new Application();

if(isset($_POST['pid'])){
  $title=$_POST['title'];
  $text=$_POST['text'];
  $pid= $_POST['pid'];
  try{
    $pageContext = $app->loadPage($pid);
    // TODO: currently no way to update title.
    // TODO: currently auto accepts each change. Fix when approvals page is done.
    $pageContext->submitChange(getUserName(), $text);
  } catch(Exception $e){
    $message=$e->getMessage();
  }
} else if(isset($_GET['pid'])){
  $pid=$_GET['pid'];
  try{
    $pageContext = $app->loadPage($pid);
    $title = $pageContext->getTitle();
    $text = $pageContext->queryContent();
    $pid = $pageContext->getId();
  } catch(Exception $e){
    $message=$e->getMessage();
  }
}

// Insert the page HTML header with chosen title.
StyleEngine::insertHeader($app, Config::APP_NAME . " - Edit");

/* Begin page content: */ ?>

<h3>Editing <i><?=$title?></i></input></h3>
<p>
  <span style="color:#FF0000;"> <?php echo $message ?> </span>
</p>
<form action="edit_page.php" method="post">
  <input type=submit value="Update Page"></input>
  <a href="view_page.php?pid=<?= $pid ?>" > View Page </a>
  <textarea name="text" style="width:100%;height:100%;" id='input'><?= $text ?></textarea>
  <input name="pid" type="hidden" value="<?= $pid ?>">
  <input name="title" type="hidden" value="<?= $title ?>">
  </form>
 </body>
</html>

<?php /* End page content. */
// Insert the page HTML footer.
StyleEngine::insertFooter($app);

?>
