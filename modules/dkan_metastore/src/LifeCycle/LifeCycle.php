<?php

namespace Drupal\dkan_metastore\LifeCycle;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Queue\QueueFactory;
use Drupal\dkan_common\Exception\DataNodeLifeCycleEntityValidationException;
use Drupal\dkan_common\Events\Event;
use Drupal\dkan_metastore\MetastoreItemInterface;
use Drupal\dkan_metastore\Reference\Dereferencer;
use Drupal\dkan_metastore\Reference\OrphanChecker;
use Drupal\dkan_metastore\Reference\Referencer;
use Drupal\dkan_metastore\Storage\DataFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

  const EVENT_DATASET_UPDATE = 'dkan_metastore_dataset_update';
  const EVENT_PRE_REFERENCE = 'dkan_metastore_metadata_pre_reference';
  const EVENT_DELETING_DISTRIBUTION = 'dkan_metastore_deleting_distribution';

  /**
   * Constructor.
   *
   * @param \Drupal\dkan_metastore\Reference\Referencer $referencer
   *   The dkan.metastore.referencer service.
   * @param \Drupal\dkan_metastore\Reference\Dereferencer $dereferencer
   *   The dkan.metastore.dereferencer service.
   * @param \Drupal\dkan_metastore\Reference\OrphanChecker $orphanChecker
   *   The dkan.metastore.orphan_checker service.
   * @param \Drupal\Core\Datetime\DateFormatter $dateFormatter
   *   The date.formatter service.
   * @param \Drupal\dkan_metastore\Storage\DataFactory $dataFactory
   *   The dkan.metastore.data_factory service.
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   The queue.factory service.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   The config.factory service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event_dispatcher service.
   */
  public function __construct(
    protected Referencer $referencer,
    protected Dereferencer $dereferencer,
    protected OrphanChecker $orphanChecker,
    protected DateFormatter $dateFormatter,
    protected DataFactory $dataFactory,
    protected QueueFactory $queueFactory,
    protected ConfigFactory $configFactory,
    protected EventDispatcherInterface $eventDispatcher,
  ) {
  }

  /**
   * Entry point for LifeCycle functions.
   *
   * Based on the schema of the $data object and the $stage, we generate a
   * method name. If that method name exists on this class, we call it.
   * Example: Stage 'load' for a dataset metastore item becomes 'datasetLoad'.
   *
   * Currently, this method handles hook implementations for Data nodes via
   * wrappers, but might be expected to handle arbitrary entities in the
   * future.
   *
   * @param string $stage
   *   Stage or hook name for execution.
   * @param \Drupal\dkan_metastore\MetastoreItemInterface $data
   *   Metastore item object.
   */
  public function go(string $stage, MetastoreItemInterface $data): void {
    // Removed dashes from schema ID since function names can't include dashes.
    $schema_id = str_replace('-', '', $data->getSchemaId());
    // Build method name from schema ID and stage, ensure it exists for this
    // life cycle stage.
    if (method_exists($this, $method = $schema_id . ucwords($stage))) {
      // Call life cycle method on metastore item.
      $this->$method($data);
    }
  }

  /**
   * Dataset preDelete.
   */
  protected function datasetPredelete(MetastoreItemInterface $data): void {
    $raw = $data->getRawMetadata();

    if (is_object($raw)) {
      $this->orphanChecker->processReferencesInDeletedDataset($raw);
    }
  }

  /**
   * Dataset load.
   *
   * @todo This behavior should be on-demand instead of always happening when
   *   the node loads, since not all dataset nodes will need to be
   *   dereferenced.
   *
   * @see \metastore_node_load()
   */
  protected function datasetLoad(MetastoreItemInterface $data): void {
    $metadata = $data->getMetaData();

    // Dereference dataset properties.
    $metadata = $this->dereferencer->dereference($metadata);
    $metadata = $this->addDatasetModifiedDate($metadata, $data->getModifiedDate());

    $data->setMetadata($metadata);
  }

  /**
   * Purge resources (if unneeded) of any updated dataset.
   */
  protected function datasetUpdate(MetastoreItemInterface $data): void {
    $event = new Event($data);
    $this->eventDispatcher->dispatch($event, self::EVENT_DATASET_UPDATE);
  }

  /**
   * Pre-process distribution node on load.
   *
   * Translate resource ID to downloadUrl, and translate internal DKAN URI
   * for data dictionary to absolute URL.
   *
   * @param \Drupal\dkan_metastore\MetastoreItemInterface $data
   *   Distribution Metastore item.
   *
   * @todo For consistency, this should either be abstracted so that it is not
   * so tightly coupled with the distribution schema, or we should better
   * document that DKAN only supports DCAT standard.
   *
   * @todo This behavior should be on-demand instead of always happening when
   *   the node loads, since not all node loads will need dereferenced download
   *   URLs.
   *
   * @see \metastore_node_load()
   */
  protected function distributionLoad(MetastoreItemInterface $data): void {
    $metadata = $data->getMetaData();
    $this->dereferencer->dereferenceResource($metadata->data);
    $this->dereferencer->dereferenceDataDictionary($metadata->data);
    $data->setMetadata($metadata);
  }

  /**
   * Distribution predelete.
   */
  protected function distributionPredelete(MetastoreItemInterface $data): void {
    $distributionUuid = $data->getIdentifier();

    $event = new Event($distributionUuid);
    $this->eventDispatcher->dispatch($event, self::EVENT_DELETING_DISTRIBUTION);

  }

  /**
   * Dataset pre-save life cycle method.
   *
   * @param \Drupal\dkan_metastore\MetastoreItemInterface $data
   *   Dataset metastore item.
   *
   * @throws \Exception
   */
  protected function datasetPresave(MetastoreItemInterface $data): void {
    $storage = $this->dataFactory->getInstance('dataset');
    $metadata = $storage->filterHtml($data->getMetadata());
    $data->setMetadata($metadata);
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
   * @param \Drupal\dkan_metastore\MetastoreItemInterface $data
   *   Metastore item.
   *
   * @throws \Exception
   */
  protected function referenceMetadata(MetastoreItemInterface $data): void {
    $metadata = $data->getMetadata();

    // Trigger datastore import if applicable.
    // Needs to happen before updating references.
    if ($data instanceof MetastoreItemInterface) {
      $event = new Event($data);
      $this->eventDispatcher->dispatch($event, self::EVENT_PRE_REFERENCE);
    }

    // Convert references in metadata to uuids.
    // Create new reference entities if they do not exist.
    $metadata = $this->referencer->reference($metadata);

    // Re-add metadata to data object with uuids.
    $data->setMetadata($metadata);
  }

  /**
   * Orphan removed references if applicable.
   *
   * @param \Drupal\dkan_metastore\MetastoreItemInterface $data
   *   Metastore item.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\dkan_common\Exception\DataNodeLifeCycleEntityValidationException
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
   * @param \Drupal\dkan_metastore\MetastoreItemInterface $data
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
   * @param \Drupal\dkan_metastore\MetastoreItemInterface $data
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
   * @param \Drupal\dkan_metastore\MetastoreItemInterface $data
   *   Dataset metastore item.
   */
  protected function distributionPresave(MetastoreItemInterface $data): void {
    $metadata = $data->getMetaData();
    $this->referencer->referenceResource($metadata->data);
    $this->referencer->referenceDataDictionary($metadata->data);
    $data->setMetadata($metadata);
  }

  /**
   * Add a modified date value to the metadata as a '%modified' property.
   *
   * @param object $metadata
   *   The metadata object to add the modified date to.
   * @param string $date
   *   The modified date to add to the metadata.
   *
   * @return object
   *   The metadata object with the modified date added.
   */
  private function addDatasetModifiedDate($metadata, $date) {
    $formattedChangedDate = $this->dateFormatter->format($date, 'html_datetime');
    $metadata->{'%modified'} = $formattedChangedDate;
    return $metadata;
  }

}
