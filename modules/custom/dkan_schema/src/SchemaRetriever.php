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
  public function __construct($appRoot, ExtensionList $profileExtensionList) {
    $this->findSchemaDirectory($appRoot, $profileExtensionList);
  }

  /**
   * Public.
   */
  public function getAllIds() {
    return [
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
  protected function findSchemaDirectory($appRoot, $profileExtensionList) {

    $drupalRoot = $appRoot;

    if (is_dir($drupalRoot . "/schema")) {
      $this->directory = $drupalRoot . "/schema";
    }
    elseif (($directory = $this->getDefaultSchemaDirectory($profileExtensionList))
          && is_dir($directory)
      ) {
      $this->directory = $directory;
    }
    else {
      throw new \Exception("No schema directory found.");
    }
  }

  /**
   * Determine default location of schema folder for dkan2 profile.
   *
   * @todo There may be easier way to do this and without hardcoding paths.
   *
   * @return string
   *   Path.
   */
  protected function getDefaultSchemaDirectory($profileExtensionList) {
    /** @var \Drupal\Core\Extension\ExtensionList $extensionList */
    $extensionList = $profileExtensionList;
    $infoFile = $extensionList->getPathname('dkan2');

    return dirname($infoFile) . '/schema';
  }

}
