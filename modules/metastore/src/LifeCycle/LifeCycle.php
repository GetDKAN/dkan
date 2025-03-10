<?php

namespace Drupal\metastore\LifeCycle;

use Drupal\common\EventDispatcherTrait;
use Drupal\common\DataResource;
use Drupal\common\Exception\DataNodeLifeCycleEntityValidationException;
use Drupal\common\UrlHostTokenResolver;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\metastore\MetastoreItemInterface;
use Drupal\metastore\Reference\Dereferencer;
use Drupal\metastore\Reference\MetastoreUrlGenerator;
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
  public function go(string $stage, MetastoreItemInterface $data): void {
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
   * Pre-process distribution node on load.
   *
   * Translate resource ID to downloadUrl, and translate internal DKAN URI
   * for data dictionary to absolute URL.
   *
   * @param \Drupal\metastore\MetastoreItemInterface $data
   *   Distribution Metastore item.
   *
   * @todo For consistency, this should either be abstracted so that it is not
   * so tightly coupled with the distribution schema, or we should better
   * document that DKAN only supports DCAT standard.
   */
  protected function distributionLoad(MetastoreItemInterface $data) {
    $metadata = $data->getMetaData();

    if (!isset($metadata->data->downloadURL)) {
      return;
    }

    $downloadUrl = $metadata->data->downloadURL;

    if (!empty($downloadUrl) && filter_var($downloadUrl, FILTER_VALIDATE_URL) === FALSE) {
      $ref = NULL;
      $original = NULL;
      [$ref, $original] = $this->retrieveDownloadUrlFromResourceMapper($downloadUrl);

      $downloadUrl = $original ?? "";

      $refProperty = "%Ref:downloadURL";
      $metadata->data->{$refProperty} = count($ref) == 0 ? NULL : $ref;
    }

    if (is_string($downloadUrl)) {
      $downloadUrl = UrlHostTokenResolver::resolve($downloadUrl);
    }
    $metadata->data->downloadURL = $downloadUrl;

    // If describedBy contains dkan:// URI, convert to absolute URL.
    if (StreamWrapperManager::getScheme($metadata->data->describedBy ?? '') == MetastoreUrlGenerator::DKAN_SCHEME) {
      $metadata->data->describedBy = $this->referencer->metastoreUrlGenerator->absoluteString($metadata->data->describedBy);
    }
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
   *
   * @throws \Exception
   */
  protected function datasetPresave(MetastoreItemInterface $data): void {
    $this->setNodeValuesFromMetadata($data);
    $this->referenceMetadata($data);

    if (!$data->isNew()) {
      try {
        $this->queueOrphanReferenceCleanup($data);
      }
      catch (InvalidPluginDefinitionException | PluginNotFoundException | DataNodeLifeCycleEntityValidationException $e) {
        throw new \Exception($e->getMessage());
      }
    }
  }

  /**
   * Trigger datastore import and reference metadata with uuids.
   *
   * @param \Drupal\metastore\MetastoreItemInterface $data
   *   Metastore item.
   *
   * @throws \Exception
   */
  protected function referenceMetadata(MetastoreItemInterface $data): void {
    $metadata = $data->getMetadata();

    // Trigger datastore import if applicable.
    // Needs to happen before updating references.
    $this->dispatchEvent(self::EVENT_PRE_REFERENCE, $data, function ($data) {
      return $data instanceof MetastoreItemInterface;
    });

    // Convert references in metadata to uuids.
    // Create new reference entities if they do not exist.
    $metadata = $this->referencer->reference($metadata);

    // Re-add metadata to data object with uuids.
    $data->setMetadata($metadata);
  }

  /**
   * Orphan removed references if applicable.
   *
   * @param \Drupal\metastore\MetastoreItemInterface $data
   *   Metastore item.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\common\Exception\DataNodeLifeCycleEntityValidationException
   */
  protected function queueOrphanReferenceCleanup(MetastoreItemInterface $data): void {
    $metadata = $data->getMetadata();

    // Check for possible orphan property references when updating a dataset.
    // Compare with the latest revision (saved as raw metadata).
    $raw = $data->getRawMetadata();
    $this->orphanChecker->processReferencesInUpdatedDataset($raw, $metadata);

    // Are we publishing this new revision?
    $state = $data->getModerationState();

    // If publishing a previous draft, check for orphans
    // from last published version.
    if ($state == 'published') {
      // Get last published version.
      $published = $data->getPublishedRevision();

      // Get latest revision ID.
      $latestVid = $data->getLoadedRevisionId();

      // Only proceed if latest revision was NOT the published revision.
      if ($published && $published->getRevisionId() <> $latestVid) {
        // Get the raw referenced metadata.
        $published_metadata = $published->getRawMetadata();
        $this->orphanChecker->processReferencesInUpdatedDataset($published_metadata, $metadata);
      }
    }
  }

  /**
   * Set required node values based on metadata.
   *
   * @param \Drupal\metastore\MetastoreItemInterface $data
   *   Data-Dictionary metastore item.
   */
  protected function setNodeValuesFromMetadata(MetastoreItemInterface $data): void {
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
  }

  /**
   * Data-Dictionary pre-save life cycle method.
   *
   * @param \Drupal\metastore\MetastoreItemInterface $data
   *   Data-Dictionary metastore item.
   */
  protected function datadictionaryPresave(MetastoreItemInterface $data): void {
    $metadata = $data->getMetaData();

    $title = $metadata->data->title;
    $data->setTitle($title);

    // If there is no uuid add one.
    if (!isset($metadata->identifier)) {
      $metadata->identifier = $data->getIdentifier();
    }
    // If one exists in the uuid it should be the same in the table.
    else {
      $data->setIdentifier($metadata->identifier);
    }
    $data->setMetadata($metadata);
  }

  /**
   * Distribution presave.
   *
   * @param \Drupal\metastore\MetastoreItemInterface $data
   *   Dataset metastore item.
   */
  protected function distributionPresave(MetastoreItemInterface $data): void {
    $metadata = $data->getMetaData();

    // If updating an existing distribution, re-reference it.
    if (!$data->isNew()) {
      $distributionUuid = $data->getIdentifier();
      $storage = $this->dataFactory->getInstance('distribution');
      $resource = $storage->retrieve($distributionUuid);
      $resource = json_decode($resource);

      $resourceId = $resource->data->{'%Ref:downloadURL'}[0]->data->identifier ?? NULL;

      // Replace download url with the resource reference ID again.
      if (isset($resourceId)) {
        $perspective = $resource->data->{'%Ref:downloadURL'}[0]->data->perspective ?? NULL;
        $version = $resource->data->{'%Ref:downloadURL'}[0]->data->version ?? NULL;
        $metadata->data->downloadURL = $resourceId . '__' . $version . '__' . $perspective;
        unset($metadata->data->{'%Ref:downloadURL'});
      }
    }
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
