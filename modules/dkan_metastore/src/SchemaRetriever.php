<?php

namespace Drupal\dkan_metastore;

use Contracts\RetrieverInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * DKAN schema retriever service.
 */
class SchemaRetriever implements RetrieverInterface, ContainerInjectionInterface {

  /**
   * Directory where schema files are stored.
   *
   * @var string
   */
  protected $directory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $appRoot = $container->getParameter('app.root');
    $moduleExtension = $container->get('extension.list.module');

    return new static($appRoot, $moduleExtension);
  }

  /**
   * Constructor.
   *
   * @param string $appRoot
   *   Drupal app root, for locating schema directory.
   * @param \Drupal\Core\Extension\ModuleExtensionList $extensionList
   *   Drupal extension list.
   */
  public function __construct($appRoot, ModuleExtensionList $extensionList) {
    $this->findSchemaDirectory($appRoot, $extensionList);
  }

  /**
   * Get all available schema IDs.
   *
   * @todo Make this dynamic.
   *
   * @return array
   *   List of schema IDs.
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
   * Get the directory where schema files are stored.
   *
   * @return string
   *   Directory path.
   */
  public function getSchemaDirectory() {
    return $this->directory;
  }

  /**
   * Retrieve a schema by ID.
   *
   * @param string $id
   *   Schema ID.
   *
   * @return string|null
   *   Schema content or null if not found.
   *
   * @throws \Exception
   *   If the schema is not found.
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
   * Find the schema directory.
   *
   * @param string $appRoot
   *   Drupal app root.
   * @param \Drupal\Core\Extension\ModuleExtensionList $extensionList
   *   Drupal extension list.
   *
   * @throws \Exception
   *   If no schema directory is found.
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
