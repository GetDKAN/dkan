<?php

namespace Drupal\metastore\Storage;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Service;

/**
 * Abstract metastore storage class, for using Drupal entities.
 */
abstract class Data implements MetastoreStorageInterface {

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
   * Entity type.
   *
   * @var string
   */
  protected $entityType;

  /**
   * Entity bundle.
   *
   * @var string
   */
  protected $bundle;

  /**
   * Constructor.
   */
  public function __construct(string $schemaId, EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityStorage = $this->entityTypeManager->getStorage($this->entityType);
    $this->setSchema($schemaId);
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
   * Private.
   */
  private function setSchema($schemaId) {
    $this->schemaId = $schemaId;
  }

  /**
   * Count objects of this metastore's schema ID.
   *
   * @return int
   *   Object count.
   */
  public function count(): int {
    return $this->entityStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $this->bundle)
      ->condition('field_data_type', $this->schemaId)
      ->count()
      ->execute();
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function retrieveAll(): array {

    $entity_ids = $this->entityStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $this->bundle)
      ->condition('field_data_type', $this->schemaId)
      ->execute();

    $all = [];
    foreach ($entity_ids as $nid) {
      $entity = $this->entityStorage->load($nid);
      if ($entity->get('moderation_state')->getString() === 'published') {
        $all[] = $entity->get('field_json_metadata')->getString();
      }
    }
    return $all;
  }

  /**
   * Get range of object UUIDs of this metastore's schema ID.
   *
   * If no start or length is specified, all dataset UUIDs will be returned.
   *
   * @param int|null $start
   *   Schema object offset or null for all.
   * @param int|null $length
   *   Number of objects to fetch or null for all.
   *
   * @return string[]
   *   Range of object UUIDs of the given schema_id.
   */
  public function retrieveRangeUuids(?int $start = NULL, ?int $length = NULL): array {
    $ids_query = $this->entityStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $this->bundle)
      ->condition('field_data_type', $this->schemaId);

    if (isset($start, $length)) {
      $ids_query = $ids_query->range($start, $length);
    }

    return array_map(function ($entity) {
      return $entity->uuid();
    }, $this->entityStorage->loadMultiple($ids_query->execute()));
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function retrieveRange($start, $length): array {
    $entity_ids = $this->entityStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $this->bundle)
      ->condition('field_data_type', $this->schemaId)
      ->range($start, $length)
      ->execute();

    $all = [];
    foreach ($this->entityStorage->loadMultiple($entity_ids) as $entity) {
      if ($entity->get('moderation_state')->getString() === 'published') {
        $all[] = $entity->get('field_json_metadata')->getString();
      }
    }
    return $all;
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function retrievePublished(string $uuid) : ?string {
    $entity = $this->getEntityPublishedRevision($uuid);

    if ($entity && $entity->get('moderation_state')->getString() == 'published') {
      return $entity->get('field_json_metadata')->getString();
    }

    throw new MissingObjectException("Error retrieving published dataset: {$this->schemaId} {$uuid} not found.");
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function retrieve(string $uuid) : ?string {

    if ($this->getDefaultModerationState() === 'published') {
      $entity = $this->getEntityPublishedRevision($uuid);
    }
    else {
      $entity = $this->getEntityLatestRevision($uuid);
    }

    if ($entity) {
      return $entity->get('field_json_metadata')->getString();
    }

    throw new MissingObjectException("Error retrieving metadata: {$this->schemaId} {$uuid} not found.");
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function publish(string $uuid) : string {

    $entity = $this->getEntityLatestRevision($uuid);

    if (!$entity) {
      throw new \Exception("Error publishing dataset: {$uuid} not found.");
    }
    elseif ('published' !== $entity->get('moderation_state')) {
      $entity->set('moderation_state', 'published');
      $entity->save();
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Load a Data entity's published revision.
   *
   * @param string $uuid
   *   The dataset identifier.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The entity's published revision, if found.
   */
  public function getEntityPublishedRevision(string $uuid) {

    $entity_id = $this->getEntityIdFromUuid($uuid);
    // @todo extract an actual published revision.
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
      ->accessCheck(FALSE)
      ->condition('uuid', $uuid)
      ->condition($this->bundleKey, $this->bundle)
      ->condition('field_data_type', $this->schemaId)
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
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function store($data, string $uuid = NULL): string {
    $data = json_decode($data);

    $data = $this->filterHtml($data);

    $uuid = (!$uuid && isset($data->identifier)) ? $data->identifier : $uuid;

    if ($uuid) {
      $entity = $this->getEntityLatestRevision($uuid);
    }

    if (isset($entity) && $entity instanceof EntityInterface) {
      return $this->updateExistingEntity($entity, $data);
    }
    // Create new entity.
    else {
      return $this->createNewEntity($uuid, $data);
    }
  }

  /**
   * Private.
   */
  private function updateExistingEntity(EntityInterface $entity, $data) {
    $entity->field_data_type = $this->schemaId;
    $new_data = json_encode($data);
    $entity->field_json_metadata = $new_data;

    // Dkan publishing's default moderation state.
    $entity->set('moderation_state', $this->getDefaultModerationState());

    $entity->setRevisionLogMessage("Updated on " . (new \DateTimeImmutable())->format(\DateTimeImmutable::ATOM));
    $entity->setRevisionCreationTime(time());
    $entity->save();

    return $entity->uuid();
  }

  /**
   * Create a new metadata entity from incoming data and identifier.
   *
   * @param string $uuid
   *   Metadata identifier.
   * @param object $data
   *   Decoded JSON data.
   *
   * @return string
   *   UUID of new entity.
   *
   * @throws \JsonPath\InvalidJsonException
   * @throws \InvalidArgumentException
   */
  private function createNewEntity(string $uuid, $data) {
    $title = '';
    if ($this->schemaId === 'dataset') {
      $title = isset($data->title) ? $data->title : $data->name;
    }
    else {
      $title = Service::metadataHash($data->data);
    }
    $entity = $this->getEntityStorage()
      ->create(
        [
          $this->labelKey => $title,
          $this->bundleKey => $this->bundle,
          'uuid' => $uuid,
          'field_data_type' => $this->schemaId,
          'field_json_metadata' => json_encode($data),
        ]
      );
    $entity->setRevisionLogMessage("Created on " . (new \DateTimeImmutable())->format(\DateTimeImmutable::ATOM));

    $entity->save();

    return $entity->uuid();
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
  private function filterHtml($input) {
    // @todo find out if we still need it.
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
    // Initialize HTML Purifier cache config settings array.
    $config = [];

    // Determine path to tmp directory.
    $tmp_path = \Drupal::service('file_system')->getTempDirectory();
    // Specify custom location in tmp directory for storing HTML Purifier cache.
    $cache_dir = rtrim($tmp_path, '/') . '/html_purifier_cache';

    // Ensure the tmp cache directory exists.
    if (!is_dir($cache_dir) && !mkdir($cache_dir)) {
      $this->log('metastore', 'Failed to create cache directory for HTML purifier');
    }
    else {
      $config['Cache.SerializerPath'] = $cache_dir;
    }

    // Create HTML purifier instance using custom cache path.
    $filter = new \HTMLPurifier(\HTMLPurifier_Config::create($config));
    // Filter the supplied string.
    return $filter->purify($input);
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
