<?php

namespace Drupal\metastore\Storage;

use Contracts\BulkRetrieverInterface;
use Contracts\RemoverInterface;
use Contracts\RetrieverInterface;
use Contracts\StorerInterface;
use DateTime;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\common\LoggerTrait;
use Drupal\node\NodeInterface;
use HTMLPurifier;

/**
 * Data.
 */
class Data implements StorerInterface, RetrieverInterface, BulkRetrieverInterface, RemoverInterface {
  use LoggerTrait;
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
    $this->setLoggerFactory(\Drupal::service('logger.factory'));
    $this->setSchema($schemaId);
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
      /* @var $node \Drupal\node\NodeInterface */
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
    $this->debug("uuid: @uuid", ['@uuid' => $uuid]);

    if ($this->getDefaultModerationState() === 'published') {
      $node = $this->getNodePublishedRevision($uuid);
    }
    else {
      $node = $this->getNodeLatestRevision($uuid);
    }

    if ($node) {

      $this->debugNode(__FUNCTION__, $node);

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

    if ($node && $node->get('moderation_state') !== 'published') {
      $node->set('moderation_state', 'published');
      $node->save();
      return $uuid;
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
  private function getNodePublishedRevision(string $uuid) {

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
  private function getNodeLatestRevision(string $uuid) {

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
    $this->debug($data);

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

    $node->save();

    $this->debugNode(__FUNCTION__, $node);

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

    $this->debugNode(__FUNCTION__, $node);

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

  /**
   * Private.
   */
  private function debugNode($function, NodeInterface $node) {
    $this->debug("%function data type: %data_type, nid: %nid, uuid: %uuid, revision: %revision",
      [
        "%function" => $function,
        "%data_type" => $node->field_data_type->value,
        "%nid" => $node->id(),
        "%uuid" => $node->uuid(),
        "%revision" => $node->getRevisionId(),
      ]
    );
    $this->debug($node->field_json_metadata->value);
  }

}
