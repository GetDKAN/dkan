<?php

namespace Drupal\frontend;

use Drupal\node\NodeStorageInterface;

/**
 * Class.
 */
class Page {

  private $appRoot;
  private $nodeStorage;

  /**
   * Constructor.
   */
  public function __construct(string $appRoot, NodeStorageInterface $nodeStorage) {
    $this->appRoot = $appRoot;
    $this->nodeStorage = $nodeStorage;
  }

  /**
   * Build.
   *
   * @return string|bool
   *   False if file doesn't exist.
   *
   * @todo /data-catalog-frontend/build/index.html may not always exist.
   */
  public function build($name) {
    if ($name == 'home') {
      $file = $this->appRoot . "/data-catalog-frontend/public/index.html";
    }
    else {
      $name = str_replace("__", "/", $name);
      $file = $this->appRoot . "/data-catalog-frontend/public/{$name}/index.html";
    }
    return is_file($file) ? file_get_contents($file) : FALSE;
  }

  /**
   * Build Dataset.
   *
   * @return string|bool
   *   False if file doesn't exist.
   */
  public function buildDataset($name) {
    $base_dataset = $this->appRoot . "/data-catalog-frontend/public/dataset/index.html";
    $node_loaded_by_uuid = $this->nodeStorage->loadByProperties(['uuid' => $name]);
    $node_loaded_by_uuid = reset($node_loaded_by_uuid);
    $file = $this->appRoot . "/data-catalog-frontend/public/dataset/{$name}/index.html";

    return is_file($file) ? file_get_contents($file) : file_get_contents($base_dataset);
  }

}
