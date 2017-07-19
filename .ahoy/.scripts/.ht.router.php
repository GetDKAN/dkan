<?php

/**
 * @file
 * Routing-script for the built-in PHP web server.
 *
 * The built-in webserver should only be used for development and testing as it
 * has a number of limitations that makes running Drupal on it highly insecure
 * and somewhat limited.
 *
 * In particular be aware that:
 *   - The server is single-threaded, any requests made during the execution of
 *     the main request will hang until the main request has been completed.
 *   - The webserver does not enforce any of the settings in .htaccess in
 *     particular a remote user will be able to download files that normally
 *     would be protected from direct access such as .module files.
 *
 * Usage:
 * php -S localhost:8888 .ht.router.php
 *
 * @see http://php.net/manual/en/features.commandline.webserver.php
 */

$url = parse_url($_SERVER['REQUEST_URI']);
if (file_exists('.' . $url['path'])) {
  // Serve the requested resource as-is.
  return FALSE;
}
// The use of a router-script means that a number of $_SERVER variables has to
// be updated to point to the index-file.
$index_file_absolute = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'index.php';
$index_file_relative = DIRECTORY_SEPARATOR . 'index.php';
// SCRIPT_FILENAME will point to the router-script itself, it should point to
// the full path to index.php.
$_SERVER['SCRIPT_FILENAME'] = $index_file_absolute;
// SCRIPT_NAME and PHP_SELF will either point to /index.php or contain the full
// virtual path being requested depending on the url being requested. They
// should always point to index.php relative to document root.
$_SERVER['SCRIPT_NAME'] = $index_file_relative;
$_SERVER['PHP_SELF'] = $index_file_relative;
// Require the main index-file and let core take over.
require $index_file_absolute;
