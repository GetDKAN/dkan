<?php

namespace Drupal\dkan_datastore\Manager;

use Drupal\dkan_datastore\Storage\Database;

use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityInterface;

use Dkan\Datastore\Resource;
use Drupal\node\Entity\Node;

/**
 * Factory class to instantiate classes that are needed to build the manager.
 *
 * Those classes exist outside of service container.
 *
 * @TODO may need a refactor in the future if dependencies are moved to service container.
 */
class Helper {

  private $entityRepository;
  private $database;

  /**
   * Helper constructor.
   */
  public function __construct(
    EntityRepository $entity_repository,
    Database $database) {

    $this->entityRepository = $entity_repository;
    $this->database = $database;
  }

  /**
   * Public.
   */
  public function getResourceFromEntity($uuid): Resource {
    $node = $this->loadNodeByUuid($uuid);
    return $this->newResource($node->id(), $this->getResourceFilePathFromNode($node));
  }

  /**
   * Creates a new resource object.
   *
   * @param int $id
   * @param string $filePath
   *
   * @return \Dkan\Datastore\Resource
   */
  public function newResource($id, $filePath) {
    return new Resource($id, $filePath);
  }

  /**
   * @return \Drupal\dkan_datastore\Storage\Database
   *
   * @codeCoverageIgnore
   */
  public function getDatabaseForResource(Resource $resource) {
    $this->database->setResource($resource);
    return $this->database;
  }

  /**
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @return string
   * @throws \Exception if validation of entity or data fails.
   */
  private function getResourceFilePathFromNode(Node $node): string {

    $meta = $node->get('field_json_metadata')->get(0)->getValue();

    if (!isset($meta['value'])) {
      throw new \Exception("Entity for {$node->uuid()} does not have required field `field_json_metadata`.");
    }

    $metadata = json_decode($meta['value']);

    if (!($metadata instanceof \stdClass)) {
      throw new \Exception("Invalid metadata information or missing file information.");
    }

    if (isset($metadata->data->downloadURL)) {
      return $metadata->data->downloadURL;
    }

    if (isset($metadata->distribution[0]->downloadURL)) {
      return $metadata->distribution[0]->downloadURL;
    }

    throw new \Exception("Invalid metadata information or missing file information.");
  }

  /**
   * @param string $uuid
   *   The UUID of the entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *
   * @throws \Exception
   */
  public function loadNodeByUuid(string $uuid): EntityInterface {
    $node = $this->entityRepository->loadEntityByUuid('node', $uuid);

    if (!($node instanceof Node)) {
      throw new \Exception("Node {$uuid} could not be loaded.");
    }

    return $node;
  }

}
