<?php

namespace Drupal\metastore\Storage;

use Contracts\BulkRetrieverInterface;
use Contracts\RemoverInterface;
use Contracts\RetrieverInterface;
use Contracts\StorerInterface;
use DateTime;
use Drupal\Core\Entity\EntityTypeManager;
use HTMLPurifier;

/**
 * Data.
 */
class Data implements StorerInterface, RetrieverInterface, BulkRetrieverInterface, RemoverInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * Node storage service.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  private $nodeStorage;

  /**
   * Represents the data type passed via the HTTP request url schema_id slug.
   *
   * @var string
   */
  private $schemaId;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Injected entity type manager.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
  }

  /**
   * Sets the data type.
   *
   * @param string $schema_id
   *   The data type.
   */
  public function setSchema($schema_id) {
    $this->schemaId = $schema_id;
    return $this;
  }

  /**
   * Assert schema is set.
   *
   * @throws \Exception
   */
  private function assertSchema() {
    if (!isset($this->schemaId)) {
      throw new \Exception("Data schema id not set.");
    }
  }

  /**
   * Inherited.
   *
   * {@inheritDoc}.
   */
  public function retrieveAll(): array {

    $this->assertSchema();

    $node_ids = $this->nodeStorage->getQuery()
      ->condition('type', $this->getType())
      ->condition('field_data_type', $this->schemaId)
      ->execute();

    $all = [];
    foreach ($node_ids as $nid) {
      /* @var $node \Drupal\node\NodeInterface */
      $node = $this->nodeStorage->load($nid);
      $fieldList = $node->get('field_json_metadata');
      $field = $fieldList->get(0);
      $data = $field->getValue();
      $all[] = $data['value'];
    }
    return $all;
  }

  /**
   * Inherited.
   *
   * {@inheritDoc}.
   */
  public function retrieve(string $id): ?string {

    $this->assertSchema();

    if (FALSE !== ($node = $this->getNodeByUuid($id))) {
      $value = $node->get('field_json_metadata')->get(0)->getValue();
      return $value['value'];
    }

    throw new \Exception("No data with the identifier {$id} was found.");
  }

  /**
   * Get the node id from the dataset identifier.
   *
   * @param string $uuid
   *   The dataset identifier.
   *
   * @return int|null
   *   The node id, if found.
   */
  private function getNidFromUuid(string $uuid) : ?int {

    $nids = $this->nodeStorage->getQuery()
      ->condition('uuid', $uuid)
      ->condition('type', $this->getType())
      ->condition('field_data_type', $this->schemaId)
      ->execute();

    return $nids ? (int) reset($nids) : NULL;
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
  public function store($data, string $id = NULL): string {

    $this->assertSchema();

    $data = json_decode($data);
    $data = $this->filterHtml($data);

    $id = (!$id && isset($data->identifier)) ? $data->identifier : $id;

    if ($id) {
      $node = $this->getNodeByUuid($id);
    }

    /* @var $node \Drupal\node\NodeInterface */
    if ($node) {
      return $this->updateExistingNode($node, $data);
    }
    // Create new node.
    else {
      return $this->createNewNode($id, $data);
    }
  }

  /**
   * Private.
   */
  private function updateExistingNode($node, $data) {
    $node->field_data_type = $this->schemaId;
    $new_data = json_encode($data);
    $node->field_json_metadata = $new_data;
    // Create a new revision.
    $node->setNewRevision(TRUE);
    $node->isDefaultRevision(TRUE);
    $node->setRevisionLogMessage("Updated on " . $this->formattedTimestamp());

    $node->save();
    return $node->uuid();
  }

  /**
   * Private.
   */
  private function createNewNode($id, $data) {
    $title = isset($data->title) ? $data->title : $data->name;
    $node = $this->nodeStorage
      ->create(
        [
          'title' => $title,
          'type' => 'data',
          'uuid' => $id,
          'field_data_type' => $this->schemaId,
          'field_json_metadata' => json_encode($data),
        ]
      );
    $node->setRevisionLogMessage("Created on " . $this->formattedTimestamp());

    $node->save();
    return $node->uuid();
  }

  /**
   * Get type.
   *
   * @return string
   *   Type of node.
   */
  private function getType() {
    return 'data';
  }

  /**
   * Fetch node id of a current type given uuid.
   *
   * @return \Drupal\node\Entity\Node|bool
   *   Returns false if no nodes match.
   */
  private function getNodeByUuid($uuid) {

    $nodes = $this->nodeStorage->loadByProperties(
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
  private function filterHtml($input) {
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
    $filter = new HTMLPurifier();
    return $filter->purify($input);
  }

  /**
   * Returns the current time, formatted.
   *
   * @return string
   *   Current timestamp, formatted.
   */
  private function formattedTimestamp() : string {
    $now = new DateTime('now');
    return $now->format(DateTime::ATOM);
  }

  /**
   * Return the default moderation state of our custom dkan_publishing workflow.
   *
   * @return string
   *   Either 'draft' or 'published'.
   */
  private function getDefaultModerationState() {
    return $this->entityTypeManager->getStorage('workflow')
      ->load('dkan_publishing')
      ->getTypePlugin()
      ->getConfiguration()['default_moderation_state'];
  }

}
