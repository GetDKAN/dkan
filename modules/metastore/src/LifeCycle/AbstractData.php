<?php

namespace Drupal\metastore\LifeCycle;

use Drupal\metastore\MetastoreItemInterface;

/**
 * AbstractData.
 */
abstract class AbstractData {

  protected $data;

  /**
   * Constructor.
   */
  public function __construct(MetastoreItemInterface $data) {
    $this->data = $data;
  }

  /**
   * Protected.
   */
  protected function go($stage) {
    $method = "{$this->data->getSchemaId()}{$stage}";
    if (method_exists($this, $method)) {
      $this->{$method}();
    }
  }

}
