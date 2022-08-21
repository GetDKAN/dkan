<?php

namespace Drupal\dkan;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\dkan\Schema\SchemaManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class.
 */
class SchemaRetriever implements ContainerInjectionInterface {

  /**
   * Directory.
   *
   * @var string
   */
  protected $directory;

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {
    $schemaManager = $container->get('plugin.manager.dkan_schema');
    return new static($schemaManager);
  }

  /**
   * Public.
   */
  public function __construct(SchemaManager $schemaManager) {
    $this->schemaManager = $schemaManager;
  }

  /**
   * Public.
   */
  public function getAllIds() {
    $pluginDefinitions = $this->schemaManager->getDefinitions();
    return array_keys($pluginDefinitions);
  }

  /**
   * Public.
   */
  public function getSchemaDirectory() {
    return $this->directory;
  }

  /**
   * Public.
   */
  public function retrieve(string $id): ?string {
    $plugin = $this->schemaManager->createInstance($id);
    $schema = $plugin->getSchema($id);
    return json_encode($schema);
  }

}
