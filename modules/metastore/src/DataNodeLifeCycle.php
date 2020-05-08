<?php

namespace Drupal\metastore;

use Drupal\common\AbstractDataNodeLifeCycle;
use Drupal\common\UrlHostTokenResolver;

/**
 * DataNodeLifeCycle.
 */
class DataNodeLifeCycle extends AbstractDataNodeLifeCycle {

  /**
   * Presave.
   *
   * Activities to move a data node through during presave.
   */
  public function presave() {

    if (empty($this->getDataType())) {
      $this->setDataType('dataset');
    }

    switch ($this->getDataType()) {
      case 'dataset':
        $this->datasetPresave();
        break;

      case 'distribution':
        $this->distributionPresave();
        break;
    }
  }

  /**
   * Private.
   */
  private function datasetPresave() {
    /* @var $entity \Drupal\node\Entity\Node */
    $entity = $this->node;

    $metadata = $this->getMetaData();

    $title = isset($metadata->title) ? $metadata->title : $metadata->name;

    $entity->setTitle($title);

    // If there is no uuid add one.
    if (!isset($metadata->identifier)) {
      $metadata->identifier = $entity->uuid();
    }
    // If one exists in the uuid it should be the same in the table.
    else {
      $entity->set('uuid', $metadata->identifier);
    }

    $referencer = \Drupal::service("metastore.referencer");
    $metadata = $referencer->reference($this->getMetaData());
    $this->setMetadata($metadata);

    // Check for possible orphan property references when updating a dataset.
    if (isset($entity->original)) {
      $orphanChecker = \Drupal::service("metastore.orphan_checker");
      $orphanChecker->processReferencesInUpdatedDataset(
        json_decode($entity->referenced_metadata),
        $metadata
      );
    }
  }

  /**
   * Private.
   */
  private function distributionPresave() {
    $metadata = $this->getMetaData();
    $host = \Drupal::request()->getHost();
    if (isset($metadata->data->downloadURL)) {
      $newUrl = $metadata->data->downloadURL;
      $parsedUrl = parse_url($newUrl);
      if ($parsedUrl['host'] == $host) {
        $parsedUrl['host'] = UrlHostTokenResolver::TOKEN;
        $metadata->data->downloadURL = $this->unparseUrl($parsedUrl);
        $this->setMetadata($metadata);
      }
    }

  }

  /**
   * Private.
   */
  private function unparseUrl($parsedUrl) {
    $url = '';
    $urlParts = [
      'scheme',
      'host',
      'port',
      'user',
      'pass',
      'path',
      'query',
      'fragment',
    ];

    foreach ($urlParts as $part) {
      if (!isset($parsedUrl[$part])) {
        continue;
      }
      $url .= ($part == "port") ? ':' : '';
      $url .= ($part == "query") ? '?' : '';
      $url .= ($part == "fragment") ? '#' : '';
      $url .= $parsedUrl[$part];
      $url .= ($part == "scheme") ? '://' : '';
    }

    return $url;
  }

}
