<?php

require_once('database.php');
require_once('page_context.php');
require_once('util.php');

/**
 * Main Application Context. All application logic above the SQL layer goes
 * here. This will need to contain the code for rendering pages from diffs,
 * checking user permissions (e.g.: users can't delete other's comments), etc.
 */
class Application {

  private $database;

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
  public function logIn($uname, $pass){
    if($this->database->authenticateUser($uname,$pass)){
      setLoginTrue($uname);
      redirectToIndex();
      return true;
    } else{
      return false;
    }
  }

  /**
   * Wrapper function that checks if a user exists.
   * Returns True if the user exists and false if not.
   */
  public function userExists($uname) {
    return $this->database->userExists($uname);
  }

  /**
   * Adds user to the database, logs them in, and redirects them to the homepage.
   */
  public function registerUser($uname, $pass){
    if($this->database->userExists($uname)){
      throw new AppException("That name is taken.");
    }
    $this->database->insertUser($uname, $pass);
    setLoginTrue($user);
    redirectToIndex();
  }

  /**
   * Saves a new page in the database
   */
  public function newPage($name, $user) {
    if (isLoggedIn()) {
      return PageContext::fromNewPage($this->database, $name, $user);
    }
  }

  /**
   * Loads a page from the database and returns its PageContext object.
   */
  public function loadPage($pid){
    return PageContext::fromDb($this->database, $pid);
  }

  /**
   * Returns an array of page arrays, of the form (id, title, user, created_date).
   */
  public function getSearchResults($title, $author, $keywords){
   return $this->database->queryPages($title,$author);
  }

  /**
   * Gets an array containing the user's user data.
   */
  public function queryUserMetadata($uname) {
    return $this->database->queryUser($uname);
  }
}

?>
