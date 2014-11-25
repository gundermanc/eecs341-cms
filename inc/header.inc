<!DOCTYPE html>
<html>
  <!-- UI Container Page-->
  <head>
    <title><?=$styleTitle?></title>
    <link href="<?=Config::APP_ROOT?>/inc/style.css" rel="stylesheet" type="text/css"/>
  </head>
  <body>
  <div id="cms_body_div">
    <div style="background:white;width:100%;margin-bottom:20px;">
      <table style="background:url('<?=Config::APP_ROOT?>/pixmaps/header.png'); background-repeat: repeat-x; width:100%; height:68;" >
        <tr>
          <td>
            <img src="<?=Config::APP_ROOT?>/pixmaps/logo.png"/>
          </td>
          <td>
            <table style="border-bottom: solid 2px #000;">
              <tr>
                <td style="padding-left:15px;">
                  <a href="<?=Config::APP_ROOT?>/pages">Home</a>
                </td>
                <td style="padding-left:25px;">
                  <?php 
                     $root = Config::APP_ROOT;
                     if(isLoggedIn()) {
                       echo "<a href=\"$root/pages/logout.php\">Logout " . getUserName() . "</a>";
                     } else {
                       echo "<a href=\"$root/pages/login.php\">Login</a>";
                     }
                  ?>
                </td style="padding-left:25px;">
                <td style="padding-left:25px;">
                  <a href="<?=Config::APP_ROOT?>/pages/register.php">Register</a>
                </td>
                <td style="padding-left:25px;">
                  <a href="<?=Config::APP_ROOT?>/pages/search.php">Search</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    <div style="margin-left:50px;margin-right:50px;">

<!-- Begin Page Content -->
