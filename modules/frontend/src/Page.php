<?php

namespace Drupal\frontend;

use Drupal\node\NodeStorageInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Frontend page.
 */
class Page {

  /**
   * App root directory for react data catalog app.
   *
   * @var string
   */
  private $appRoot;

  /**
   * Node storage service.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  private $nodeStorage;

  /**
   * Build folder configuration.
   *
   * @var string
   */
  private $buildFolder;

  /**
   * Frontend path configuration.
   *
   * @var string
   */
  private $frontendPath;

  /**
   * Constructor.
   */
  public function __construct(
    string $appRoot,
    NodeStorageInterface $nodeStorage,
    ConfigFactoryInterface $configFactory
  ) {
    $this->appRoot = $appRoot;
    $this->nodeStorage = $nodeStorage;
    $this->buildFolder = $configFactory->get('frontend.config')->get('build_folder');
    $this->frontendPath = $configFactory->get('frontend.config')->get('frontend_path');
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
      $file = $this->appRoot . $this->frontendPath . $this->buildFolder . "/index.html";
    }
    else {
      $name = str_replace("__", "/", $name);
      $file = $this->appRoot . $this->frontendPath . $this->buildFolder . "/{$name}/index.html";
    }
    return is_file($file) ? file_get_contents($file) : FALSE;
  }

  /**
   * Build Dataset.
   *
   * @return string|bool
   *   False if file doesn't exist.
   *
   * @todo Is this dead code?
   */
  public function buildDataset($name) {
    $base_dataset = $this->appRoot . $this->frontendPath . $this->buildFolder . "/dataset/index.html";
    $file = $this->appRoot . $this->frontendPath . $this->buildFolder . "/dataset/{$name}/index.html";

    return is_file($file) ? file_get_contents($file) : file_get_contents($base_dataset);
  }

}
