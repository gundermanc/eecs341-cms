<?php

/**
 * Define parent class for all exceptions.
 * You can catch any exception for the application with a
 * catch (AppException).
 */
class AppException extends Exception {
  public function __construct($message, $code = 0, Exception $previous = null) {
    parent::__construct($message, $code, $previous);
  }

  public function __toString() {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }
}

?>
