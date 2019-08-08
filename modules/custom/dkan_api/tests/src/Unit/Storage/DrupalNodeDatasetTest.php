<?php

namespace Drupal\Tests\dkan_api\Unit\Storage;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_api\Storage\DrupalNodeDataset;
use Drupal\node\NodeStorageInterface;
use Drupal\dkan_datastore\Manager\Helper;
use Drupal\dkan_datastore\Manager\DeferredImportQueuer;
use Dkan\Datastore\Resource;
use Psr\Log\LoggerInterface;
use Drupal\Core\Logger\RfcLogLevel;

/**
 * Tests Drupal\dkan_api\Storage\DrupalNodeDataset.
 *
 * @coversDefaultClass \Drupal\dkan_api\Storage\DrupalNodeDataset
 * @group dkan_api
 */
class DrupalNodeDatasetTest extends DkanTestBase {

  /**
   * Tests __construct().
   */
  public function testConstruct() {
    $mockEntityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $mock = $this->getMockBuilder(DrupalNodeDataset::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Assert.
    $mock->__construct($mockEntityTypeManager);

    $this->assertSame(
          $mockEntityTypeManager,
          $this->readAttribute($mock, 'entityTypeManager')
      );
  }

  /**
   * Tests getNodeStorage.
   */
  public function testGetNodeStorage() {

    // Setup.
    $mock = $this->getMockBuilder(DrupalNodeDataset::class)
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();

    $mockEntityTypeManager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->setMethods(['getStorage'])
      ->getMockForAbstractClass();

    $mockNodeStorage = $this->createMock(NodeStorageInterface::class);

    $this->writeProtectedProperty($mock, 'entityTypeManager', $mockEntityTypeManager);

    // Expect.
    $mockEntityTypeManager->expects($this->once())
      ->method('getStorage')
      ->with('node')
      ->willReturn($mockNodeStorage);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getNodeStorage');

    $this->assertSame($mockNodeStorage, $actual);
  }

  /**
   * Tests getType().
   */
  public function testGetType() {

    // Setup.
    $mock = $this->getMockBuilder(DrupalNodeDataset::class)
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();

    $expected = 'data';

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getType');

    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests EnqueueDeferredImport().
   */
  public function testEnqueueDeferredImport() {
    // Setup.
    $mock = $this->getMockBuilder(DrupalNodeDataset::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockBuilderHelper = $this->getMockBuilder(Helper::class)
      ->setMethods(['newResourceFromEntity'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockDeferredImporter = $this->getMockBuilder(DeferredImportQueuer::class)
      ->setMethods(['createDeferredResourceImport'])
      ->disableOriginalConstructor()
      ->getMock();
    $this->setActualContainer(
          [
            'dkan_datastore.manager.datastore_manager_builder_helper' => $mockBuilderHelper,
            'dkan_datastore.manager.deferred_import_queuer'    => $mockDeferredImporter,
          ]
      );

    $mockResource = $this->createMock(Resource::class);
    $uuid         = uniqid('foo');
    $expected     = 42;

    // Expect.
    $mockBuilderHelper->expects($this->once())
      ->method('newResourceFromEntity')
      ->with($uuid)
      ->willReturn($mockResource);

    $mockDeferredImporter->expects($this->once())
      ->method('createDeferredResourceImport')
      ->with($uuid, $mockResource)
      ->willReturn($expected);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'enqueueDeferredImport', $uuid);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests EnqueueDeferredImport().
   */
  public function testEnqueueDeferredImportOnException() {
    // Setup.
    $mock = $this->getMockBuilder(DrupalNodeDataset::class)
      ->setMethods(['getLogger'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockBuilderHelper = $this->getMockBuilder(Helper::class)
      ->setMethods(['newResourceFromEntity'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockDeferredImporter = $this->getMockBuilder(DeferredImportQueuer::class)
      ->setMethods(['createDeferredResourceImport'])
      ->disableOriginalConstructor()
      ->getMock();

    $this->setActualContainer(
          [
            'dkan_datastore.manager.datastore_manager_builder_helper' => $mockBuilderHelper,
            'dkan_datastore.manager.deferred_import_queuer'    => $mockDeferredImporter,
          ]
      );

    $mockLogger = $this->getMockBuilder(LoggerInterface::class)
      ->setMethods(['log'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $uuid             = uniqid('foo');
    $exceptionMessage = 'something went fubar.';

    // Expect.
    $mockBuilderHelper->expects($this->once())
      ->method('newResourceFromEntity')
      ->with($uuid)
      ->willThrowException(new \Exception($exceptionMessage));

    $mockDeferredImporter->expects($this->never())
      ->method('createDeferredResourceImport');

    $mock->expects($this->once())
      ->method('getLogger')
      ->with('dkan_api')
      ->willReturn($mockLogger);

    $mockLogger->expects($this->exactly(2))
      ->method('log')
      ->withConsecutive(
              [RfcLogLevel::ERROR, "Failed to enqueue dataset import for {$uuid}. Reason: " . $exceptionMessage],
              // Value of trace may change depending of debugger so just assume it's a string.
              [RfcLogLevel::DEBUG, $this->isType('string')]
          );

    // Assert.
    $this->invokeProtectedMethod($mock, 'enqueueDeferredImport', $uuid);

  }

  /**
   * Placeholder.
   */
  public function testRemainingMethods() {

    $this->markTestIncomplete('Review of other methods in ' . DrupalNodeDataset::class . ' pending review of refactor.');
  }

  /**
   * @param mixed $input
   * @param $expected
   *
   * @dataProvider dataTestFilterHtml
   */
  public function testFilterHtml($input, $expected) {
    // Setup.
    $mock = $this->getMockBuilder(DrupalNodeDataset::class)
      ->setMethods(
              [
                'htmlPurifier',
              ]
          )
      ->disableOriginalConstructor()
      ->getMock();

    // Expects.
    $mock->expects($this->any())
      ->method('htmlPurifier')
      ->willReturn("purified");

    // Asserts.
    $actual = $this->invokeProtectedMethod($mock, 'filterHtml', $input);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Public.
   */
  public function dataTestFilterHtml() {
    return [
      "Test with integer" => [
        0, 0,
      ],
      "Test with string" => [
        "Test string", "purified",
      ],
      "Test with array" => [
      ["one" => "two", "three" => "four"], ["one" => "purified", "three" => "purified"],
      ],
      'Test with stdClass' => [
        (object) ['key' => 'value'], (object) ['key' => "purified"],
      ]
      ];
  }

}
