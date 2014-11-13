<?php
require_once '../inc/util.php';
require_once '../inc/application.php';
require_once '../inc/style_engine.php';

session_start();
redirectIfLoggedOut();

$title="";
$author="";
$message="";
$keywords="";
$pages=null;

if(isset($_GET['title'])){
  $title = $_GET['title'];
  $author = $_GET['author'];
  $keywords= $_GET['keywords'];
  try{
    $A =new Application();
    $pages = $A->getSearchResults(
	$title==""? null : $title,
	$author==""? null : $author,
	$keywords==""? null : $keywords);
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
      #toggle{
	text-decoration: underline;
	cursor: pointer;
      }
    </style>
  </head>
  <body>
<?php 
  echo getLoginInfo();
  echo getThingsToDo();
?>
  <form action="search.php" method="get">
      Search by title: <input type=text name=title maxlength=20 value="<?php echo $title ?>"></input>
      <!-- advanced search -->
      </br><span id="toggle" onclick=openAdv()>Advanced Search</span>
      <span id="adv">
	Search by author: <input type=text name=author maxlength=20 value="<?php echo $author ?>"></input></br>
	Search by keywords(separated by commas): <input type=text name=keywords maxlength=20 value="<?php echo $keywords ?>"></input></br>
	% is unlimited chars</br>
	_ is any one char
      </span>
      <input type=submit></input>
  </form>
<?php
echo $message;

//display search results
if($pages != null){
  while ($row = mysql_fetch_array($pages)){
    echo makeSearchResult($row['id'],$row['title'],$row['user'],$row['created_date']);
  }
}
?>
 </body>
</html>
