<?php
/**
 * @file
 * Bootstraps Drupal 7 site.
 */

use Drupal\Driver\DrupalDriver;
use Drupal\Driver\Cores\Drupal7;

require __DIR__ . '/../vendor/autoload.php';

// Path to Drupal.
$dir = '/var/www/docroot';

// Host.
$uri = 'http://web';

$driver = new DrupalDriver($dir, $uri);
$driver->setCoreFromVersion();

// Bootstrap Drupal.
$driver->bootstrap();
