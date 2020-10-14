<?php

namespace Drupal\Tests\metastore\Unit\Events;

use Drupal\common\Resource;
use Drupal\metastore\Events\Registration;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

class RegistrationTest extends TestCase {

  public function test() {
    $mockResource = (new Chain($this))
      ->add(Resource::class)
      ->getMock();

    $registration = new Registration($mockResource);

    $this->assertEquals($mockResource, $registration->getResource());
  }

}
