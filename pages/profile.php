<?php

require_once('../inc/application.php');
require_once('../inc/style_engine.php');

session_start();
redirectIfNotLoggedIn();

// Get an application context.
$app = new Application();

$uname = "";
$message = "";
$pages = null;
$views = null;
$changes = null;

if (isset($_GET['u'])) {
  $uname = $_GET['u'];
} else {
  $uname = getUserName();
}

try {
  if ($app->userExists($uname)) {
    $pages = $app->getSearchResults(null, $uname, null);
    $views = $app->queryUsersViews(getUserName());
    $changes = $app->queryChangesByUser(getUserName());
  } else {
    $message = "Requested user $uname does not exist.";
  }
} catch (Exception $e) {
  $message = $e->getMessage();
}

// Insert the page HTML header with chosen title.
StyleEngine::insertHeader($app, Config::APP_NAME . " - Profile");

/* Begin page content: */ ?>
<script src="../inc/util.js"></script>
<script>
  window.onload = function(){
    sendRequest("../inc/ajax.php",
                "f=numPending",
                function(response){
                  var json = JSON.parse(response);
                  for(i in json){
                    document.getElementById(json[i].id).innerHTML=json[i].num;
                  }
                });
  }
</script>

<h2>Profile of <?=$uname?></h2>
<p>
  <span style="color:#FF0000;"> <?php echo $message ?> </span>
</p>

<h4>Owned Pages</h4>
<?php
if($pages != null) {
  echo "<table>";
  foreach($pages as $row){
    echo makeProfilePageEntry($row[0], $row[1], $row[3]);
  }
  echo "</table>";
}
?>
<h4>Recently Viewed Pages</h4>
<?php
if($views != null) {
  echo "<table>";
  foreach($views as $row){
    echo makeProfileViewEntry($row[0], $row[1], $row[3], $row[4]);
  }
  echo "</table>";
}
?>
<h4>Recent Posted Edits</h4>
<?php
if($changes != null) {
  echo "<table>";
  foreach($changes as $row){
    echo makeProfileChangeEntry($row[0], $row[1], $row[2], $row[3]);
  }
  echo "</table>";
}
?>

<?php /* End page content. */
// Insert the page HTML footer.
StyleEngine::insertFooter($app);

?>
