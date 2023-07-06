<?php

namespace Drupal\metastore\LifeCycle;

use Drupal\common\EventDispatcherTrait;
use Drupal\common\DataResource;
use Drupal\common\UrlHostTokenResolver;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Queue\QueueFactory;
use Drupal\metastore\MetastoreItemInterface;
use Drupal\metastore\Reference\Dereferencer;
use Drupal\metastore\Reference\OrphanChecker;
use Drupal\metastore\Reference\Referencer;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Storage\DataFactory;

/**
 * Abstraction of logic used in entity hooks.
 *
 * The LifeCycle class contains the logic that is used by our entity hooks, to
 * make changes to the metadata at the time of save or load. To prepare for a
 * move to a custom entity, we abstract out any code that is specific to a
 * certain entity type, bundle or field name, and replace these references with
 * methods that are defined in an interface to be shared with future
 * storage systems.
 */
class LifeCycle {
  use EventDispatcherTrait;

  const EVENT_DATASET_UPDATE = 'dkan_metastore_dataset_update';
  const EVENT_PRE_REFERENCE = 'dkan_metastore_metadata_pre_reference';

  /**
   * Referencer service.
   *
   * @var \Drupal\metastore\Reference\Referencer
   */
  protected $referencer;

  /**
   * Dereferencer.
   *
   * @var \Drupal\metastore\Reference\Dereferencer
   */
  protected $dereferencer;

  /**
   * OrphanChecker service.
   *
   * @var \Drupal\metastore\Reference\OrphanChecker
   */
  protected $orphanChecker;

  /**
   * ResourceMapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected $resourceMapper;

  /**
   * DateFormatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * Metastore storage service.
   *
   * @var \Drupal\metastore\Storage\DataFactory
   */
  protected $dataFactory;

  /**
   * Queue service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructor.
   */
  public function __construct(
    Referencer $referencer,
    Dereferencer $dereferencer,
    OrphanChecker $orphanChecker,
    ResourceMapper $resourceMapper,
    DateFormatter $dateFormatter,
    DataFactory $dataFactory,
    QueueFactory $queueFactory,
    ConfigFactory $configFactory
  ) {
    $this->referencer = $referencer;
    $this->dereferencer = $dereferencer;
    $this->orphanChecker = $orphanChecker;
    $this->resourceMapper = $resourceMapper;
    $this->dateFormatter = $dateFormatter;
    $this->dataFactory = $dataFactory;
    $this->queueFactory = $queueFactory;
    $this->configFactory = $configFactory;
  }

  /**
   * Entry point for LifeCycle functions.
   *
   * @param string $stage
   *   Stage or hook name for execution.
   * @param \Drupal\metastore\MetastoreItemInterface $data
   *   Metastore item object.
   */
  public function go($stage, MetastoreItemInterface $data) {
    // Removed dashes from schema ID since function names can't include dashes.
    $schema_id = str_replace('-', '', $data->getSchemaId());
    $stage = ucwords($stage);
    // Build method name from schema ID and stage.
    $method = "{$schema_id}{$stage}";
    // Ensure a method exists for this life cycle stage.
    if (method_exists($this, $method)) {
      // Call life cycle method on metastore item.
      $this->{$method}($data);
    }
  }

  /**
   * Dataset preDelete.
   */
  protected function datasetPredelete(MetastoreItemInterface $data) {
    $raw = $data->getRawMetadata();

    if (is_object($raw)) {
      $this->orphanChecker->processReferencesInDeletedDataset($raw);
    }
  }

  /**
   * Dataset load.
   */
  protected function datasetLoad(MetastoreItemInterface $data) {
    $metadata = $data->getMetaData();

    // Dereference dataset properties.
    $metadata = $this->dereferencer->dereference($metadata);
    $metadata = $this->addDatasetModifiedDate($metadata, $data->getModifiedDate());

    $data->setMetadata($metadata);
  }

  /**
   * Purge resources (if unneeded) of any updated dataset.
   */
  protected function datasetUpdate(MetastoreItemInterface $data) {
    $this->dispatchEvent(self::EVENT_DATASET_UPDATE, $data);
  }

  /**
   * Private.
   *
   * @todo Decouple "resource" functionality from specific dataset properties.
   */
  protected function distributionLoad(MetastoreItemInterface $data) {
    $metadata = $data->getMetaData();

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

    $data->setMetadata($metadata);
  }

