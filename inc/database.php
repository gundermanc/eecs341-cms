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
 * Comment too long exception.
 */
class CommentTooLongDatabaseException extends DatabaseException {

  public function __construct($max) {
    parent::__construct("Comment is more than the max length of $max.");
  }
}

/**
 * Invalid rating exception.
 */
class InvalidRatingDatabaseException extends DatabaseException {

  public function __construct($rating) {
    parent::__construct("Rating must be between 0 and 5. Got $rating.");
  }
}

/**
 * Replace view database exception.
 */
class ReplaceViewDatabaseException extends DatabaseException {

  public function __construct($user, $pageId) {
    parent::__construct("Unable to update View record. Invalid username '$user'"
                        . "or page id '$pageId'.");
  }
}

/**
 * Database interaction class.
 */
class Database {
  const USER_MAX = 20;
  const TITLE_MAX = 250;
  const DIFF_MAX = 990;
  const RATING_MAX = 5;
  const COMMENT_MAX = 250;

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
    if (strlen($user) > Database::USER_MAX) {
      throw new UsernameTooLongDatabaseException($user, Database::USER_MAX);
    }

    $user = $this->connection->escape_string($user);
    $date = Database::timeStamp();
    $pass = hash(Config::HASH_ALGO, $pass);

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
   * Returns: The ID of the page that was inserted.
   */
  public function insertPage($title, $user) {

    if (strlen($title) > Database::TITLE_MAX) {
      throw new TitleTooLongException($title, $max);
    }

    $title = $this->connection->escape_string($title);
    $user = $this->connection->escape_string($user);
    $date = Database::timeStamp();

    $this->query("INSERT INTO Pages (title, user, created_date, cached_data) "
                 . "VALUES ('$title', '$user', '$date', '')");
    return $this->connection->insert_id;
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
    $result = $this->query("SELECT id, title, user, created_date "
                           . "FROM Pages WHERE id=$pageId");

      #return null;
    // No pages with the specified id, return null.
    if ($result->num_rows == 0) {
      return null;
    }

    return $result->fetch_row();
  }

  /**
   * Queries a page's cached content by the page ID.
   * Throws: DatabaseException if a SQL error occurs.
   * Returns: The current cached page content as a string,
   * or null if the page doesn't exist.
   */
  public function queryPageCachedData($pageId) {
    $result = $this->query("SELECT (cached_data) "
                           . "FROM Pages WHERE id=$pageId");

    // No pages with the specified id, return null.
    if ($result->num_rows == 0) {
      return null;
    }

    return $result->fetch_row()[0];
  }

  /**
   * Update's a page's cached content.
   * Throws: DatabaseException if there is a SQL error.
   * Returns: True if everything is ok, and false if there was
   * an error.
   */
  public function updatePageCachedData($pageId, $data) {
    return $this->query("UPDATE Pages SET cached_data='$data' WHERE id=$pageId");
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
    $queryStr = "SELECT (id, title, user, created_date) "
      . "FROM Pages WHERE title LIKE '$title' AND user LIKE '$user'";

    $result = $this->query($queryStr);

    return $result->fetch_all();
  }
  
  /**
   * Inserts a new keyword associated with a page
   */
  public function insertKeyword($page_id, $word) {
    $page_id = $this->connection->escape_string($page_id);
    $word = $this->connection->escape_string($word);
      
    $this->query("INSERT INTO Keywords (page_id, word)"
                   . " VALUES ('$page_id', '$word')");
  }
   
    
    
  public function queryKeywordsByPageId($pageId) {
    $result = $this->query("SELECT * FROM Keywords WHERE page_id='$pageId'");
        
    // No pages with the specified id, return null.
    if ($result->num_rows == 0) {
        return null;
    }
        
    return $result->fetch_all();
  }
    
    
    
  public function queryKeywordsByWord($word) {
    $result = $this->query("SELECT page_id FROM Keywords WHERE word='$word'");
        
    // No pages with the specified id, return null.
    if ($result->num_rows == 0) {
        return null;
    }
        
    return $result->fetch_all();
  }
    
  /**
   * Deletes a keyword that is associated with a page.
   * Throws: DatabaseException if a SQL error occurs.
   * Returns: true, or false if an error occurs.
   */
  public function deleteKeyword($page_id, $word) {
    return $this->query("DELETE FROM Keywords WHERE page_id=$page_id AND word=$word");
  }

