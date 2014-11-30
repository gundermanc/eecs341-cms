<?php

require_once('inc/config.php');

// Autoredirect to the homepage.
header("location: " . Config::APP_ROOT . "/pages");

?>
