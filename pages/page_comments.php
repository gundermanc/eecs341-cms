<?php

require_once('../inc/application.php');
require_once('../inc/style_engine.php');

session_start();

$message = "";
$pid = "";
$views = null;
$title = "";

// Get an application context.
$app = new Application();

if(isset($_GET['pid'])){
  $pid = $_GET['pid'];
  try{
    $pageContext = $app->loadPage($pid);
    $title = $pageContext->getTitle();
    $views = $pageContext->getViews();
  } catch(Exception $e){
    $message=$e->getMessage();
  }
}

// Insert the page HTML header with chosen title.
StyleEngine::insertHeader($app, Config::APP_NAME . " - Page Comments");

/* Begin page content: */ ?>



<h3><i><?=$title?></i> Comments</h3>
<p>
  <a href="view_page.php?pid=<?=$pid?>">View Page</a>
  <a href="comment.php?pid=<?=$pid?>">Comment</a>
</p>

<?php

if ($views != null) {
  echo "<table>";
  echo "<tr><td><b>User</b></td><td><b>Rating</b></td><td><b>Comment</b></td></tr>";
  foreach ($views as $row) {
    echo makeCommentEntry($row[0], $row[2], $row[3]);
  }
  echo "</table>";
}

?>




<?php /* End page content. */
// Insert the page HTML footer.
StyleEngine::insertFooter($app);

?>
