<?php

namespace Drupal\metastore\Storage;

use Drupal\common\LoggerTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Service;
use Drupal\workflows\WorkflowInterface;

/**
 * Abstract metastore storage class, for using Drupal entities.
 */
abstract class Data implements MetastoreEntityStorageInterface {

  use LoggerTrait;

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
   * Get the field name where the JSON metadata is stored.
   *
   * @return string
   *   Field name, eg., field_json_metadata.
   */
  abstract public static function getMetadataField();

  /**
   * Get the field name where the schema ID is stored.
   *
   * @return string
   *   Field name, eg., field_data_type.
   */
  abstract public static function getSchemaIdField();

  /**
   * Create basic query for a list of metastore items.
   *
   * @param int|null $start
   *   Offset. NULL if no range, 0 to start at beginning of set.
   * @param int|null $length
   *   Number of items to retrieve. NULL for no limit.
   * @param bool $unpublished
   *   Whether to include unpublished items in the results.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   A Drupal query object.
   */
  protected function listQueryBase(int $start = NULL, ?int $length = NULL, bool $unpublished = FALSE):QueryInterface {
    $query = $this->entityStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $this->bundle)
      ->condition(static::getSchemaIdField(), $this->schemaId)
      ->range($start, $length);

    if ($unpublished === FALSE) {
      $query->condition('status', 1);
    }

    return $query;
  }

  /**
   * Take an array of entity IDs and load the JSON metadata for each.
   *
   * @param array $entityIds
   *   An array of Drupal entity IDs, returned from an entity query.
   *
   * @return string[]
   *   An array of JSON strings containing the metadata.
   */
  protected function entityIdsToJsonStrings(array $entityIds): array {
    return array_map(function ($entity) {
      return $entity->get($this->getMetadataField())->getString();
    }, $this->entityStorage->loadMultiple($entityIds));
  }

  /**
   * {@inheritdoc}
   */
  public function count($unpublished = FALSE): int {
    return $this->listQueryBase()->count($unpublished)->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveAll(?int $start = NULL, ?int $length = NULL, bool $unpublished = FALSE): array {
    $entityIds = $this->listQueryBase($start, $length, $unpublished)->execute();
    return $this->entityIdsToJsonStrings($entityIds);
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveIds(?int $start = NULL, ?int $length = NULL, bool $unpublished = FALSE): array {

    $entityIds = $this->listQueryBase($start, $length, $unpublished)->execute();

    return array_map(function ($entity) {
      return $entity->uuid();
    }, $this->entityStorage->loadMultiple($entityIds));
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function retrievePublished(string $uuid) : ?string {
    $entity = $this->getEntityPublishedRevision($uuid);

    if ($entity && $entity->get('moderation_state')->getString() == 'published') {
      return $entity->get(static::getMetadataField())->getString();
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
      return $entity->get(static::getMetadataField())->getString();
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
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
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
      ->condition(static::getSchemaIdField(), $this->schemaId)
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

    if (isset($entity) && $entity instanceof ContentEntityInterface) {
      return $this->updateExistingEntity($entity, $data);
    }
    // Create new entity.
    else {
      return $this->createNewEntity($uuid, $data);
    }
  }

  /**
   * Overwrite a content entity with new metadata.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Drupal content entity.
   * @param string $data
   *   JSON data.
   *
   * @return string|null
   *   The content entity UUID, or null if failed.
   */
  private function updateExistingEntity(ContentEntityInterface $entity, string $data): ?string {
    $entity->{static::getSchemaIdField()} = $this->schemaId;
    $new_data = json_encode($data);
    $entity->{static::getMetadataField()} = $new_data;

    // Dkan publishing's default moderation state.
    $entity->set('moderation_state', $this->getDefaultModerationState());

    if ($entity instanceof RevisionLogInterface) {
      $entity->setRevisionLogMessage("Updated on " . (new \DateTimeImmutable())->format(\DateTimeImmutable::ATOM));
      $entity->setRevisionCreationTime(time());
    }
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
    $entity = $this->getEntityStorage()->create(
      [
        $this->labelKey => $title,
        $this->bundleKey => $this->bundle,
        'uuid' => $uuid,
        static::getSchemaIdField() => $this->schemaId,
        static::getMetadataField() => json_encode($data),
      ]
    );
    if ($entity instanceof RevisionLogInterface) {
      $entity->setRevisionLogMessage("Created on " . (new \DateTimeImmutable())->format(\DateTimeImmutable::ATOM));
    }

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
  public function getDefaultModerationState(): string {
    $workflow = $this->entityTypeManager->getStorage('workflow')->load('dkan_publishing');
    if ($workflow instanceof WorkflowInterface) {
      return $workflow->getTypePlugin()->getConfiguration()['default_moderation_state'];
    }
    // If this failed for some reason, default to published.
    return 'published';
  }

}
