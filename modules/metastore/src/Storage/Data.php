<?php

namespace Drupal\metastore\Storage;

use Contracts\BulkRetrieverInterface;
use Contracts\RemoverInterface;
use Contracts\RetrieverInterface;
use Contracts\StorerInterface;
use DateTime;
use Drupal\node\NodeStorageInterface;
use HTMLPurifier;

/**
 * Data.
 */
class Data implements StorerInterface, RetrieverInterface, BulkRetrieverInterface, RemoverInterface {

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
   * @param \Drupal\node\NodeStorageInterface $nodeStorage
   *   Injected node storage service.
   */
  public function __construct(NodeStorageInterface $nodeStorage) {
    $this->nodeStorage = $nodeStorage;
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

    // Normally the condition should be on moderation_state, not status. Due to
    // https://www.drupal.org/project/drupal/issues/3025164 we cannot getQuery
    // on moderation_state as it is not a normal field, but a computed one.
    // However, currently, it works for dkan_publishing workflow since it only
    // has a single state considered 'Published'.
    $node_ids = $this->nodeStorage->getQuery()
      ->condition('type', $this->getType())
      ->condition('field_data_type', $this->schemaId)
      ->execute();

    $all = [];
    foreach ($node_ids as $nid) {
      /* @var $node \Drupal\node\NodeInterface */
      $node = $this->nodeStorage->load($nid);
      if ($node->get('moderation_state')->getString() == 'published') {
        $all[] = $node->get('field_json_metadata')->getString();
      }
    }
    return $all;
  }

  /**
   * Retrieve the json metadata from a node only if it is published.
   *
   * @param string $uuid
   *   The identifier.
   *
   * @return string|null
   *   The node's json metadata, or NULL if the node was not found.
   */
  public function retrievePublished(string $uuid) : ?string {

    $this->assertSchema();

    $nodes = $this->nodeStorage->loadByProperties([
      'uuid' => $uuid,
      'field_data_type' => $this->schemaId,
      'type' => $this->getType(),
    ]);

    $node = $nodes ? reset($nodes) : FALSE;

    if ($node && $node->get('moderation_state')->getString() == 'published') {
      return $node->get('field_json_metadata')->getString();
    }

    throw new \Exception("No data with that identifier was found.");
  }

  /**
   * Inherited.
   *
   * {@inheritDoc}.
   */
  public function retrieve(string $uuid): ?string {

    $this->assertSchema();
    $node = $this->getLatestNodeRevision($uuid);
    if ($node) {
      return $node->get('field_json_metadata')->getString();
    }

    throw new \Exception("No data with that identifier was found.");
  }

  /**
   * Publish the latest version of a data node.
   *
   * @param string $id
   *   Identifier.
   *
   * @return string
   *   Identifier.
   */
  public function publish(string $id) {

    $this->assertSchema();
    if ($this->schemaId !== 'dataset') {
      throw new \Exception("Publishing currently only implemented for datasets.");
    }

    $node = $this->getLatestNodeRevision($id);

    if (!$node) {
      throw new \Exception("No data with that identifier was found.");
    }

    if ($node->get('moderation_state') !== 'published') {
      $node->set('moderation_state', 'published');
      $node->save();
    }

    return $id;
  }

  /**
   * Load a node's latest revision, given a dataset's uuid.
   *
   * @param string $uuid
   *   The dataset identifier.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The node's latest revision, if found.
   */
  private function getLatestNodeRevision(string $uuid) {
    $nid = $this->getNidFromUuid($uuid);
    $revision_id = $this->nodeStorage->getLatestRevisionId($nid);
    return $this->nodeStorage->loadRevision($revision_id);
  }

  /**
   * Get the node id from the dataset identifier.
   *
   * @param string $uuid
   *   The dataset identifier.
   *
   * @return int|string
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

    // Dkan publishing's default moderation state
    $node->set('moderation_state', $this->getDefaultModerationState());

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
   */
  private function getDefaultModerationState() {
    return \Drupal::service('entity_type.manager')
      ->getStorage('workflow')
      ->load('dkan_publishing')
      ->getTypePlugin()
      ->getConfiguration()['default_moderation_state'];
  }

}
