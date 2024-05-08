<?php

namespace Drupal\Tests\common\Unit\Contracts;

use Drupal\Tests\common\Unit\Mocks\IdGenerator\Sequential;
use PHPUnit\Framework\TestCase;

/**
 * @group dkan
 * @group common
 * @group unit
 * @group contracts
 */
class SequentialTest extends TestCase {

  public function test(): void {
    $generator = new Sequential();
    $id1 = $generator->generate();
    $this->assertEquals(1, $id1);
    $id2 = $generator->generate();
    $this->assertEquals(2, $id2);
  }

}
