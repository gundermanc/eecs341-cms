<?php
require_once '../inc/util.php';
require_once '../inc/application.php';
require_once '../inc/style_engine.php';

session_start();.

$title="";
$author="";
$message="";
$keywords="";
$pages=null;

// Get an application context.
$app = new Application();

if(isset($_GET['title'])){
  $title = $_GET['title'];
  $author = $_GET['author'];
  $keywords= $_GET['keywords'];
  try{
    $pages = $app->getSearchResults(
	$title==""? null : $title,
	$author==""? null : $author,
	$keywords==""? null : $keywords);
  } catch(Exception $e){
    $message = $e->getMessage();
  }
}

// Insert the page HTML header with chosen title.
StyleEngine::insertHeader($app, Config::APP_NAME . " - Search");

/* Begin page content: */ ?>

<h3>Search Pages</h3>
<p>
  <span style="color:#FF0000;"> <?php echo $message ?> </span>
</p>
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
<form action="search.php" method="get">
    <table>
      <tr>
        <td>Search by title:</td>
        <td><input type=text name=title maxlength=20 value="<?php echo $title ?>" /></td>
      </tr>
      <tr>
        <td><span id="toggle" onclick=openAdv()>Advanced Search</span></td>
      </tr>
    </table>

    <!-- advanced search -->
    <span id="adv" style="margin-left:25px;">
    <table>
      <tr>
        <td>Search by author:</td>
        <td><input type=text name=author maxlength=20 value="<?php echo $author ?>" /></td>
      </tr>
      <tr>
        <td>Search by keywords(separated by commas):</td>
        <td><input type=text name=keywords maxlength=20 value="<?php echo $keywords ?>" /></td>
      </tr>
    </table>
    <ul>
      <li>% is unlimited chars</li>
      <li>_ is any one char</li>
    </ul>
    </span>
    <br /><br />
    <input type=submit value="Search"></input>
  </form>
<?php

//display search results

if($pages != null) {
  echo "<hr />";
  echo "<table>";
  foreach($pages as $row){
    echo makeSearchResult($row[0],$row[1],$row[2],$row[3]);
  }
  echo "</table>";
}
?>

<?php /* End page content. */
// Insert the page HTML footer.
StyleEngine::insertFooter($app);

?>
