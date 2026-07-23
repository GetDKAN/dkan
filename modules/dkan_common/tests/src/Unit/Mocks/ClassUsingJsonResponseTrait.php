<?php

namespace Drupal\Tests\dkan_common\Unit\Mocks;

use Drupal\dkan_common\JsonResponseTrait;

/**
 * Trait host for JsonResponseTraitTest.
 *
 * Exposes the protected getExceptionData method via a public proxy. Kept in
 * its own file (no inline class) per #4706 review feedback:
 * https://github.com/GetDKAN/dkan/pull/4706#discussion_r3274833176
 */
class ClassUsingJsonResponseTrait {
  use JsonResponseTrait;

  public function callGetExceptionData(\Exception $e) {
    return $this->getExceptionData($e);
  }

}
