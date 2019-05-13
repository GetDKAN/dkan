<?php

namespace Drupal\dkan_schema;

use Contracts\Retriever;

/**
 *
 */
class SchemaRetriever implements Retriever {

  /**
   *
   * @var string
   */
  protected $directory;

  /**
   *
   */
  public function __construct() {
    $this->findSchemaDirectory();
  }

  /**
   *
   */
  public function getAllIds() {
    return [
      'dataset',
    ];
  }

  /**
   *
   */
  public function getSchemaDirectory() {
    return $this->directory;
  }

  /**
   *
   */
  public function retrieve(string $id): ?string {

    $filename = $this->getSchemaDirectory() . "/collections/{$id}.json";

    if (
            in_array($id, $this->getAllIds())
            && is_readable($filename)
        ) {
      return file_get_contents($filename);
    }
    throw new \Exception("Schema {$id} not found.");
  }

  /**
   *
   */
  protected function findSchemaDirectory() {

    $drupalRoot = \Drupal::service('app.root');

    if (is_dir($drupalRoot . "/schema")) {
      $this->directory = $drupalRoot . "/schema";
    }
    elseif (
      ($directory = $this->getDefaultSchemaDirectory())
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
   * @return string path.
   */
  protected function getDefaultSchemaDirectory() {

    // Try to determine root `info.yml` of dkan profile.
    /** @var \Drupal\Core\Extension\ExtensionList $extensionList */
    $extensionList = \Drupal::service('extension.list.profile');
    $infoFile = $extensionList->getPathname('dkan2');

    return dirname($infoFile) . '/schema';
  }

}
