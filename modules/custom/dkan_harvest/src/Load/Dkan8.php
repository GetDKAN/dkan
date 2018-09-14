<?php

namespace Drupal\dkan_harvest\Load;

use Drupal\dkan_harvest\Load;

class Dkan8 extends Load {

  function run($items) {
    $this->log->write('DEBUG', 'Load', 'Loading from Dkan8.');
    foreach ($items as $collection => $item) {
      switch ($collection) {

      }
    }
  }

  function generateHash($doc) {
  }

  function checkHash($hash, $identifier) {
  }

  function datasetObjectBase() {
    $node = [
      'type' => 'dataset',
    ];
  }

  function orgObjectBase() {
    $node = [
      'type' => 'organization',
    ];
  }
  function keywordObjectBase() {
    $term = [
      'type' => 'dataset',
    ];
  }
  function themeObjectBase() {
    $term = [
      'type' => 'dataset',
    ];
  }
  function licenseObjectBase() {
    $term = [
      'type' => 'dataset',
    ];
  }

}
