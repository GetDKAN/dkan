<?php

namespace Drupal\dkan_frontend;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class.
 */
class Page {

  private $appRoot;
  private $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(string $appRoot, EntityTypeManagerInterface $entityTypeManager) {
    $this->appRoot = $appRoot;
    $this->entityTypeManager = $entityTypeManager;
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
    $node_loaded_by_uuid = $this->entityTypeManager->getStorage('node')->loadByProperties(['uuid' => $name]);
    $node_loaded_by_uuid = reset($node_loaded_by_uuid);
    $file = $this->appRoot . "/data-catalog-frontend/public/dataset/{$name}/index.html";

    return is_file($file) ? file_get_contents($file) : file_get_contents($base_dataset);
  }

}
