<?php

namespace Drupal\metastore_entity;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\metastore\SchemaRetrieverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class.
 */
class MetastoreEntitySchemaRetriever implements SchemaRetrieverInterface {

  /**
   * Config storage.
   *
   * @var Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  protected $entityTypeManager;

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager')
    );
  }

  public function __construct(EntityTypeBundleInfoInterface $bundleInfo, EntityTypeManagerInterface $entityTypeManager) {
    $this->bundleInfo = $bundleInfo;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Public.
   */
  public function getAllIds() {
    $schemas = $this->bundleInfo->getBundleInfo('metastore_item');
    return array_keys($schemas);
  }

  /**
   * Public.
   */
  public function retrieve(string $id): ?string {
    if ($this->isUiSchemaId($id)) {
      return $this->retrieveUiSchema($id);
    }
    $schema = $this->entityTypeManager->getStorage('metastore_schema')->load($id);
    $jsonSchema = $schema->getSchema();
    return $jsonSchema;

  }

  private function isUiSchemaId($id) {
    return (substr($id, -3, 3) === '.ui');
  }

  private function retrieveUiSchema($id) {
    $id = substr($id, 0, -3);
    $schema = $this->entityTypeManager->getStorage('metastore_schema')->load($id);
    $jsonSchema = $schema->getUiSchema();
    return $jsonSchema;
  }

}
