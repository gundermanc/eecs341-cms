<?php
require_once '../inc/application.php';
require_once '../inc/util.php';

sesssion_start();
redirectIfLoggedOut();

$title="";
$author="";
$message="";
$pages=null;

if(isset($_GET['title'])){
  $title = $_GET['title'];
  $author = $_GET['author'];
  try{
    $pages = Application->getSearchResults(
			  $title==""? null : $title,
			  $author==""? null : $author);
  } catch(Exception $e){
    $message = $e->getException();
  }
}

?>
<html>
  <head>
    <script>
      function openAdv(){
	document.getElementById("adv").style.display="block";
      }
    </script>
    <style>
      #adv{
	display: none;
      }
    </style>
  </head>
  <body>
<?php 
  echo getLoginInfo();
  echo getThingsToDo();
?>
  <form action="search.php" method="get">
      Search by title: <input type=text name=title maxlength=20><?php echo $title ?></input>
      <!-- advanced search -->
      <span onclick=openAdv()>Advanced Search</span>
      <span id="adv">
	Search by author: <input type=text name=author maxlength=20><?php echo $author ?></input></br>
	% is unlimited chars</br>
	_ is any one char
      </span>
      <input type=submit></input>
  </form>
<?php
echo $message

//display search results
if($pages != null){
  while ($row = mysql_fetch_array($pages)){
    echo "<div><a href='editPage?pid=".$row['id']."'>".$row['title']."</a>By <a href='profile.php?u=".$row['user']."'></a> on ".$row['created_date']."</div>";
  }
}
?>
 </body>
</html>
