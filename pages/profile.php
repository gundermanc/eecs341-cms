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
<h4>My Pages</h4>
<table>
  <tr>
    <td><a href="write_page.php">Create page</a></td>
  </tr>
</table>



<?php /* End page content. */
// Insert the page HTML footer.
StyleEngine::insertFooter($app);

?>
