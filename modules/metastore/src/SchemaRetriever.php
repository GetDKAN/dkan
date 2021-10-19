<?php

namespace Drupal\metastore;

use Contracts\RetrieverInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class.
 */
class SchemaRetriever implements RetrieverInterface, ContainerInjectionInterface {

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
    $appRoot = $container->getParameter('app.root');
    $moduleExtension = $container->get('extension.list.module');

    return new static($appRoot, $moduleExtension);
  }

  /**
   * Public.
   */
  public function __construct($appRoot, ModuleExtensionList $extensionList) {
    $this->findSchemaDirectory($appRoot, $extensionList);
  }

  /**
   * Public.
   */
  public function getAllIds() {
    return [
      'catalog',
      'dataset',
      'dataset.ui',
      'publisher',
      'publisher.ui',
      'distribution',
      'distribution.ui',
      'theme',
      'theme.ui',
      'keyword',
      'keyword.ui',
      'data-dictionary',
    ];
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

    $filename = $this->getSchemaDirectory() . "/collections/{$id}.json";

    if (in_array($id, $this->getAllIds())
          && is_readable($filename)
      ) {
      return file_get_contents($filename);
    }
    throw new \Exception("Schema {$id} not found.");
  }

  /**
   * Private.
   */
  protected function findSchemaDirectory($appRoot, $extensionList) {

    $drupalRoot = $appRoot;
    $drupalRootSchema = $drupalRoot . "/schema";

    $defaultSchema = $drupalRoot . "/" . $this->getDefaultSchemaDirectory($extensionList);

    if (is_dir($drupalRootSchema)) {
      $this->directory = $drupalRootSchema;
    }
    elseif (is_dir($defaultSchema)) {
      $this->directory = $defaultSchema;
    }
    else {
      throw new \Exception("No schema directory found.");
    }
  }

  /**
   * Determine default location of schema folder for dkan.
   *
   * @todo There may be easier way to do this and without hardcoding paths.
   *
   * @return string
   *   Path.
   */
  protected function getDefaultSchemaDirectory(ModuleExtensionList $extensionList) {
    $infoFile = $extensionList->getPathname('dkan');
    return dirname($infoFile) . '/schema';
  }

}
