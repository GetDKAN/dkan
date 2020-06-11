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

  const PUBLISH_NOW = 1;
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
   * Inherited.
   *
   * {@inheritDoc}.
   */
  public function retrieveAll(): array {

    if (!isset($this->schemaId)) {
      throw new \Exception("Data schema id not set.");
    }

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
    if (!isset($this->schemaId)) {
      throw new \Exception("Data schema id not set.");
    }

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
  public function retrieve(string $id): ?string {

    if (!isset($this->schemaId)) {
      throw new \Exception("Data schema id not set.");
    }

    if (FALSE !== ($node = $this->getNodeByUuid($id))) {
      $value = $node->get('field_json_metadata')->get(0)->getValue();
      return $value['value'];
    }

    throw new \Exception("No data with that identifier was found.");
  }

  /**
   * Return the publishing method.
   *
   * @return string
   *   Either Data::PUBLISH_NOW or Data::PUBLISH_LATER.
   */
  public function getPublishMethod() {
    return $this->configService->get('metastore.settings')->get('publishing');
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
    if (!isset($this->schemaId)) {
      throw new \Exception("Data schema id not set.");
    }
    if ($this->schemaId !== 'dataset') {
      throw new \Exception("Publishing currently only implemented for datasets.");
    }

    // @Todo: Publishing logic.
    throw new \Exception("@Todo: Storage Data publishing logic.");
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

    if (!isset($this->schemaId)) {
      throw new \Exception("Data schema id not set.");
    }

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

}
