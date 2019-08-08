<?php

namespace Drupal\dkan_api\Storage;

use Harvest\Storage\Storage;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\RfcLogLevel;
use HTMLPurifier;

/**
 * DrupalNodeDataset.
 */
class DrupalNodeDataset implements Storage {
  use \Drupal\Core\Logger\LoggerChannelTrait;

  /**
   * Entity Type Manager.
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
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Injected entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Sets the data type.
   *
   * @param string $schema_id
   *   The HTTP request's schema or data type.
   */
  public function setSchema($schema_id) {
    $this->schemaId = $schema_id;
  }

  /**
   * Get the node storage.
   *
   * @return \Drupal\node\NodeStorageInterface
   *   Node Storage.
   */
  protected function getNodeStorage() {
    return $this->entityTypeManager
      ->getStorage('node');
  }

  /**
   * Get type.
   *
   * @return string
   *   Type of node.
   */
  protected function getType() {
    return 'data';
  }

  /**
   * Inherited.
   *
   * {@inheritDoc}.
   */
  public function retrieve(string $id): ?string {

    if (!isset($this->schemaId)) {
      throw new \Exception("DrupalNodeDataset schemaId not set in retrieve().");
    }

    if (FALSE !== ($node = $this->getNodeByUuid($id))) {
      return $node->field_json_metadata->value;
    }

    throw new \Exception("No data with the identifier {$id} was found.");
  }

  /**
   * Inherited.
   *
   * {@inheritDoc}.
   */
  public function retrieveAll(): array {

    if (!isset($this->schemaId)) {
      throw new \Exception("DrupalNodeDataset schemaId not set in retrieveAll().");
    }

    $nodeStorage = $this->getNodeStorage();

    $node_ids = $nodeStorage->getQuery()
      ->condition('type', $this->getType())
      ->condition('field_data_type', $this->schemaId)
      ->execute();

    $all = [];
    foreach ($node_ids as $nid) {
      $node = $nodeStorage->load($nid);
      $all[] = $node->field_json_metadata->value;
    }
    return $all;
  }

  /**
   * Inherited.
   *
   * {@inheritDoc}.
   */
  public function remove(string $id) {

    if (FALSE !== ($node = $this->getNodeByUuid($id))) {
      return $node->delete();
    }
  }

  /**
   * Inherited.
   *
   * {@inheritDoc}.
   */
  public function store(string $data, string $id = NULL): string {

    if (!isset($this->schemaId)) {
      $this->schemaId = 'dataset';
    }

    $data = json_decode($data);
    $data = $this->filterHtml($data);

    if (!$id && isset($data->identifier)) {
      $id = $data->identifier;
    }

    if ($id) {
      $node = $this->getNodeByUuid($id);
    }

    /* @var $node \Drupal\node\NodeInterface */
    // Update existing node.
    if ($node) {
      $node->field_data_type = $this->schemaId;
      $new_data = json_encode($data);
      $node->field_json_metadata = $new_data;
      $node->save();
      return $node->uuid();
    }
    // Create new node.
    else {
      $title = isset($data->title) ? $data->title : $data->name;
      $node = $this->getNodeStorage()
        ->create(
                [
                  'title' => $title,
                  'type' => 'data',
                  'uuid' => $id,
                  'field_data_type' => $this->schemaId,
                  'field_json_metadata' => json_encode($data),
                ]
            );
      $node->save();

      $uuid = $node->uuid();
      $this->enqueueDeferredImport($uuid);
      return $node->uuid();
    }

    return NULL;
  }

  /**
   * Enqueue the dataset for further processing.
   *
   * @param string $uuid
   *   Uuid of node.
   *
   * @todo pass import config.
   *
   * @return int|bool
   *   New queue ID or false on failure
   */
  protected function enqueueDeferredImport(string $uuid) {

    try {
      /** @var \Drupal\dkan_datastore\Manager\Helper $managerBuilderHelper */
      $managerBuilderHelper = \Drupal::service('dkan_datastore.manager.datastore_manager_builder_helper');

      $resource = $managerBuilderHelper->newResourceFromEntity($uuid);

      /** @var \Drupal\dkan_datastore\Manager\DeferredImportQueuer $deferredImporter */
      $deferredImporter = \Drupal::service('dkan_datastore.manager.deferred_import_queuer');

      return $deferredImporter->createDeferredResourceImport($uuid, $resource);
    }
    catch (\Exception $e) {
      $logger = $this->getLogger('dkan_api');

      $logger->log(RfcLogLevel::ERROR, "Failed to enqueue dataset import for {$uuid}. Reason: " . $e->getMessage());
      $logger->log(RfcLogLevel::DEBUG, $e->getTraceAsString());
    }
  }

  /**
   * Fetch node id of a current type given uuid.
   *
   * @return \Drupal\node\Entity\Node|bool
   *   Returns false if no nodes match.
   */
  protected function getNodeByUuid($uuid) {

    $nodes = $this->getNodeStorage()
      ->loadByProperties(
              [
                'type' => $this->getType(),
                'uuid' => $uuid,
              ]
          );
    // Uuid should be universally unique and always return
    // a single node.
    return current($nodes);
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
    $filter = new HTMLPurifier();
    return $filter->purify($input);
  }

}
