<?php
require_once '../inc/util.php';
require_once '../inc/style_engine.php';
require_once '../inc/application.php';

session_start();
redirectIfNotLoggedIn();

// Get an application context.
$app = new Application();

$message = "";
$changeId=null;
$context=null;
$cid=null;
$pid=null;
$change=null;

if (isset($_GET['cid']) && isset($_GET['pid'])) {
  $cid = $_GET['cid'];
  $pid = $_GET['pid'];
  $context = $app->loadPage($pid);
  $change = $context->loadChange($cid,getUserName());
} else {
    $message = "no change/page id found";
}

if (isset($_POST['pid'])) {
    $pid = $_POST['pid'];
    $cid = $_POST['cid'];
    $context = $app->loadPage($pid);
    $context->setChangeApproved($cid,isset($_POST['approve']));
    $message= "Done.";
}

// Insert the page HTML header with chosen title.
StyleEngine::insertHeader($app, Config::APP_NAME . " - Edit");
/* Begin page content: */ ?>


<h3><?= $context->getTitle() ?></h3>
<p>
  <span style="color:#FF0000;"> <?php echo $message ?> </span>
</p>

<p>
    By: <?=$change[4]?></br>
    On: <?=$change[3]?></br>
</p>
<form action="view_change.php" method="post">
    <textarea type=textBox name="text" style="width:100%;height:100px;" id='input'><?= $change[2] ?></textarea>
    Approve: <input type="checkbox" name="approve" value="approved"></br>
    <input name="pid" type="hidden" value="<?= $pid ?>">
    <input name="cid" type="hidden" value="<?= $cid ?>">
    <input type=submit value="Submit"></input>
</form>

<?php /* End page content. */
// Insert the page HTML footer.
StyleEngine::insertFooter($app);
?>