<?php
require_once '../inc/util.php';
require_once '../inc/style_engine.php';
require_once '../inc/application.php';

session_start();

$message = "";
$title="";
$text="";
$pid="";

// Get an application context.
$app = new Application();

if(isset($_GET['pid'])){
    $pid=$_GET['pid'];
  try{
    $pageContext = $app->loadPage($pid);
    $title = $pageContext->getTitle();
    $text = $pageContext->queryContent();
  } catch(Exception $e){
    $message=$e->getMessage();
  }
}

// Insert the page HTML header with chosen title.
StyleEngine::insertHeader($app, "Page Title");

/* Begin page content: */ ?>

<h3><?= $title ?></h3>
<p>
  <span style="color:#FF0000;"> <?php echo $message ?> </span>
</p>

<p>
  <i>Owned By: <?= $pageContext->getOwner() ?></i>
  <a href="edit_page.php?pid=<?= $pid?>">Edit this page</a></br></br>
</p>

<div name="text" type='textArea' id='input'><p><?= $text ?></p></div>

<?php /* End page content. */
// Insert the page HTML footer.
StyleEngine::insertFooter($app);

?>
<<<<<<< HEAD
<html>
  <body>
</br>
<a href="edit_page.php?pid=<?= $pid?>">Edit this page</a></br></br>
Title:<div name="title" type="text"><?= $title?></div></br>
Text:<div name="text" type='textArea' id='input'><?= $text ?></div></br>
  </form>
  <?php echo $message ?>
 </body>
</html>
=======
>>>>>>> 2a647bd... Styled search pages and view page.
