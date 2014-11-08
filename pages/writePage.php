<?php
require_once '../inc/util.php';
require_once '../inc/styleEngine.php'

sesssion_start();

?>
<html>
  <head>
    <script src="../inc/util.js"></script>
    <script>
      var input = document.getElementById('input');
      var msgBox = document.getElementById('message');
      function submit(){
	sendRequest("createPage.php",
		    "u=".getUserName()."&t=".input.value(),
		    function(response, msgBox){
		      msgBox.innerHTML = response;
		    });
      }
    </script>
  </head>
  <body>
<?php 
  echo getLoginInfo();
  echo getThingsToDo();
?>
  <input type='textArea' id='input'>Write here</input>
  <input type='button' onclick='submit()'>Submit!</input>
  <div id="message"></div>
 </body>
</html>