  /**
   * Inserts a new change record into the database. Note: actual diffing
   * happens elsewhere. This function just handles the SQL.
   * Throws: DatabaseException if there is a SQL error.
   * Returns: Nothing.
   */
  public function insertChange($user, $pageId, $contribDiff, $approved = null) {
    $user = $this->connection->escape_string($user);
    $contribDiff = $this->connection->escape_string($contribDiff);
    $date = Database::timeStamp();

    if (strlen($contribDiff) > Database::DIFF_MAX) {
      throw new DiffTooLongException(Database::DIFF_MAX);
    }

    if ($approved == null) {
      $approved = "NULL";
    } else if ($approved == true) {
      $approved = "TRUE";
    } else {
      $approved = "FALSE";
    }

    $this->query("INSERT INTO Changes (approved, contrib_diff, "
                 . "change_date, user, page_id) "
                 . "VALUES ($approved, '$contribDiff', '$date', "
                 . "'$user', $pageId)");

    return $this->connection->insert_id;
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
   * (id, approved, contrib_diff, change_date, user, page_id)
   */
  public function queryChangesByPage($pageId, $approved) {
    if ($approved == null) {
      $approved = "NULL";
    } else if ($approved == true) {
      $approved = "TRUE";
    } else {
      $approved = "FALSE";
    }

    $result = $this->query("SELECT * FROM Changes WHERE page_id='$pageId' "
                           . "AND approved=$approved");

    return $result->fetch_all();
  }

  /**
   * Get changes diffs for a page by its pageId.
   * Throws: DatabaseException if SQL error.
   * Returns: a numeric array of diff strings.
   */
  public function queryApprovedChangesDiffs($pageId, $approved) {
    if ($approved == null) {
      $approved = "NULL";
    } else if ($approved == true) {
      $approved = "TRUE";
    } else {
      $approved = "FALSE";
    }

    $result = $this->query("SELECT contrib_diff FROM Changes WHERE page_id='$pageId' "
                           . "AND approved=$approved ORDER BY id ASC");

    $ilkFormat = $result->fetch_all(MYSQLI_NUM);

    // Convert ridiculous array of arrays to a nice array of strings.
    // Sure PHP or MySQLi has a function for this but couldn't find it.
    $niceFormat = array();
    for ($i = 0; $i < count($ilkFormat); $i++) {
      $niceFormat[] = $ilkFormat[$i][0];
    }

    return $niceFormat;
  }

  /**
   * Approves or denies a change entry.
   * Throws: DatabaseException if there is a SQL error.
   * Returns: Nothing.
   */
  public function updateChangeApproved($changeId, $approved) {
    if ($approved == null) {
      $approved = "NULL";
    } else if ($approved == true) {
      $approved = "TRUE";
    } else {
      $approved = "FALSE";
    }

    $this->query("UPDATE Changes SET approved=$approved WHERE id=$changeId");
  }

  /**
   * Creates or updates a view record with the new comment and or rating.
   * Either comment or rating may be null if you don't want to change it.
   * Throws: DatabaseException if a SQL error occurs, or if an invalid
   * user or pageId is given.
   * Returns: Nothing.
   */
  public function replaceView($user, $pageId, $rating, $comment) {

    $columns = "";
    $values = "";

    if ($rating != null) {
      if ($rating < 0 || $rating > Database::RATING_MAX) {
        throw new InvalidRatingDatabaseException($rating);
      }

      $columns .= ", rating";
      $values .= ", $rating";
    }

    if ($comment != null) {
      if (strlen($comment) > Database::COMMENT_MAX) {
        throw new CommentTooLongDatabaseException(Database::MAX_COMMENT);
      }

      $comment = $this->connection->escape_string($comment);
      $columns .= ", comment";
      $values .= ", '$comment'";
    }

    try {
      $this->query("REPLACE INTO Views (user, page_id $columns) "
                   . "VALUES ('$user', $pageId$values)");
    } catch (DatabaseException $ex) {

      // FOREIGN KEY Constraint: Incorrect username or page.
      if ($ex->getSqlCode() == 1452) {
        throw new ReplaceViewDatabaseException($user, $pageId);
      } else {
        throw $ex;
      }
    }
  }

  /**
   * Queries DB for all views of the requested page.
   * Throws: DatabaseException if a SQL error occurs.
   * Returns: an array of "View" arrays. View array is formed as:
   * (user, page_id, rating, comment).
   */
  public function queryViews($pageId) {
    $result = $this->query("SELECT * FROM Views WHERE page_id=$pageId");

    return $result->fetch_all();
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
                 . "cached_data MEDIUMTEXT NOT NULL, "
                 . "PRIMARY KEY (id), "
                 . "FOREIGN KEY (user) REFERENCES Users(user) ON DELETE SET NULL"
                 . ")");

    // Create the changes table.
    $this->query("CREATE TABLE Changes ("
                 . "id MEDIUMINT AUTO_INCREMENT, "
                 . "approved BOOLEAN, "
                 . "contrib_diff VARCHAR(1000) NOT NULL, "
                 . "change_date DATETIME NOT NULL, "
                 . "user VARCHAR(25) , "
                 . "page_id MEDIUMINT NOT NULL, "
                 . "PRIMARY KEY (id), "
                 . "FOREIGN KEY (user) REFERENCES Users(user) ON DELETE SET NULL, "
                 . "FOREIGN KEY (page_id) REFERENCES Pages(id) ON DELETE CASCADE"
                 . ")");
      
    // Create the keywords table.
    $this->query("CREATE TABLE Keywords ("
                 . "page_id MEDIUMINT NOT NULL, "
                 . "word VARCHAR(25) NOT NULL, "
                 . "PRIMARY KEY (page_id, word), "
                 . "FOREIGN KEY (page_id) REFERENCES Pages(id) ON DELETE CASCADE"
                 . ")");

    // Create the views table.
    $this->query("CREATE TABLE Views ("
                 . "user VARCHAR(25), "
                 . "page_id MEDIUMINT, "
                 . "rating TINYINT NOT NULL, "
                 . "comment VARCHAR(255), "
                 . "PRIMARY KEY (user, page_id), "
                 . "FOREIGN KEY (user) REFERENCES Users(user) ON DELETE CASCADE, "
                 . "FOREIGN KEY (page_id) REFERENCES Pages(id) ON DELETE CASCADE"
                 .")");
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
      throw new DatabaseException("Error occurred processing SQL statement '$query': "
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
