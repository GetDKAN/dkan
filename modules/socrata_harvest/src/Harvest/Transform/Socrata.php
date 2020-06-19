<?php

namespace Drupal\socrata_harvest\Harvest\Transform;

use Harvest\ETL\Transform\Transform;

/**
 * Class Socrata.
 */
class Socrata extends Transform {
  /**
   * Inherited.
   *
   * {@inheritDoc}
   */
  public function run($item) {
    // Convert URL identifier to just the ID.
    $identifier = $item->identifier;
    $item->identifier = $this->getIdentifier($identifier);

    // Add a keyword when keywords are null.
    if (empty($item->keyword)) {
      $item->keyword = ['No keywords provided'];
    }

    // Add a description if null.
    if (empty($item->description)) {
      $item->description = 'No description provided';
    }

    // Provide publisher name.
    $publisher = $item->publisher;
    if (!isset($publisher->name) && $publisher->source) {
      $publisher->name = $publisher->source;
    }

    // Add titles for csv distributions.
    if ($item->distribution) {
      foreach ($item->distribution as $key => $dist) {
        if ($dist->mediaType != "text/csv") {
          unset($item->distribution[$key]);
        }
        else {
          $dist->title = "{$item->identifier}.csv";
          $item->distribution[$key] = $dist;
        }
      }
    }

    return $item;
  }

  /**
   * Private.
   */
  private function getIdentifier($identifier) {
    $path = parse_url($identifier, PHP_URL_PATH);
    $path = str_replace('/api/views/', "", $path);
    return $path;
  }

}
