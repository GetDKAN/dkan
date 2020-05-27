<?php

namespace Drupal\metastore\Storage;

use Contracts\BulkRetrieverInterface;
use Contracts\RemoverInterface;
use Contracts\RetrieverInterface;
use Contracts\StorerInterface;
use DateTime;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use HTMLPurifier;

/**
 * Data.
 */
class Data implements StorerInterface, RetrieverInterface, BulkRetrieverInterface, RemoverInterface {

  const PUBLISH_IMMEDIATELY = "immediately";
  const PUBLISH_MANUALLY = "not immediately";

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The config factory service.
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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Injected entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configService) {
    $this->entityTypeManager = $entityTypeManager;
    $this->configService = $configService;
  }

  /**
   * Sets the data type.
   *
   * @param string $schema_id
   *   The data type.
   */
  public function setSchema($schema_id) {
    $this->schemaId = $schema_id;
  }

  /**
   * Inherited.
   *
   * {@inheritDoc}.
   */
  public function retrieveAll(): array {

    if (!isset($this->schemaId)) {
      throw new \Exception("Data schemaId not set in retrieveAll().");
    }

    $nodeStorage = $this->getNodeStorage();

    $node_ids = $nodeStorage->getQuery()
      ->condition('type', $this->getType())
      ->condition('field_data_type', $this->schemaId)
      ->execute();

    $all = [];
    foreach ($node_ids as $nid) {
      /* @var $node \Drupal\node\NodeInterface */
      $node = $nodeStorage->load($nid);
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

    if (!isset($this->schemaId)) {
      throw new \Exception("Data schemaId not set in retrieve().");
    }

    if (FALSE !== ($node = $this->getNodeByUuid($id))) {
      $value = $node->get('field_json_metadata')->get(0)->getValue();
      return $value['value'];
    }

    throw new \Exception("No data with the identifier {$id} was found.");
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
      throw new \Exception("Data schemaId not set in store().");
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
    // Conditionally publish this new revision.
    $publish = $this->configService->get('metastore.settings')->get('publishing');
    $node->isDefaultRevision($publish === self::PUBLISH_IMMEDIATELY);

    $node->save();
    return $node->uuid();
  }

  /**
   * Publish the latest version of a data node.
   *
   * @param $id
   *   Identifier.
   */
  public function publish($id) {
    if (!isset($this->schemaId)) {
      throw new \Exception("Data schemaId not set in publish().");
    }
    if ($this->schemaId !== 'dataset') {
      throw new \Exception("Publishing currently only implemented for datasets.");
    }

    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getNodeByUuid($id);

    if (FALSE !== $node) {
      if (!$node->isLatestRevision()) {
        /** @var \Drupal\node\NodeStorageInterface $nodeStorage */
        $nodeStorage = $this->entityTypeManager->getStorage('node');
        $latestRevId = $nodeStorage->getLatestRevisionId($node->id());
        $latestRevision = $nodeStorage->loadRevision($latestRevId);
        $latestRevision->isDefaultRevision(TRUE);
        $latestRevision->save();
      }
      return $node->id();
    }

    throw new \Exception("No data with the identifier {$id} was found.");
  }

  /**
   * Private.
   */
  private function createNewNode($id, $data) {
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
    $node->setRevisionLogMessage("Created on " . $this->formattedTimestamp());

    $node->save();
    return $node->uuid();
  }

  /**
   * Get the node storage.
   *
   * @return \Drupal\node\NodeStorageInterface
   *   Node Storage.
   */
  private function getNodeStorage() {
    return $this->entityTypeManager
      ->getStorage('node');
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

    $nodes = $this->getNodeStorage()->loadByProperties(
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
    $now = new \DateTime('now');
    return $now->format(DateTime::ATOM);
  }

}
