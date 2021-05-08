<?php

namespace Drupal\metastore\Storage;

use Drupal\Core\Entity\EntityTypeManager;

/**
 * Data.
 */
abstract class AbstractEntityStorage {

  const EVENT_DATASET_UPDATE = 'dkan_metastore_dataset_update';

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Represents the data type passed via the HTTP request url schema_id slug.
   *
   * @var string
   */
  protected $schemaId;

  /**
   * Entity storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|mixed|object
   */
  protected $entityStorage;

  /**
   * Entity label key.
   *
   * @var string
   */
  protected $labelKey;

  /**
   * Entity bundle key.
   *
   * @var string
   */
  protected $bundleKey;

  /**
   * Constructor.
   */
  public function __construct(string $schemaId, EntityTypeManager $entityTypeManager, $entityType) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityStorage = $this->entityTypeManager->getStorage($entityType);
    $this->schemaId = $schemaId;

    $this->bundleKey = $this->entityStorage->getEntityType()->getKey('bundle');
    $this->labelKey = $this->entityStorage->getEntityType()->getKey('label');
  }

  /**
   * Get entity storage.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface|mixed|object
   *   Entity storage.
   */
  public function getEntityStorage() {
    return $this->entityStorage;
  }
 
  /**
   * Load a Data entity's published revision.
   *
   * @param string $uuid
   *   The dataset identifier.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity's published revision, if found.
   */
  public function getEntityPublishedRevision(string $uuid) {

    $entity_id = $this->getEntityIdFromUuid($uuid);
    // TODO: extract an actual published revision.
    return $entity_id ? $this->entityStorage->load($entity_id) : NULL;
  }

  /**
   * Load a entity's latest revision, given a dataset's uuid.
   *
   * @param string $uuid
   *   The dataset identifier.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity's latest revision, if found.
   */
  public function getEntityLatestRevision(string $uuid) {

    $entity_id = $this->getEntityIdFromUuid($uuid);

    if ($entity_id) {
      $revision_id = $this->entityStorage->getLatestRevisionId($entity_id);
      return $this->entityStorage->loadRevision($revision_id);
    }

    return NULL;
  }

  /**
   * Get the entity id from the dataset identifier.
   *
   * @param string $uuid
   *   The dataset identifier.
   *
   * @return int|null
   *   The entity id, if found.
   */
  public function getEntityIdFromUuid(string $uuid) : ?int {

    $entity_ids = $this->entityStorage->getQuery()
      ->condition('uuid', $uuid)
      ->execute();

    return $entity_ids ? (int) reset($entity_ids) : NULL;
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function remove(string $uuid) {

    $entity = $this->getEntityLatestRevision($uuid);
    if ($entity) {
      return $entity->delete();
    }
  }

  /**
   * Recursively filter the metadata object and all its properties.
   *
   * @param mixed $input
   *   Unfiltered input.
   *
   * @return mixed
   *   Filtered output.
   */
  protected function filterHtml($input) {
    switch (gettype($input)) {
      case "string":
        return $this->htmlPurifier($input);

      case "array":
      case "object":
        foreach ($input as &$value) {
          $value = $this->filterHtml($value);
        }
        return $input;

      default:
        // Leave integers, floats or boolean unchanged.
        return $input;
    }
  }

  /**
   * Run a string through HTMLPurifier.
   *
   * Extracted to facilitate unit-testing because of the "new".
   *
   * @param string $input
   *   Unfiltered string.
   *
   * @return string
   *   Filtered string.
   *
   * @codeCoverageIgnore
   */
  private function htmlPurifier(string $input) {
    $filter = new \HTMLPurifier();
    return $filter->purify($input);
  }

  /**
   * Returns the current time, formatted.
   *
   * @return string
   *   Current timestamp, formatted.
   */
  protected function formattedTimestamp() : string {
    $now = new \DateTime('now');
    return $now->format(\DateTime::ATOM);
  }

  /**
   * Return the default moderation state of our custom dkan_publishing workflow.
   *
   * @return string
   *   Either 'draft', 'published' or 'orphaned'.
   */
  public function getDefaultModerationState() {
    return $this->entityTypeManager->getStorage('workflow')
      ->load('dkan_publishing')
      ->getTypePlugin()
      ->getConfiguration()['default_moderation_state'];
  }

}
