<?php

require_once('../inc/application.php');
require_once('../inc/style_engine.php');

session_start();

redirectIfNotLoggedIn();

// Get an application context.
$app = new Application();

// Insert the page HTML header with chosen title.
StyleEngine::insertHeader($app, Config::APP_NAME . " - Profile");

/* Begin page content: */ ?>



<h2>Profile of <?=getUserName()?></h2>

<p>
  This is an example page.
</p>




<?php /* End page content. */
// Insert the page HTML footer.
StyleEngine::insertFooter($app);

?>
