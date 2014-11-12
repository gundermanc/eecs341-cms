<?php

require_once '../inc/util.php';
require_once '../inc/style_engine.php';

session_start();

?>
<html>
  <body>
<?php
  echo getLoginInfo();
  echo getThingsToDo();
?>
 </body>
</html>
