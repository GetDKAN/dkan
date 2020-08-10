<?php

namespace Drupal\metastore\LifeCycle;

use Drupal\metastore\NodeWrapper\Data as Wrapper;

/**
 * AbstractData.
 */
abstract class AbstractData {

  protected $data;

  /**
   * Constructor.
   */
  public function __construct(Wrapper $data) {
    $this->data = $data;
  }

  /**
   * Protected.
   */
  protected function go($stage) {
    $method = "{$this->data->getDataType()}{$stage}";
    if (method_exists($this, $method)) {
      $this->{$method}();
    }
  }

}
