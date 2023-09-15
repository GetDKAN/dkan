<?php

namespace Drupal\metastore\Storage;

use Drupal\common\LoggerTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\MetastoreService;
use Drupal\workflows\WorkflowInterface;

/**
 * Abstract metastore storage class, for using Drupal entities.
 *
 * @todo Separate workflow management and storage into separate classes.
 */
abstract class Data implements MetastoreEntityStorageInterface {

  use LoggerTrait;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
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
   * The entity field or property used to store JSON metadata.
   *
   * @var string
   */
  protected $metadataField;

  /**
   * The entity field or property used to store the schema ID (e.g. "dataset").
   *
   * @var string
   */
  protected $schemaIdField;

  /**
   * Constructor.
   */
  public function __construct(string $schemaId, EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityStorage = $this->entityTypeManager->getStorage($this->entityType);
    $this->schemaId = $schemaId;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityStorage() {
    return $this->entityStorage;
  }

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
      ->condition($this->schemaIdField, $this->schemaId)
      ->range($start, $length);

    if ($unpublished === FALSE) {
      $query->condition('status', 1);
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function count($unpublished = FALSE): int {
    return $this->listQueryBase(NULL, NULL, $unpublished)->count()->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveAll(?int $start = NULL, ?int $length = NULL, bool $unpublished = FALSE): array {
    $entityIds = $this->listQueryBase($start, $length, $unpublished)->execute();
    return array_map(function ($entity) {
      return $entity->get($this->metadataField)->getString();
    }, array_values($this->entityStorage->loadMultiple($entityIds)));
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveIds(?int $start = NULL, ?int $length = NULL, bool $unpublished = FALSE): array {

    $entityIds = $this->listQueryBase($start, $length, $unpublished)->execute();

    return array_map(function ($entity) {
      return $entity->uuid();
    }, array_values($this->entityStorage->loadMultiple($entityIds)));
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function isHidden(string $uuid): bool {
    $entity = $this->getEntityPublishedRevision($uuid);

    return isset($entity) && ($entity->moderation_state->value ?? NULL) === 'hidden';
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function isPublished(string $uuid): bool {
    $entity = $this->getEntityPublishedRevision($uuid);

    return isset($entity);
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function retrieve(string $uuid, bool $published = FALSE) : ?string {
    $entity = $published ? $this->getEntityPublishedRevision($uuid) : $this->getEntityLatestRevision($uuid);

    if (!isset($entity)) {
      throw new MissingObjectException("Error retrieving metadata: {$this->schemaId} {$uuid} not found.");
    }

    return $entity->get($this->metadataField)->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function publish(string $uuid): bool {
    return $this->setWorkflowState($uuid, 'published');
  }

  /**
   * {@inheritdoc}
   */
  public function archive(string $uuid): bool {
    return $this->setWorkflowState($uuid, 'archived');
  }

  /**
   * Change the state of a metastore item.
   *
   * @param string $uuid
   *   Metastore identifier.
   * @param string $state
   *   Any workflow state that can be applied to a metastore entity.
   *
   * @return bool
   *   Whether or not an item was transitioned.
   */
  protected function setWorkflowState(string $uuid, string $state): bool {
    $entity = $this->getEntityLatestRevision($uuid);

    if (!$entity) {
      throw new MissingObjectException("Error: {$uuid} not found.");
    }
    elseif ($state !== $entity->get('moderation_state')->getString()) {
      $entity->set('moderation_state', $state);
      $entity->save();
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityPublishedRevision(string $uuid): ?ContentEntityInterface {
    $entity_id = $this->getEntityIdFromUuid($uuid);
    if (!isset($entity_id)) {
      return NULL;
    }

    $entity = $this->entityStorage->load($entity_id);
    if ($entity instanceof EntityPublishedInterface) {
      return $entity->isPublished() ? $entity : NULL;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityLatestRevision(string $uuid): ?ContentEntityInterface {

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
      ->condition($this->schemaIdField, $this->schemaId)
      ->execute();

    return $entity_ids ? (int) reset($entity_ids) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function remove(string $uuid) {

    $entity = $this->getEntityLatestRevision($uuid);
    if ($entity) {
      return $entity->delete();
    }
  }

  /**
   * {@inheritdoc}
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
   * @param object $data
   *   JSON data.
   *
   * @return string|null
   *   The content entity UUID, or null if failed.
   */
  private function updateExistingEntity(ContentEntityInterface $entity, $data): ?string {
    $entity->{$this->schemaIdField} = $this->schemaId;
    $new_data = json_encode($data);
    $entity->{$this->metadataField} = $new_data;

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
      $title = MetastoreService::metadataHash($data->data);
    }
    $entity = $this->getEntityStorage()->create(
      [
        $this->labelKey => $title,
        $this->bundleKey => $this->bundle,
        'uuid' => $uuid,
        $this->schemaIdField => $this->schemaId,
        $this->metadataField => json_encode($data),
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
  }

}
