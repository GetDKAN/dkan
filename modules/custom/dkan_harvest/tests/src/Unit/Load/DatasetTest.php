<?php

namespace Drupal\Tests\dkan_harvest\Unit\Extract;

use Contracts\Mock\Storage\Memory;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_harvest\Load\Dataset;
use Sae\Sae;

/**
 * Tests Drupal\dkan_harvest\Load\Dataset.
 *
 * @coversDefaultClass Drupal\dkan_harvest\Load\Dataset
 * @group dkan_harvest
 */
class DatasetTest extends DkanTestBase {

  /**
   * Public.
   */
  public function test() {
    $load = $this->getMockBuilder(Dataset::class)
      ->disableOriginalConstructor()
      ->setMethods(['getDatasetEngine'])
      ->getMock();

    $storage = new Memory();
    $storage->store("This is a string", "1");
    $this->assertEquals(1, count($storage->retrieveAll()));

    $engine = new Sae($storage, "{
  \"\$schema\": \"http://json-schema.org/draft-07/schema#\",
  \"title\": \"Yep\",
  \"type\": \"string\"");

    $load->method('getDatasetEngine')->willReturn($engine);
    $load->removeItem("1");

    $this->assertEquals(0, count($storage->retrieveAll()));
  }

  /**
   * Tests saveItem().
   */
  public function testSaveItem() {
    // Setup.
    $mock = $this->getMockBuilder(Dataset::class)
      ->disableOriginalConstructor()
      ->setMethods(['getDatasetEngine'])
      ->getMock();

    $mockEngine = $this->getMockBuilder(Sae::class)
      ->setMethods(['post'])
      ->disableOriginalConstructor()
      ->getMock();

    $item = (object) ['foo' => 'bar'];
    // Expect.
    $mock->expects($this->once())
      ->method('getDatasetEngine')
      ->willReturn($mockEngine);

    $mockEngine->expects($this->once())
      ->method('post')
      ->willReturn(json_encode($item));

    // Assert.
    $this->invokeProtectedMethod($mock, 'saveItem', $item);
  }

}
