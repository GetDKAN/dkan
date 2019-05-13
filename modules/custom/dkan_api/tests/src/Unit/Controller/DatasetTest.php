<?php

namespace Drupal\Tests\dkan_api\Unit\Controller;

use Drupal\dkan_api\Controller\Dataset;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_api\Storage\DrupalNodeDataset;
use Contracts\Retriever;

/**
 * Tests Drupal\dkan_api\Controller\Dataset.
 *
 * @coversDefaultClass Drupal\dkan_api\Controller\Dataset
 * @group dkan_api
 * @author Yaasir Ketwaroo <yaasir.ketwaroo@semanticbits.com>
 */
class DatasetTest extends DkanTestBase {

  /**
   * Tests __construct().
   */
  public function testConstruct() {

    $mockContainer = $this->getMockContainer();
    $mockDataset = $this->createMock(DrupalNodeDataset::class);

    $mock = $this->getMockBuilder(Dataset::class)
      ->disableOriginalConstructor()
      ->setMethods(['parentCall'])
      ->getMock();

    // Expect.
    $mock->expects($this->once())
      ->method('parentCall')
      ->with('__construct', $mockContainer);

    $mockContainer->expects($this->once())
      ->method('get')
      ->with('dkan_api.storage.drupal_node_dataset')
      ->willReturn($mockDataset);

    // Assert.
    $mock->__construct($mockContainer);

    $this->assertSame(
            $mockDataset,
            $this->readAttribute($mock, 'nodeDataset')
    );
  }

  /**
   * Tests getStorage().
   */
  public function testGetStorage() {
    // Setup.
    $mockDataset = $this->createMock(DrupalNodeDataset::class);
    $mock = $this->getMockBuilder(Dataset::class)
      ->disableOriginalConstructor()
      ->setMethods(['parentCall'])
      ->getMock();
    $this->writeProtectedProperty($mock, 'nodeDataset', $mockDataset);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getStorage');
    $this->assertSame($mockDataset, $actual);
  }

  /**
   * Tests getJsonSchema().
   */
  public function testGetJsonSchema() {

    // Setup.
    $mockRetriever = $this->getMockBuilder(Retriever::class)
      ->setMethods(['retrieve'])
      ->getMockForAbstractClass();

    $mockContainer = $this->getMockContainer();

    $mock = $this->getMockBuilder(Dataset::class)
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();

    $this->writeProtectedProperty($mock, 'container', $mockContainer);

    $expected = uniqid('fake provider return');

    // Expect.
    $mockContainer->expects($this->once())
      ->method('get')
      ->with('dkan_schema.schema_retriever')
      ->willReturn($mockRetriever);

    $mockRetriever->expects($this->once())
      ->method('retrieve')
      ->with('dataset')
      ->willReturn($expected);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getJsonSchema');
    $this->assertEquals($expected, $actual);
  }

}
