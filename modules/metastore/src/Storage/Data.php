<?php

namespace Drupal\metastore\Storage;

use Contracts\BulkRetrieverInterface;
use Contracts\RemoverInterface;
use Contracts\RetrieverInterface;
use Contracts\StorerInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\NodeInterface;

/**
 * Data.
 */
class Data implements StorerInterface, RetrieverInterface, BulkRetrieverInterface, RemoverInterface {

  const EVENT_DATASET_UPDATE = 'dkan_metastore_dataset_update';

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
   */
  public function __construct(string $schemaId, EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
    $this->setSchema($schemaId);
  }

  /**
   * Get node storage.
   *
   * @return \Drupal\node\NodeStorageInterface
   *   Node storage.
   */
  public function getNodeStorage() {
    return $this->nodeStorage;
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

    $node_ids = $this->nodeStorage->getQuery()
      ->condition('type', $this->getType())
      ->condition('field_data_type', $this->schemaId)
      ->execute();

    $all = [];
    foreach ($node_ids as $nid) {
      /** @var \Drupal\node\NodeInterface $node */
      $node = $this->nodeStorage->load($nid);
      if ($node->get('moderation_state')->getString() === 'published') {
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
    $node = $this->getNodePublishedRevision($uuid);

    if ($node && $node->get('moderation_state')->getString() == 'published') {
      return $node->get('field_json_metadata')->getString();
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
      $node = $this->getNodePublishedRevision($uuid);
    }
    else {
      $node = $this->getNodeLatestRevision($uuid);
    }

    if ($node) {
      return $node->get('field_json_metadata')->getString();
    }

    throw new \Exception("No data with that identifier was found.");
  }

  /**
   * Publish the latest version of a data node.
   *
   * @param string $uuid
   *   Identifier.
   *
   * @return string
   *   Identifier.
   */
  public function publish(string $uuid) : string {

    if ($this->schemaId !== 'dataset') {
      throw new \Exception("Publishing currently only implemented for datasets.");
    }

    $node = $this->getNodeLatestRevision($uuid);

    if ($node) {
      $moderationState = $node->moderation_state->value;
      if ($moderationState !== 'published') {
        $node->set('moderation_state', 'published');
        $node->save();
        return $uuid;
      }
    }

    throw new \Exception("No data with that identifier was found.");
  }

  /**
   * Load a Data node's published revision.
   *
   * @param string $uuid
   *   The dataset identifier.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The node's published revision, if found.
   */
  public function getNodePublishedRevision(string $uuid) {

    $nid = $this->getNidFromUuid($uuid);

    return $nid ? $this->nodeStorage->load($nid) : NULL;
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
  public function getNodeLatestRevision(string $uuid) {

    $nid = $this->getNidFromUuid($uuid);

    if ($nid) {
      $revision_id = $this->nodeStorage->getLatestRevisionId($nid);
      return $this->nodeStorage->loadRevision($revision_id);
    }

    return NULL;
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
  public function getNidFromUuid(string $uuid) : ?int {

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
   * {@inheritdoc}.
   */
  public function remove(string $uuid) {

    $node = $this->getNodeLatestRevision($uuid);
    if ($node) {
      return $node->delete();
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
      $node = $this->getNodeLatestRevision($uuid);
    }

    if (isset($node) && $node instanceof NodeInterface) {
      return $this->updateExistingNode($node, $data);
    }
    // Create new node.
    else {
      return $this->createNewNode($uuid, $data);
    }
  }

  /**
   * Private.
   */
  private function updateExistingNode(NodeInterface $node, $data) {
    $node->field_data_type = $this->schemaId;
    $new_data = json_encode($data);
    $node->field_json_metadata = $new_data;

    // Dkan publishing's default moderation state.
    $node->set('moderation_state', $this->getDefaultModerationState());

    $node->setRevisionLogMessage("Updated on " . $this->formattedTimestamp());
    $node->setRevisionCreationTime(time());
    $node->save();

    return $node->uuid();
  }

  /**
   * Private.
   */
  private function createNewNode($uuid, $data) {
    $title = isset($data->title) ? $data->title : $data->name;
    $node = $this->nodeStorage
      ->create(
        [
          'title' => $title,
          'type' => 'data',
          'uuid' => $uuid,
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
    $filter = new \HTMLPurifier();
    return $filter->purify($input);
  }

  /**
   * Returns the current time, formatted.
   *
   * @return string
   *   Current timestamp, formatted.
   */
  private function formattedTimestamp() : string {
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
