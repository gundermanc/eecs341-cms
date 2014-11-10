<?php

require_once 'config.php';
require_once 'exception.php';

/**
 * Database exception type.
 */
class DatabaseException extends AppException {

  public function __construct($message, $sqlCode = 0,
                              Exception $previous = null) {
    $this->sqlCode = $sqlCode;
    parent::__construct($message, 0, $previous);
  }

  public function getSqlCode() {
    return $this->sqlCode;
  }

  // custom string representation of object
  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }
}

/**
 * User already exists exception type.
 */
class UserExistsDatabaseException extends DatabaseException {

  public function __construct($user) {
    $this->user = $user;
    parent::__construct("User '$user' already exists.");
  }

  public function getUser() {
    return $this->user;
  }
}

/**
 * User already exists exception type.
 */
class UsernameTooLongDatabaseException extends DatabaseException {

  public function __construct($user, $max) {
    parent::__construct("User '$user' is more than the max length of $max.");
  }
}






/**
 * Database interaction class.
 */
class Database {
  const USER_MAX = 20;

  /**
   * Constructs a database interaction object and connects to the DB.
   * install: If true, connects to the DB and performs wipe and fresh
   * install of the DB before constructing the object.
   */
  public function __construct($freshInstall = false) {
    $this->connection = new mysqli(Config::SQL_SERVER,
                                   Config::SQL_USER,
                                   Config::SQL_PASS);

    // SQL connection attempt failed.
    if ($this->connection->connect_error) {
      throw new DatabaseException("Failed to connect to SQL server. Code " 
                                  . $this->connection->connect_error);
    }

    // Perform fresh install before selecting db.
    if ($freshInstall) {
      $this->freshInstall();
    }

    // Try Select DB. If SQL DB selection failed, app is probably not installed.
    if (!$this->connection->select_db(Config::SQL_DB)) {
      throw new DatabaseException("Failed to select database '"
                                  . Config::SQL_DB
                                  . "'. Ensure that you have installed the application.");
    }
  }

  /**
   * Destroys the object when finished and closes the connection.
   */
  public function __destruct() {
    $this->connection->close();
  }

  /**
   * Attempts to create the user with the specified password in the users
   * table. Passwords are stored as encrypted one-way hashes.
   * Throws: UserExistsDatabaseException if another user with that name exists
   * already.
   * Throws: DatabaseException if a SQL error occurs.
   * Returns: Nothing.
   */
  public function insertUser($user, $pass) {
    $user = $this->connection->escape_string($user);
    $date = date("Y-m-d H:i:s");
    $pass = hash(Config::HASH_ALGO, $pass);

    if (strlen($user) > Database::USER_MAX) {
      throw new UsernameTooLongDatabaseException($user, Database::USER_MAX);
    }

    try {
      $this->query("INSERT INTO Users (user, pass, join_date)"
                       . " VALUES ('$user', '$pass', '$date')");
    } catch (DatabaseException $ex) {

      // DUPLICATE ENTRY: A user with the specified name already exists.
      if ($ex->getSqlCode() == 1062) {
        throw new UserExistsDatabaseException($user);
      } else {
        throw $ex;
      }
    }
  }

  /**
   * Checks that a user exists.
   * Returns: True if the specified user exists, and false if not.
   * Throws: Database exception if SQL error occurs.
   */
  public function userExists($user) {
    $user = $this->connection->escape_string($user);

    $result = $this->query("SELECT user FROM Users U where U.user='$user'");

    return ($result->num_rows > 0);
  }

  /**
   * Authenticates a user by comparing his/her username and password to the
   * stored password hashes. This function should be used at login.
   * Returns: True if the user name and password match, false if they don't,
   * or the user doesn't exist.
   * Throws: DatabaseException if SQL error occurs.
   */
  public function authenticateUser($user, $pass) {
    $user = $this->connection->escape_string($user);

    $result = $this->query("SELECT pass FROM Users U WHERE U.user='$user'");
    // Invalid username.
    if ($result->num_rows == 0) {
      return false;
    }
    
    $passHash = $result->fetch_row()[0];
    // Invalid password.
    if (hash(Config::HASH_ALGO, $pass) != $passHash) {
      return false;
    }
    // User authentication successful.
    return true;
  }

  /**
   * Deletes a user account.
   * Returns: True if the user was deleted succesfully, and
   * false if the user doesn't exist.
   * Throws: DatabaseException if there is a SQL error.
   */
  public function deleteUser($user) {
    $user = $this->connection->escape_string($user);

    $this->query("DELETE FROM Users WHERE user='$user'");

    return ($this->connection->affected_rows > 0);
  }

  /**
   * Inserts a new blank page with the given title and owner.
   * Throws: DatabaseException if a SQL error occurs, or if insertPage
   * is requested for user that doesn't exist.
   * Returns: True if the page was created and false if not.
   */
  public function insertPage($title, $user) {
    $title = $this->connection->escape_string($title);
    $user = $this->connection->escape_string($user);

    $this->query("INSERT INTO Pages (title, user) VALUES ('$title', '$user')");
    return mysqli_insert_id($this->connection);
  }

  /**
   * Deletes a page identified by its page id.
   * Throws: DatabaseException if a SQL error occurs.
   * Returns: True if page is deleted and false if page not exist.
   */
  public function deletePage($id) {
    $this->query("DELETE FROM Pages WHERE id=$id");
    return ($this->connection->affected_rows > 0);
  }

  /**
   * Drops the old database and creates the table schemas from scratch.
   */
  private function freshInstall() {

    // Drop the old database *sniffle* goodbye!
    $this->query("DROP DATABASE IF EXISTS " . Config::SQL_DB);

    // Create the parent database.
    $this->query("CREATE DATABASE " . Config::SQL_DB);
    $this->query("USE " . Config::SQL_DB);

    // Create users table.
    $this->query("CREATE TABLE Users ("
                     . "user VARCHAR(25), "
                     . "pass VARCHAR(64), "
                     . "join_date DATETIME, "
                     . "PRIMARY KEY(user) "
                     . ")");

    // Create admin user account.
    $this->insertUser(Config::ADMIN_USER, Config::ADMIN_PASS);

    // Create the pages table.
    $this->query("CREATE TABLE Pages ("
                 . "id MEDIUMINT AUTO_INCREMENT, "
                 . "title VARCHAR(255) NOT NULL, "
                 . "user VARCHAR(25), "
                 . "PRIMARY KEY (id), "
                 . "FOREIGN KEY (user) REFERENCES Users(user)"
                 . ")");
  }

  /**
   * Performs a SQL query on the current DB instance.
   * Must have created object, loaded a DB, first.
   * Throws: DatabaseException if a SQL error occurred.
   * Returns: Nothing.
   */
  private function query($query) {
    $result = $this->connection->query($query);
    if (!$result) {
      throw new DatabaseException("Error occurred processing SQL statement: "
                                  . $this->connection->error,
                                  $this->connection->errno);
    }

    return $result;
  }
}

?>
