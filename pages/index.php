<?php
$root=__DIR__."/..";
require_once $root.'/inc/database.php';
require_once $root.'/inc/util.php';

sesssion_start();
redirectIfLoggedOut();

?>

<html>
  <body>
Welcome, <?php echo $_SESSION['userName'] ?></br>
<a href="create.php">Create a page</a></br>
<a href="profile.php?u="<?php echo $_SESSION['userName']?>>View my profile</a></br>
<a href="logout.php">Logout</a></br>
 </body>
</html>
