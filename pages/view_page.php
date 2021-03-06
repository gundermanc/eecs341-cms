<?php
require_once '../inc/util.php';
require_once '../inc/style_engine.php';
require_once '../inc/application.php';

session_start();

$message = "";
$title="";
$text="";
$pid="";
$rating = 0;
$owner = "";
$keywords = null;

// Get an application context.
$app = new Application();

if(isset($_GET['pid'])){
  $pid=$_GET['pid'];
  try{
    $pageContext = $app->loadPage($pid);
    $title = $pageContext->getTitle();
    $text = $pageContext->queryContent();
    $owner = $pageContext->getOwner();
    $keywords = $pageContext->queryKeywords();

    if(isset($_GET['rating'])) {
      $rating = $_GET['rating'];

      redirectIfNotLoggedIn();

      if($rating > 5 || $rating < 1) {
        $message = "Rating must be between 1 and 5.";
      } else {
        $pageContext->replaceView(getUserName(), $rating, null);
      }
    } else {
      $rating = $pageContext->queryRating();
    }
  } catch(Exception $e){
    $message=$e->getMessage();
  }
}

// Insert the page HTML header with chosen title.
StyleEngine::insertHeader($app, Config::APP_NAME . " - " . $title);

/* Begin page content: */ ?>

<h3><?= $title ?></h3>
<p>
  <span style="color:#FF0000;"> <?php echo $message ?> </span>
</p>

<p>

<p>
  <i>Owned By: <a href="profile.php?u=<?=$owner?>"><?= $owner ?></a></i>,
  <a href="edit_page.php?pid=<?= $pid?>">Edit this page</a>,
  {Rating{<?=$rating?>}
  <a href="view_page.php?pid=<?=$pid?>&rating=1">1</a>
  <a href="view_page.php?pid=<?=$pid?>&rating=2">2</a>
  <a href="view_page.php?pid=<?=$pid?>&rating=3">3</a>
  <a href="view_page.php?pid=<?=$pid?>&rating=4">4</a>
   <a href="view_page.php?pid=<?=$pid?>&rating=5">5</a>},
<a href="page_comments.php?pid=<?=$pid?>">Comments</a>
  </br>
<?php
if (($keywords != null) && (count($keywords) > 0)) {
  echo "<i>Pertaining to: </i>";
  foreach ($keywords as $keyword) {
    echo "<a href='search.php?title=&author=&keywords=$keyword'>$keyword</a>" . ", ";
  }
}
?>
</p>

<div name="text" type='textArea' id='input'><p><?= $text ?></p></div>

<?php /* End page content. */
// Insert the page HTML footer.
StyleEngine::insertFooter($app);

?>
