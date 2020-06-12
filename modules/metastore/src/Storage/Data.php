<?php

namespace Drupal\metastore\Storage;

use Contracts\BulkRetrieverInterface;
use Contracts\RemoverInterface;
use Contracts\RetrieverInterface;
use Contracts\StorerInterface;
use DateTime;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\node\NodeStorageInterface;
use HTMLPurifier;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Data.
 */
class Data implements ContainerInjectionInterface, StorerInterface, RetrieverInterface, BulkRetrieverInterface, RemoverInterface {

  /**
   * Metastore update and create operations take effect immediately.
   *
   * @var int
   */
  const PUBLISH_NOW = 1;

  /**
   * Metastore update and create operations are deferred.
   *
   * @var int
   */
  const PUBLISH_LATER = 2;

  /**
   * Node storage service.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  private $nodeStorage;

  /**
   * Config service, where the publishing method setting is stored.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configService;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configService
   *   Injected config factory service.
   */
  public function __construct(NodeStorageInterface $nodeStorage, ConfigFactoryInterface $configService) {
    $this->nodeStorage = $nodeStorage;
    $this->configService = $configService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.common.node_storage'),
      $container->get('config.factory')
    );
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
      ->condition('status', 1)
      ->execute();

    $all = [];
    foreach ($node_ids as $nid) {
      /* @var $node \Drupal\node\NodeInterface */
      $node = $this->nodeStorage->load($nid);
      $all[] = $node->get('field_json_metadata')->getString();
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
      'status' => 1,
    ]);

    $node = $nodes ? reset($nodes) : FALSE;

    if ($node) {
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

    $node = $this->getLatestNodeRevision($uuid);
    if ($node) {
      return $node->get('field_json_metadata')->getString();
    }

    throw new \Exception("No data with that identifier was found.");
  }

  /**
   * Return the publishing method.
   *
   * @return int
   *   One of this class' PUBLISH_ constants, cast to an integer to mitigate
   *   that the return value from config's get is a string, and to facilitate
   *   stricter comparison.
   */
  public function getPublishMethod() {
    $config = $this->configService->get('metastore.settings');
    return (int) $config->get('publishing');
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
    if ($node) {
      $node->setPublished();
      $node->isDefaultRevision(TRUE);
      $node->save();
      return $id;
    }

    throw new \Exception("@Todo: Storage Data publishing logic.");
  }

  /**
   * Inherited.
   *
   * {@inheritDoc}.
   */
  public function remove(string $uuid) {

    $nid = $this->getNidFromUuid($uuid);
    $node = $this->nodeStorage->load($nid);
    if ($node) {
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
      $node = $this->getLatestNodeRevision($id);
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
          'type' => $this->getType(),
          'uuid' => $id,
          'field_data_type' => $this->schemaId,
          'field_json_metadata' => json_encode($data),
          'status' => ($this->getPublishMethod() === Data::PUBLISH_NOW) ? 1 : 0,
        ]
      );
    $node->setRevisionLogMessage("Created on " . $this->formattedTimestamp());

    $node->save();
    return $node->uuid();
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

}
