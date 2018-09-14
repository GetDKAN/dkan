<?php

/**
 * @file
 *
 * Rough draft of steps for harvesting.
 */

// This won't be necessary if called from within Drupal.
require_once('../../dkan_schema/src/Schema.php');
require_once('../../../../../../autoload.php');
require_once('Extract.php');
require_once('Harvest.php');
require_once('Extract/DataJson.php');
require_once('Transform.php');
require_once('Transform/Filter.php');
require_once('Transform/Override.php');
require_once('Transform/DataJsonToDkan.php');
require_once('Load.php');
require_once('Load/Dkan8.php');
require_once('Log.php');
require_once('Log/File.php');
require_once('Log/Stdout.php');
require '../vendor/autoload.php';


use Drupal\dkan_schema\Schema;
use Drupal\dkan_harvest\Harvest;

$schema = new Schema('pod-light');
$collections = $schema->getActiveCollections();

// Example config and harvests.
$config = json_decode(file_get_contents('harvest-example-config.json'));
$harvests = json_decode(file_get_contents('harvest-example.json'));

$Harvest = new Harvest($config);
foreach ($harvests as $harvest) {
  $Harvest->init($harvest);
  //$Harvest->cache();
  $items = $Harvest->extract();
  $items = $Harvest->transform($items);
  //$Harvest->load($items);
}