  /**
   * Distribution predelete.
   */
  protected function distributionPredelete(MetastoreItemInterface $data) {
    $distributionUuid = $data->getIdentifier();

    $storage = $this->dataFactory->getInstance('distribution');
    $resource = $storage->retrieve($distributionUuid);
    $resource = json_decode($resource);

    $id = $resource->data->{'%Ref:downloadURL'}[0]->data->identifier ?? NULL;

    // Ensure a valid resource ID was found since it's required.
    if (isset($id)) {
      $perspective = $resource->data->{'%Ref:downloadURL'}[0]->data->perspective ?? NULL;
      $version = $resource->data->{'%Ref:downloadURL'}[0]->data->version ?? NULL;
      $this->queueFactory->get('orphan_resource_remover')->createItem([
        $id,
        $perspective,
        $version,
      ]);
    }
  }

  /**
   * Get a download URL.
   *
   * @param string $resourceIdentifier
   *   Identifier for resource.
   *
   * @return array
   *   Array of reference and original.
   */
  private function retrieveDownloadUrlFromResourceMapper(string $resourceIdentifier) {
    $reference = [];
    $original = NULL;

    $info = DataResource::parseUniqueIdentifier($resourceIdentifier);

    // Load resource object.
    $sourceResource = $this->resourceMapper->get($info['identifier'], DataResource::DEFAULT_SOURCE_PERSPECTIVE, $info['version']);

    if (!$sourceResource) {
      return [$reference, $original];
    }

    $reference[] = $this->createResourceReference($sourceResource);
    $perspective = $this->configFactory->get('metastore.settings')->get('resource_perspective_display')
      ?: DataResource::DEFAULT_SOURCE_PERSPECTIVE;
    $resource = $sourceResource;

    if (
      $perspective != DataResource::DEFAULT_SOURCE_PERSPECTIVE &&
      $new = $this->resourceMapper->get($info['identifier'], $perspective, $info['version'])
    ) {
      $resource = $new;
      $reference[] = $this->createResourceReference($resource);
    }
    $original = $resource->getFilePath();

    return [$reference, $original];
  }

  /**
   * Private.
   */
  private function createResourceReference(DataResource $resource): object {
    return (object) [
      "identifier" => $resource->getUniqueIdentifier(),
      "data" => $resource,
    ];
  }

  /**
   * Dataset pre-save life cycle method.
   *
   * @param \Drupal\metastore\MetastoreItemInterface $data
   *   Dataset metastore item.
   */
  protected function datasetPresave(MetastoreItemInterface $data): void {
    $this->referenceMetadata($data);
  }

  /**
   * Sanitize and reference metadata.
   *
   * @param \Drupal\metastore\MetastoreItemInterface $data
   *   Metastore item.
   */
  protected function referenceMetadata(MetastoreItemInterface $data): void {
    $metadata = $data->getMetaData();

    $title = $metadata->title ?? $metadata->name;
    $data->setTitle($title);

    // If there is no uuid add one.
    if (!isset($metadata->identifier)) {
      $metadata->identifier = $data->getIdentifier();
    }
    // If one exists in the uuid it should be the same in the table.
    else {
      $data->setIdentifier($metadata->identifier);
    }

    $this->dispatchEvent(self::EVENT_PRE_REFERENCE, $data, function ($data) {
      return $data instanceof MetastoreItemInterface;
    });

    $metadata = $this->referencer->reference($metadata);

    $data->setMetadata($metadata);

    // Check for possible orphan property references when updating a dataset.
    if (!$data->isNew()) {
      $raw = $data->getRawMetadata();
      $this->orphanChecker->processReferencesInUpdatedDataset($raw, $metadata);
    }
  }

  /**
   * Data-Dictionary pre-save life cycle method.
   *
   * @param \Drupal\metastore\MetastoreItemInterface $data
   *   Data-Dictionary metastore item.
   */
  protected function datadictionaryPresave(MetastoreItemInterface $data): void {
    $this->referenceMetadata($data);
  }

  /**
   * Private.
   */
  protected function distributionPresave(MetastoreItemInterface $data) {
    $metadata = $data->getMetaData();
    $data->setMetadata($metadata);
  }

  /**
   * Private.
   */
  private function addDatasetModifiedDate($metadata, $date) {
    $formattedChangedDate = $this->dateFormatter->format($date, 'html_datetime');
    $metadata->{'%modified'} = $formattedChangedDate;
    return $metadata;
  }

}
