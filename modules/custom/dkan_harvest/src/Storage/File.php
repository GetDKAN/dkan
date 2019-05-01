<?php

namespace Drupal\dkan_harvest\Storage;

use Harvest\Storage\Storage;
use Drupal\dkan_harvest\Load\FileHelperTrait;

/**
 *
 */
class File implements Storage {

  protected $directoryPath;
  use FileHelperTrait;

  /**
   *
   */
  public function __construct($directory_path) {
    $this->directoryPath = $directory_path;
    $this->getFileHelper()
      ->prepareDir(
                    $this->directoryPath,
                    FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS
              );
  }

  /**
   *
   */
  public function retrieve(string $id): ?string {
    $file_path = "{$this->directoryPath}/{$id}.json";
    return $this->getFileHelper()
      ->fileGetContents($file_path);
  }

  /**
   *
   */
  public function store(string $data, string $id = NULL): string {
    $file_path = "{$this->directoryPath}/{$id}.json";
    $this->getFileHelper()
      ->filePutContents($file_path, $data);
    return $id;
  }

  /**
   *
   */
  public function remove(string $id) {
    $file_path = "{$this->directoryPath}/{$id}.json";
    $this->getFileHelper()
      ->fileDelete($file_path);
  }

  /**
   *
   */
  public function retrieveAll(): array {
    $files_pattern = "{$this->directoryPath}/*.json";
    $items = [];
    $fileHelper = $this->getFileHelper();

    foreach ($fileHelper->fileGlob($files_pattern) as $file) {
      $items[basename($file, ".json")] = $fileHelper->fileGetContents($file);
    }
    return $items;
  }

}
