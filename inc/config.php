<?php

/**
 * Contains global configuration options for the application.
 * Access these elsewhere with Config::CONST_NAME
 */
class Config {
  // Global Application Name String, printed in UI.
  const APP_NAME = "EECS 341 Project";

  // SQL Server login information.
  const SQL_SERVER = "localhost";
  const SQL_USER = "root";
  const SQL_PASS = "";
  const SQL_DB = "EECS341Db";

  // Password Hashing algorithm.
  const HASH_ALGO = "sha256";

  // Application Admin User account.
  const ADMIN_USER = "root";
  const ADMIN_PASS = "";
}

?>
