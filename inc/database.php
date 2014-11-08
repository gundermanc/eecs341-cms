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
 * Username too long exception.
 */
class UsernameTooLongDatabaseException extends DatabaseException {

  public function __construct($user, $max) {
    parent::__construct("User '$user' is more than the max length of $max.");
  }
}

/**
 * Title too long exception.
 */
class TitleTooLongDatabaseException extends DatabaseException {

  public function __construct($title, $max) {
    parent::__construct("Title '$title' is more than the max length of $max.");
  }
}

/**
 * Diff too long exception.
 */
class DiffTooLongDatabaseException extends DatabaseException {

  public function __construct($max) {
    parent::__construct("Diff is more than the max length of $max.");
  }
}






/**
 * Database interaction class.
 */
class Database {
  const USER_MAX = 20;
  const TITLE_MAX = 250;
  const DIFF_MAX = 990;

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
    $date = Database::timeStamp();
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
    $date = Database::timeStamp();

    if (strlen($title) > Database::TITLE_MAX) {
      throw new TitleTooLongException($title, $max);
    }

    return $this->query("INSERT INTO Pages (title, user, created_date) "
                        . "VALUES ('$title', '$user', '$date')");
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
   * Queries a page by it's ID.
   * Throws: DatabaseException if a SQL error occurs.
   * Returns: The specified page as an array (id, title, user, created_date),
   * or null if the page doesn't exist.
   */
  public function queryPageById($pageId) {
    $result = $this->query("SELECT * FROM Pages WHERE id=$pageId");

    // No pages with the specified id, return null.
    if ($result->num_rows == 0) {
      return null;
    }

    return $result->fetch_row();
  }

  /**
   * Finds all pages with the specified title, or title pattern. e.g.: 
   * $title="Hi%Charlie" matches any string that begins with Hi and ends
   * with Charlie. Uses SQL 'LIKE' patterns.
   * Username must match given username. Name can be null for any user.
   * '%' means match unlimited any chars.
   * '_' means match one any char.
   * Throws: DatabaseException if SQL errors occur.
   * Returns: An array of "Page" arrays. Page array is of format
   * (id, title, user, created_date). Returns empty array on no matches.
   */
  public function queryPages($title = null, $user = null) {

    if ($title == null) {
      $title = "%";
    }

    if ($user == null) {
      $user = "%";
    }

    $title = $this->connection->escape_string($title);
    $user = $this->connection->escape_string($user);
    $queryStr = "SELECT * FROM Pages WHERE title LIKE '$title' AND user LIKE '$user'";

    $result = $this->query($queryStr);

    return $result->fetch_all();
  }

  /**
   * Inserts a new change record into the database. Note: actual diffing
   * happens elsewhere. This function just handles the SQL.
   * Throws: DatabaseException if there is a SQL error.
   * Returns: Nothing.
   */
  public function insertChange($user, $pageId, $parentId,  
                               $contribDiff, $approved = null) {
    $user = $this->connection->escape_string($user);
    $contribDiff = $this->connection->escape_string($contribDiff);
    $date = Database::timeStamp();
    $parentId = $parentId == null ? "NULL" : $parentId;

    if (strlen($contribDiff) > Database::MAX_DIFF) {
      throw new DiffTooLongException(Database::MAX_DIFF);
    }

    if ($approved == null) {
      $approved = "NULL";
    } else if ($approved == true) {
      $approved = "TRUE";
    } else {
      $approved = "FALSE";
    }

    $this->query("INSERT INTO Changes (approved, contrib_diff, "
                 . "change_date, user, page_id, parent_id) "
                 . "VALUES ($approved, '$contribDiff', '$date', "
                 . "'$user', $pageId, $parentId)");
  }

  /**
   * Deletes a change by its changeId number.
   * Throws: DatabaseException if a SQL error occurs.
   * Returns: true, or false if an error occurs. NOTE: this function
   * will still return true if no change with the specified id exists.
   */
  public function deleteChange($changeId) {
    return $this->query("DELETE FROM Changes WHERE id=$changeId");
  }

  /**
   * Get changes for a page by its pageId.
   * Throws: DatabaseException if SQL error.
   * Returns: an array of "Changes" arrays of form
   * (id, approved, contrib_diff, change_date, user, page_id, parent_id)
   */
  public function queryChangesByPage($pageId) {
    $result = $this->query("SELECT * FROM Changes WHERE page_id='$pageId'");

    return $result->fetch_all();
  }

  /**
   * Approves or denies a change entry.
   * Throws: DatabaseException if there is a SQL error.
   * Returns: True if everything is ok, and false if there was
   * an error.
   */
  public function updateChangeApproved($changeId, $approved) {
    if ($approved == null) {
      $approved = "NULL";
    } else if ($approved == true) {
      $approved = "TRUE";
    } else {
      $approved = "FALSE";
    }

    return $this->query("UPDATE Changes SET approved=$approved WHERE id=$changeId");
  }

  /**
   * Drops the old database and creates the table schemas from scratch.
   */
  private function freshInstall() {

    // Drop the old database *sniffle* goodbye!
    $this->query("DROP DATABASE " . Config::SQL_DB);

    // Create the parent database.
    $this->query("CREATE DATABASE " . Config::SQL_DB);
    $this->query("USE " . Config::SQL_DB);

    // Create users table.
    $this->query("CREATE TABLE Users ("
                     . "user VARCHAR(25), "
                     . "pass VARCHAR(64) NOT NULL, "
                     . "join_date DATETIME NOT NULL, "
                     . "PRIMARY KEY(user) "
                     . ")");

    // Create admin user account.
    $this->insertUser(Config::ADMIN_USER, Config::ADMIN_PASS);

    // Create the pages table.
    $this->query("CREATE TABLE Pages ("
                 . "id MEDIUMINT AUTO_INCREMENT, "
                 . "title VARCHAR(255) NOT NULL, "
                 . "user VARCHAR(25), "
                 . "created_date DATETIME NOT NULL, "
                 . "PRIMARY KEY (id), "
                 . "FOREIGN KEY (user) REFERENCES Users(user)"
                 . ")");

    // Create the changes table.
    $this->query("CREATE TABLE Changes ("
                 . "id MEDIUMINT AUTO_INCREMENT, "
                 . "approved BOOLEAN, "
                 . "contrib_diff VARCHAR(1000) NOT NULL, "
                 . "change_date DATETIME NOT NULL, "
                 . "user VARCHAR(25) NOT NULL, "
                 . "page_id MEDIUMINT NOT NULL, "
                 . "parent_id MEDIUMINT, "
                 . "PRIMARY KEY (id), "
                 . "FOREIGN KEY (user) REFERENCES Users(user), "
                 . "FOREIGN KEY (page_id) REFERENCES Pages(id), "
                 . "FOREIGN KEY (parent_id) REFERENCES Changes(id)"
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

  /**
   * Gets the current date and time in a SQL ready format.
   */
  private static function timeStamp() {
    return date("Y-m-d H:i:s");
  }
}

?>
