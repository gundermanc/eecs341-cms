<?php
require_once('../inc/application.php');
require_once('../inc/style_engine.php');

session_start();
redirectIfNotLoggedIn();

// Get an application context.
$app = new Application();

$message = "";
$pid=null;
$context=null;
$pending=null;

try {
  if (isset($_GET['pid'])) {
    $pid = $_GET['pid'];
    $context = $app->loadPage($pid);
    $pending = $context->pendingChanges(getUserName());
  } else {
    $message = "no page id found";
  }
} catch (Exception $ex) {
  $message = $ex->getMessage();
}

// Insert the page HTML header with chosen title.
StyleEngine::insertHeader($app, Config::APP_NAME . " - Review");

/* Begin page content: */ ?>
<p>
  <span style="color:#FF0000;"> <?php echo $message ?> </span>
</p>
<?php
if($pending != null){
    echo "<table>";
    foreach($pending as $row){
        echo makePendingChangeEntry($row[0], $row[2], $row[3], $pid);
    }
    echo "</table>";
}
?>
<?php /* End page content. */
// Insert the page HTML footer.
StyleEngine::insertFooter($app);

?>
