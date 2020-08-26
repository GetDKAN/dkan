<?php

namespace Drupal\metastore\Traits;

use Drupal\metastore\ResourceMapper;

/**
 * ResourceMapper Trait.
 */
trait ResourceMapperTrait {

  /**
   * File mapper.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  private $fileMapper;

  /**
   * Setter.
   */
  public function setFileMapper(ResourceMapper $fileMapper) {
    $this->fileMapper = $fileMapper;
  }

  /**
   * Getter.
   */
  private function getFileMapper(): ResourceMapper {
    if (!isset($this->fileMapper)) {
      throw new \Exception("ResourceMapper not set.");
    }
    return $this->fileMapper;
  }

}
