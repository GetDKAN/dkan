<?php

namespace Drupal\dkan_schema;

use Contracts\RetrieverInterface;

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
  public function __construct() {
    $this->findSchemaDirectory();
  }

  /**
   * Public.
   */
  public function getAllIds() {
    return [
      'dataset',
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
  protected function findSchemaDirectory() {

    $drupalRoot = \Drupal::service('app.root');

    if (is_dir($drupalRoot . "/schema")) {
      $this->directory = $drupalRoot . "/schema";
    }
    elseif (($directory = $this->getDefaultSchemaDirectory())
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
  protected function getDefaultSchemaDirectory() {
    /** @var \Drupal\Core\Extension\ExtensionList $extensionList */
    $extensionList = \Drupal::service('extension.list.profile');
    $infoFile = $extensionList->getPathname('dkan2');

    return dirname($infoFile) . '/schema';
  }

}
