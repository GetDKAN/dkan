<?php

namespace Drupal\common\Util;

/**
 * Trait allows for simpler testing of `parent::` calls.
 *
 * Usage:
 *
 *  In subject under test, instead of using `parent::method($arg1, $arg2)`,
 *  use `$this->parentCall('method',$arg1, $arg2)`
 */
trait ParentCallTrait {

  /**
   * Wrapper for unit testing.
   *
   * Implementation is compatible with PHP 5.4+
   *
   * @param string $method
   *   Method name.
   * @param mixed $args
   *   Arguments to pass to parent.
   *
   * @codeCoverageIgnore
   *
   * @return mixed
   *   Return of parent.
   */
  protected function parentCall($method, ...$args) {
    return parent::$method(...$args);
  }

}
