<?php
require_once '../inc/util.php';
require_once '../inc/styleEngine.php'

sesssion_start();

?>
<html>
  <body>
<?php 
  echo getLoginInfo();
  echo getThingsToDo();
?>
 </body>
</html>
