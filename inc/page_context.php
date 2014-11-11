<?php

require_once('config.php');
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

    $this->database = $database;
    $this->id = $pageId;

    // See queryPageById in database.php for schema info.
    $record = $this->database->queryPageById($this->id);

    // Save record to the class fields.
    $this->title = $record[1];
    $this->owner = $record[2];
    $this->id = $record[3];

    return $pageContext;
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
   * Rebuilds page from diffs. This method is called any time a new change is
   * approved.
   */
  private function rebuildContent() {
    // Gets all the changes associated with this page.
    $diffsArray = $this->database->queryApprovedChangesDiffs($this->id, true);

    echo var_dump($diffsArray);
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
}

?>
