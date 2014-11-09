<?php

require_once('database.php');
require_once('util.php');

/**
 * Main Application Context. All application logic above the SQL layer goes
 * here. This will need to contain the code for rendering pages from diffs,
 * checking user permissions (e.g.: users can't delete other's comments), etc.
 */
class Application {

  /**
   * Creates an application instance.
   */
  public function __construct() {
    $this->database = new Database();
  }

  /**
   * Wipes old database and installs new one with current configuration.
   */
  public static function freshInstall() {
    new Database(true);
  }

  /**
   * Logs the player in and redirects them to the home page.
   * Returns false if not accepted
   */
  public static function logIn($uname, $pass){
    if($this->database->authenticateUser($uname,$pass)){
      logIn($uname);
      redirectToIndex();
      return true;
    } else{
      return false;
    }
  }
}
?>
