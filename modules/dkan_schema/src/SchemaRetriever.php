<?php

namespace Drupal\dkan_schema;

use Contracts\RetrieverInterface;
use Drupal\Core\Extension\ExtensionList;

/**
 * Class.
 */
class SchemaRetriever implements RetrieverInterface {

  /**
   * Directory.
   *
   * @var string
   */
  protected $directory;

  /**
   * Public.
   */
  public function __construct($appRoot, ExtensionList $extensionList) {
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

    if (is_dir($drupalRoot . "/schema")) {
      $this->directory = $drupalRoot . "/schema";
    }
    elseif (($directory = $this->getDefaultSchemaDirectory($extensionList))
          && is_dir($directory)
      ) {
      $this->directory = $directory;
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
  protected function getDefaultSchemaDirectory($extensionList) {
    /** @var \Drupal\Core\Extension\ExtensionList $extensionList */
    $extensionList = $extensionList;
    $infoFile = $extensionList->getPathname('dkan');

    return dirname($infoFile) . '/schema';
  }

}
