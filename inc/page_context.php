<?php

require_once('config.php');
require_once('exception.php');
require_once('database.php');
require_once('delta_encoder.php');

/**
 * Allows for querying of page information, content, title, ownership
 * and generation of page text and types through diffing and patching.
 */
class PageContext {

  private $database;
  private $title;
  private $owner;
  private $id;
  private $content = null;
  private $views = null;
  private $keywords = null;

  /**
   * Creates a new page and returns a page context for it.
   */
  public static function fromNewPage($database, $title, $owner) {
    $pageContext = new self();

    $pageContext->database = $database;
    $pageContext->title = $title;
    $pageContext->owner = $owner;

    $pageContext->id = $pageContext->database->insertPage($pageContext->title,
                                                          $pageContext->owner);

    return $pageContext;
  }

  /**
   * Gets a page context for the page specified by the given pageId.
   */
  public static function fromDb($database, $pageId) {
    $pageContext = new self();

    $pageContext->database = $database;
    $pageContext->id = $pageId;

    // See queryPageById in database.php for schema info.
    $record = $pageContext->database->queryPageById($pageContext->id);

    // Query list of views, ratings, comments.
    $pageContext->views = $pageContext->database->queryViews($pageContext->id);

    // Save record to the class fields.
    $pageContext->title = $record[1];
    $pageContext->owner = $record[2];
    $pageContext->id = $record[0];

    return $pageContext;
  }

  /**
   * Adds or updates a View record comment and rating for a page.
   * If rating or comment is null, it will not be changed.
   */
  public function replaceView($user, $rating, $comment) {
    $this->database->replaceView($user, $this->id, $rating, $comment);
  }

  /**
   * Gets the average page rating for this page.
   */
  public function queryRating() {
    return $this->database->queryPageRating($this->id);
  }

  /**
   * Gets the page title.
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * Gets the page owner username.
   */
  public function getOwner() {
    return $this->owner;
  }

  /**
   * Gets the page Id.
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Gets an array of Page View arrays.
   */
  public function getViews() {
    return $this->views;
  }

  /**
   * Add page keyword, first trying best to strip out whitespace.
   * Returns: nothing.
   */
  public function addKeyword($keyword) {
    $keyword = trim($keyword, " \t\n\r\0\x0B,");
    $keyword = strtolower($keyword);

    if (strlen($keyword) != 0) {
      $this->database->insertKeyword($this->id, $keyword);
    }
  }

  /**
   * Adds an array of keywords to this page.
   * Returns: nothing.
   */
  public function addKeywords($keywords) {
    foreach ($keywords as $keyword) {
      $this->addKeyword($keyword);
    }
  }

  /**
   * Submits a new pending change and returns the ID integer.
   */
  public function submitChange($user, $newContent, $approved = null) {

    // Diff the changes.
    $diff = DeltaEncoder::encodeDelta($this->queryContent(), $newContent);

    // Store the diff and changes as a pending change and returns change ID.
    $id = $this->database->insertChange($user, $this->id, $diff, $approved);

    if ($approved) {
      $this->rebuildContent();
    }

    return $id;
  }
  
  /**
   *Returns a list of unapproved changes made to this page
   *(id, approved, change_date, user, page_id)
   */
  public function pendingChanges($user){
    $this->checkIsOwner($user);
    $array = $this->database->queryChangesByPage($this->id,null/*not approved*/);
    return $array;
  }
  
  /**
   *Returns the amount of unapproved changes made to this page
   */
  public function numPendingChanges($user){
    $this->checkIsOwner($user);
    $num = $this->database->queryNumChangesByPage($this->id,null/*not approved*/);
    return $num;
  }
  
  /**
   *Retrieves the change info and the actual diff
   */
  public function loadChange($cid, $user){
    $this->checkIsOwner($user);
    $array = $this->database->queryChangeByID($cid);
    return $array;
  }
  
  /**
   * Approves or rejects a pending change. Approved is a boolean.
   * Rebuilds content cache from the diffs library.
   */
  public function setChangeApproved($changeId, $approved) {
    $this->database->updateChangeApproved($changeId, $approved);

    if ($approved) {
      $this->rebuildContent();
    }
  }

  /**
   * Queries for page content. Page content is cached if queried more than
   * once.
   */
  public function queryContent() {

    if ($this->content == null) {
      $this->content = $this->database->queryPageCachedData($this->id);
    }

    return $this->content;
  }

  /**
   * Queries keywords associated with this page and returns them as an
   * array of strings.
   */
  public function queryKeywords() {
    $keywordsArrays = $this->database->queryKeywordsByPageId($this->id);
    $keywordStrArray = array();

    if ($keywordsArrays == null) {
      return null;
    }

    // Convert array of records to an array of keyword strings.
    foreach ($keywordsArrays as $keyword) {
      $keywordStrArray[] = $keyword[1];
    }

    return $keywordStrArray;
  }

  /**
   * Rebuilds page from diffs. This method is called any time a new change is
   * approved.
   */
  private function rebuildContent() {
    // Gets all the changes associated with this page.
    $diffsArray = $this->database->queryApprovedChangesDiffs($this->id, true);

    // Build the page content from the diffs history.
    $content = DeltaEncoder::assembleDeltas($diffsArray);

    // Store the cached content in database.
    $this->database->updatePageCachedData($this->id, $content);

    // Store the cached content in this object.
    $this->content = $content;
  }

  private function __construct() {
    // uhhh, maybe do something. At the moment everything useful happens in
    // the static construction methods.
  }
  
  private function checkIsOwner($user){
    if($user != $this->owner){
      throw new AppException("You cannot view pending changes if you are not the owner");
    }
  }
}

?>
