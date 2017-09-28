<?php

/**
 * @file
 * Bootstraps Drupal 7 site.
 */

use Drupal\Driver\DrupalDriver;
use Drupal\Driver\Cores\Drupal7;

require __DIR__ . '/../vendor/autoload.php';

// Path to Drupal.
$dir = implode('/', array(__DIR__, '..', '..', '..', 'docroot'));

// Host.
$uri = getenv('DKAN_WEB_1_PORT_80_TCP_ADDR') ? 'http://' . getenv('DKAN_WEB_1_PORT_80_TCP_ADDR') : 'http://127.0.0.1:8888';

$driver = new DrupalDriver($dir, $uri);
$driver->setCoreFromVersion();

// Bootstrap Drupal.
$driver->bootstrap();
