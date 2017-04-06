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
