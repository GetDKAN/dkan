<?php

namespace Drupal\Tests\dkan\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Fail a CI...
 *
 * @group dkan
 */
class DemoFailingTest extends UnitTestCase {

  public function testThisWillFail() {
    $this->fail('You have failed.');
  }

}
