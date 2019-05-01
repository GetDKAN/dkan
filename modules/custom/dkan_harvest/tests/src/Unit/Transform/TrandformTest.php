<?php

namespace Drupal\Tests\dkan_harvest\Unit\Transform;

use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_harvest\Transform\Transform;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Tests Drupal\dkan_harvest\Transform\Transfom.
 *
 * @coversDefaultClass Drupal\dkan_harvest\Transform\Transfom
 * @group dkan_harvest
 */
class TrandformTest extends DkanTestBase {

  /**
   *
   */
  public function testConstruct() {
    // Setup.
    $mock = $this->getMockBuilder(Transform::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $harvestPlan = 'has no typehint, fix this?';

    // Assert.
    $mock->__construct($harvestPlan);
    $this->assertEquals($harvestPlan, $this->readAttribute($mock, 'harvestPlan'));
  }

  /**
   *
   */
  public function testRun() {
    // Setup.
    $mock = $this->getMockBuilder(Transform::class)
      ->setMethods(['hook'])
      ->disableOriginalConstructor()
      ->getMock();

    $items = ['has no typehint, fix this?'];

    // Expect.
    $mock->expects($this->once())
      ->method('hook')
      ->with($items);

    // Assert.
    $mock->run($items);
  }

  /**
   *
   */
  public function testHook() {
    // Setup.
    $mock = $this->getMockBuilder(Transform::class)
      ->setMethods(['hook'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockModuleHandler = $this->getMockBuilder(ModuleHandlerInterface::class)
      ->setMethods(['invokeAll'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $this->setActualContainer([
      'module_handler' => $mockModuleHandler,
    ]);

    $items       = ['has no typehint, fix this?'];
    $newItems    = ['something has changed?'];
    $harvestPlan = 'nothing to see here';

    $this->writeProtectedProperty($mock, 'harvestPlan', $harvestPlan);

    // Expect.
    $mockModuleHandler->expects($this->once())
      ->method('invokeAll')
      ->with('dkan_harvest_transform', [$items, $harvestPlan])
      ->willReturn($newItems);

    // Assert.
    $mock->hook($items);
    $this->markTestIncomplete("Passing by reference isn't really supported by unit test suites. Consider refactoring to use return.");
  }

}
