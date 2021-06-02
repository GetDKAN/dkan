<?php

namespace Drupal\Tests\metastore\Unit\Events;

use Drupal\common\Resource;
use Drupal\common\Events\Event;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

class RegistrationTest extends TestCase {

  public function test() {
    $mockResource = (new Chain($this))
      ->add(Resource::class)
      ->getMock();

    $registration = new Event($mockResource);

    $this->assertEquals($mockResource, $registration->getData());
  }

}
