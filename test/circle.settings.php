<?php

$databases = array (
  'default' =>
  array (
    'default' =>
    array (
      'database' => 'circle_test',
      'username' => 'ubuntu',
      'password' => '',
      'host' => '127.0.0.1',
      'port' => '3306',
      'driver' => 'mysql',
      'prefix' => '',
      'pdo' => array(
        PDO::MYSQL_ATTR_LOCAL_INFILE => 1,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => 1,
      )
    ),
  ),
);

// Conditionally manage memory.
$high_memory_paths = array();

// ODSM edit forms.
$high_memory_paths[] = 'admin/config/services/odsm/edit';
// Standarize node edit paths for validation.
$current_path = preg_replace("/\/\d+/", '/%node', $_GET['q']);
foreach ($high_memory_paths as $high_memory_path) {
  if ((strpos($current_path, $high_memory_path) === 0)) {
    ini_set('memory_limit', '512M');
  }
}
