<?php

namespace Drupal\metastore\Storage;

use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Data.
 */
abstract class AbstractEntityStorage implements MetastoreEntityStorageInterface {

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
  private $schemaId;

  /**
   * Entity storage service.
   *
   * @var \Drupal\Core\Entity\ContentEntityStorageInterface
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
   * Entity field used to store the schema.
   *
   * @var string
   */
  protected $schemaField;

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
   * Field/property where metadata is stored.
   *
   * @var string
   */
  protected $metadataField;

  /**
   * Constructor.
   */
  public function __construct(string $schemaId, EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityStorage = $this->entityTypeManager->getStorage($this->entityType);
    $this->setSchema($schemaId);

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
   * Private.
   */
  private function setSchema($schemaId) {
    $this->schemaId = $schemaId;
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function retrieveAll(): array {

    $entity_ids = $this->entityStorage->getQuery()
      ->condition($this->bundleKey, $this->bundle)
      ->condition($this->schemaField, $this->schemaId)
      ->execute();

    $all = [];
    foreach ($entity_ids as $entity_id) {
      $entity = $this->getEntityPublishedRevision($entity_id);
      $all[] = $entity->get($this->metadataField)->getString();
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
      return $entity->get($this->metadataField)->getString();
    }

    throw new \Exception("No data with that identifier was found.");
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
      return $entity->get($this->metadataField)->getString();
    }

    throw new \Exception("No data with that identifier was found.");
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function publish(string $uuid) : string {

    if ($this->schemaId !== 'dataset') {
      throw new \Exception("Publishing currently only implemented for datasets.");
    }

    $entity = $this->getEntityLatestRevision($uuid);

    if ($entity && $entity->get('moderation_state') !== 'published') {
      $entity->set('moderation_state', 'published');
      $entity->save();
      return $uuid;
    }

    throw new \Exception("No data with that identifier was found.");
  }

  /**
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function getEntityIdFromUuid(string $uuid) : ?int {

    $entity_ids = $this->entityStorage->getQuery()
      ->condition('uuid', $uuid)
      ->condition($this->bundleKey, $this->bundle)
      ->condition($this->schemaField, $this->schemaId)
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

    if (isset($entity) && $entity instanceof EditorialContentEntityBase) {
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
  private function updateExistingEntity(EditorialContentEntityBase $entity, $data) {
    $entity->{$this->schemaField} = $this->schemaId;
    $new_data = json_encode($data);
    $entity->{$this->metadataField} = $new_data;

    // Dkan publishing's default moderation state.
    $entity->set('moderation_state', $this->getDefaultModerationState());

    $entity->setRevisionLogMessage("Updated on " . $this->formattedTimestamp());
    $entity->setRevisionCreationTime(time());
    $entity->save();

    return $entity->uuid();
  }

  /**
   * Private.
   */
  protected function createNewEntity($uuid, $data) {
    $title = '';
    if ($this->schemaId === 'dataset') {
      $title = isset($data->title) ? $data->title : $data->name;
    }
    else {
      $title = md5(json_encode($data->data));
    }
    $entity = $this->entityStorageCreate([
      $this->labelKey => $title,
      $this->bundleKey => $this->bundle,
      'uuid' => $uuid,
      $this->schemaField => $this->schemaId,
      $this->metadataField => json_encode($data),
    ]);
    $entity->setRevisionLogMessage("Created on " . $this->formattedTimestamp());

    $entity->save();

    return $entity->uuid();
  }

  /**
   * Create a storage, implement EditorialContentEntityBase.
   *
   * @param array $values
   *   Values array to use to build entity.
   *
   * @return \Drupal\Core\Entity\EditorialContentEntityBase
   *   New content entity.
   */
  protected function entityStorageCreate(array $values) {
    $entity = $this->entityStorage->create($values);
    return $entity;
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
  protected function htmlPurifier(string $input) {
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
   * {@inheritdoc}
   */
  public function getDefaultModerationState() {
    return $this->entityTypeManager->getStorage('workflow')
      ->load('dkan_publishing')
      ->getTypePlugin()
      ->getConfiguration()['default_moderation_state'];
  }

}
