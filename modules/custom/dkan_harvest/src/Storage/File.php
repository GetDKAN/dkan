<?php

namespace Drupal\dkan_harvest\Storage;

use Harvest\Storage\Storage;

class File implements Storage {

  private $directoryPath;

  public function __construct($directory_path) {
    $this->directoryPath = $directory_path;
    file_prepare_directory($this->directoryPath, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
  }

  public function retrieve(string $id): ?string {
    $content = NULL;
    $file_path = "{$this->directoryPath}/{$id}.json";
    if (file_exists($file_path)) {
      $content = file_get_contents($file_path);
    }
    return $content;
  }

  public function store(string $data, string $id = NULL): string {
    $file_path = "{$this->directoryPath}/{$id}.json";
    file_put_contents($file_path, $data);
    return $id;
  }

  public function remove(string $id) {
    $file_path = "{$this->directoryPath}/{$id}.json";
    if (file_exists($file_path)) {
      unlink($file_path);
    }
  }

  public function retrieveAll(): array {
    $files_pattern = "{$this->directoryPath}/*.json";
    $items = [];
    foreach(glob($files_pattern) as $file) {
      $items[basename($file, ".json")] = file_get_contents($file);
    }
    return $items;
  }


}