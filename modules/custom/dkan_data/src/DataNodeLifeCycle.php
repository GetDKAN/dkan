<?php

namespace Drupal\dkan_data;

use Drupal\Core\Entity\EntityInterface;
use Drupal\dkan_common\UrlHostTokenResolver;
use Drupal\node\Entity\Node;

/**
 * DataNodeLifeCycle.
 */
class DataNodeLifeCycle {
  private $node;

  /**
   * Constructor.
   */
  public function __construct(EntityInterface $entity) {
    $this->validate($entity);
    $this->node = $entity;
  }

  /**
   * Presave.
   *
   * Activities to move a data node through during presave.
   */
  public function presave() {
    /* @var $entity \Drupal\node\Entity\Node */
    $entity = $this->node;

    if (empty($entity->get('field_data_type')->value)) {
      $entity->set('field_data_type', "dataset");
    }

    $dataType = $entity->get('field_data_type')->value;

    switch ($dataType) {
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
    if (empty($entity->field_data_type->value)) {
      $entity->field_data_type->value = "dataset";
    }

    // If there is no uuid add one.
    if (!isset($metadata->identifier)) {
      $metadata->identifier = $entity->uuid();
    }
    // If one exists in the uuid it should be the same in the table.
    else {
      $entity->set('uuid', $metadata->identifier);
    }

    // Reference the dataset's values, and update our json metadata.
    $referencer = \Drupal::service("dkan_data.value_referencer");
    $metadata = $referencer->reference($metadata);
    $this->setMetadata($metadata);

    // Check for possible orphan property references when updating a dataset.
    if (isset($entity->original)) {
      $referencer->processReferencesInUpdatedDataset(
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
    $host = \Drupal::request()->getSchemeAndHttpHost();
    if (isset($metadata->data->downloadURL)) {
      $newUrl = $metadata->data->downloadURL;
      if (substr_count($newUrl, $host) > 0) {
        $parsedUrl = parse_url($newUrl);
        $parsedUrl['host'] = UrlHostTokenResolver::TOKEN;
        $metadata->data->downloadURL = $this->unparseUrl($parsedUrl);
        $this->setMetadata($metadata);
      }
    }

  }

  /**
   * Private.
   */
  private function getMetaData() {
    /* @var $entity \Drupal\node\Entity\Node */
    $entity = $this->node;
    return json_decode($entity->get('field_json_metadata')->value);
  }

  /**
   * Private.
   */
  private function setMetadata($metadata) {
    /* @var $entity \Drupal\node\Entity\Node */
    $entity = $this->node;
    $entity->set('field_json_metadata', json_encode($metadata));
  }

  /**
   * Private.
   */
  private function validate(EntityInterface $entity) {
    if (!($entity instanceof Node)) {
      throw new \Exception("We only work with nodes.");
    }

    if ($entity->bundle() != "data") {
      throw new \Exception("We only work with data nodes.");
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
