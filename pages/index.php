<?php

require_once('../inc/application.php');
require_once('../inc/style_engine.php');
require_once('../inc/util.php');

session_start();

$app = new Application();

$message = null;
$pages = null;

try {
  $pages = $app->queryPopularPages();
} catch (Exception $e) {
  $message = $e->getMessage();
}

// Get an application context.
$app = new Application();

// Insert the page HTML header with chosen title.
StyleEngine::insertHeader($app, Config::APP_NAME . " - Home");
/* Begin page content: */ ?>

<h2>Welcome to <?=Config::APP_NAME?></h2>
<p>
  <span style="color:#FF0000;"> <?php echo $message ?> </span>
</p>
<p>
  <?=Config::APP_NAME?> is a notes sharing system written in PHP and MySQL.
  Users can create pages on topics, share them, review other pages, comment,
  and expand their profile.
</p>
<p>
  Click <a href="<?=Config::APP_ROOT?>/pages/register.php">here</a> to sign up.
</p>

<h4>Popular Pages</h4>
<?php
if($pages != null) {
  echo "<table>";
  foreach($pages as $row){
    echo makeNewsfeedPageEntry($row[0], $row[1], $row[2]);
  }
  echo "</table>";
}
?>

<?php /* End page content. */
// Insert the page HTML footer.
StyleEngine::insertFooter($app);

?>
