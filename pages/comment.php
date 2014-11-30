<?php
require_once '../inc/util.php';
require_once '../inc/style_engine.php';
require_once '../inc/application.php';

session_start();
redirectIfNotLoggedIn();

$title = "";
$pid = "";
$comment = "";
$message = "";

// Get an application context.
$app = new Application();

if(isset($_GET['pid'])){
  $pid = $_GET['pid'];
  try{
    $pageContext = $app->loadPage($pid);
    $title = $pageContext->getTitle();
  } catch(Exception $e){
    $message = $e->getMessage();
  }
}

if(isset($_POST['pid'])) {
  $pid = $_POST['pid'];
  try {
    $comment = $_POST['comment'];
    $pageContext = $app->loadPage($pid);
    $title = $pageContext->getTitle();

    $pageContext->replaceView(getUserName(), null, $comment);
    $message = "Comment saved.";
  } catch(Exception $e){
    $message = $e->getMessage();
  }
}

// Insert the page HTML header with chosen title.
StyleEngine::insertHeader($app, Config::APP_NAME . " - Comment");
/* Begin page content: */ ?>

<h3><i><?=$title?></i> Comment</h3>
<p>
  <span style="color:#FF0000;"> <?php echo $message ?> </span>
</p>
<p>
  <a href="view_page.php?pid=<?=$pid?>">View Page</a>
  <a href="page_comments.php?pid=<?=$pid?>">Comments</a>
</p>
<form action="comment.php" method="post">
  <table>
    <tr>
      <td>
  Comment:
      </td>
      <td>
        <input type="text" name="comment" maxlength="250" value="<?= $comment ?>" />
        <input type="hidden" name="pid" value="<?= $pid ?>" />
      </td>
    </tr>
  </table>
  <br />
  <input type=submit value="Comment" />
</form>


<?php /* End page content. */
// Insert the page HTML footer.
StyleEngine::insertFooter($app);

?>
