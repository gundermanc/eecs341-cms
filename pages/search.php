<?php
require_once '../inc/application.php';
require_once '../inc/util.php';

sesssion_start();
redirectIfLoggedOut();

$search="";
$message="";
$pages=null;

if(isset($_GET['search'])){
  $search = $_GET['search'];
  try{
    $pages = Application->getSearchResults($search);
  } catch(Exception $e){
    $message = $e->getException();
  }
}

?>
<html>
  <body>
<?php 
  echo getLoginInfo();
  echo getThingsToDo();
?>
Note:</br>
% is unlimited chars</br>
_ is any one char</br>
  <form action="search.php" method="get">
      Search: <input type=text name=search maxlength=20><?php echo $search ?></input>
      <input type=submit></input>
  </form>
<?php
echo $message
if($pages != null){
  while ($row = mysql_fetch_array($pages)){
    echo "<div><a href='editPage?pid=".$row['id']."'>".$row['title']."</a>By <a href='profile.php?u=".$row['user']."'></a> on ".$row['created_date']."</div>";
  }
}

 </body>
</html>
