<?php

namespace Drupal\metastore\LifeCycle;

use Drupal\common\Resource;
use Drupal\common\UrlHostTokenResolver;
use Drupal\metastore\Events\PreReference;
use Drupal\metastore\Traits\ResourceMapperTrait;

/**
 * Data.
 */
class Data extends AbstractData {
  use ResourceMapperTrait;

  const EVENT_PRE_REFERENCE = 'dkan_metastore_metadata_pre_reference';

  /**
   * Load.
   */
  public function load() {
    $this->go('Load');
  }

  /**
   * Presave.
   *
   * Activities to move a data node through during presave.
   */
  public function presave() {
    $this->go('Presave');
  }

  /**
   * Predelete.
   */
  public function predelete() {
    $this->go('Predelete');
  }

  /**
   * Protected.
   */
  protected function datasetPredelete() {
    $raw = $this->data->getRawMetadata();

    if (is_object($raw)) {
      $referencer = \Drupal::service("dkan.metastore.orphan_checker");
      $referencer->processReferencesInDeletedDataset($raw);
    }
  }

  /**
   * Private.
   */
  protected function datasetLoad() {
    $metadata = $this->data->getMetaData();

    // Dereference dataset properties.
    $dereferencer = \Drupal::service("dkan.metastore.dereferencer");
    $metadata = $dereferencer->dereference($metadata);
    $metadata = $this->addNodeModifiedDate($metadata);

    $this->data->setMetadata($metadata);
  }

  /**
   * Private.
   *
   * @todo Decouple "resource" functionality from specific dataset properties.
   */
  protected function distributionLoad() {
    $metadata = $this->data->getMetaData();

    if (!isset($metadata->data->downloadURL)) {
      return;
    }

    $downloadUrl = $metadata->data->downloadURL;

    if (isset($downloadUrl) && !filter_var($downloadUrl, FILTER_VALIDATE_URL)) {
      $resourceIdentifier = $downloadUrl;
      $ref = NULL;
      $original = NULL;
      [$ref, $original] = $this->retrieveDownloadUrlFromResourceMapper($resourceIdentifier);

      $downloadUrl = isset($original) ? $original : "";

      $refProperty = "%Ref:downloadURL";
      $metadata->data->{$refProperty} = count($ref) == 0 ? NULL : $ref;
    }

    if (is_string($downloadUrl)) {
      $downloadUrl = UrlHostTokenResolver::resolve($downloadUrl);
    }

    $metadata->data->downloadURL = $downloadUrl;

    $this->data->setMetadata($metadata);
  }

  /**
   * Private.
   */
  private function retrieveDownloadUrlFromResourceMapper($resourceIdentifier) {
    $reference = [];
    $original = NULL;

    $fileMapperInfo = Resource::parseUniqueIdentifier($resourceIdentifier);

    /** @var \Drupal\common\Resource $sourceResource */
    $sourceResource = $this->getFileMapper()->get($fileMapperInfo['identifier'],
      Resource::DEFAULT_SOURCE_PERSPECTIVE, $fileMapperInfo['version']);
    if (!$sourceResource) {
      return [$reference, $original];
    }

    $reference[] = $this->createResourceReference($sourceResource);

    $perspective = resource_mapper_display();

    /** @var \Drupal\common\Resource $sourceResource */
    $resource = $sourceResource;

    if ($perspective != Resource::DEFAULT_SOURCE_PERSPECTIVE) {
      $new = $this->getFileMapper()->get(
        $fileMapperInfo['identifier'],
        $perspective,
        $fileMapperInfo['version']);
      if ($new) {
        $resource = $new;
        $reference[] = $this->createResourceReference($resource);
      }
    }
    $original = $resource->getFilePath();

    return [$reference, $original];
  }

  /**
   * Private.
   */
  private function createResourceReference(Resource $resource): object {
    return (object) [
      "identifier" => $resource->getUniqueIdentifier(),
      "data" => $resource,
    ];
  }

  /**
   * Private.
   */
  protected function datasetPresave() {
    $metadata = $this->data->getMetaData();

    $title = isset($metadata->title) ? $metadata->title : $metadata->name;
    $this->data->setTitle($title);

    // If there is no uuid add one.
    if (!isset($metadata->identifier)) {
      $metadata->identifier = $this->data->getIdentifier();
    }
    // If one exists in the uuid it should be the same in the table.
    else {
      $this->data->setIdentifier($metadata->identifier);
    }

    /** @var \Symfony\Component\EventDispatcher\EventDispatcher $eventDispatcher */
    $eventDispatcher = \Drupal::service("event_dispatcher");
    $eventDispatcher->dispatch(self::EVENT_PRE_REFERENCE,
      new PreReference($this->data));

    $referencer = \Drupal::service("dkan.metastore.referencer");
    $metadata = $referencer->reference($metadata);

    $this->data->setMetadata($metadata);

    // Check for possible orphan property references when updating a dataset.
    if (!$this->data->isNew()) {
      $raw = $this->data->getRawMetadata();
      $orphanChecker = \Drupal::service("dkan.metastore.orphan_checker");
      $orphanChecker->processReferencesInUpdatedDataset(
        $raw,
        $metadata
      );
    }

  }

  /**
   * Private.
   */
  protected function distributionPresave() {
    $metadata = $this->data->getMetaData();
    $this->data->setMetadata($metadata);
  }

  /**
   * Private.
   */
  private function addNodeModifiedDate($metadata) {
    $formattedChangedDate = \Drupal::service('date.formatter')
      ->format($this->data->getModifiedDate(), 'html_date');
    $metadata->{'%modified'} = $formattedChangedDate;
    return $metadata;
  }

}
