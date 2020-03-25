<?php

namespace Drupal\Tests\dkan_schema\Unit;

use Drupal\dkan_schema\SchemaRetriever;
use Drupal\Core\Extension\ExtensionList;
use PHPUnit\Framework\TestCase;

/**
 * Tests Drupal\dkan_schema\SchemaRetriever.
 *
 * @coversDefaultClass \Drupal\dkan_schema\SchemaRetriever
 * @group dkan_harvest
 */
class SchemaRetrieverTest extends TestCase {

  /**
   *
   */
  public function testSchemaDirectory() {
    $profile = $this->getMockBuilder(ExtensionList::class)
      ->setMethods(['getPathname'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $profile->method("getPathname")->willReturn("./tmp");

    $retriever = new SchemaRetriever("/tmp", $profile);
    $dir = $retriever->getSchemaDirectory();
    $this->assertEquals($dir, './schema');
  }

  /**
   *
   */
  public function testGetAllIds() {
    $profile = $this->getMockBuilder(ExtensionList::class)
      ->setMethods(['getPathname'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $profile->method("getPathname")->willReturn("./tmp");

    $retriever = new SchemaRetriever("/tmp", $profile);
    $ids = $retriever->getAllIds();
    $this->assertEquals(['dataset', 'dataset.ui'], $ids);
  }

  /**
   *
   */
  public function testGet() {
    $profile = $this->getMockBuilder(ExtensionList::class)
      ->setMethods(['getPathname'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $profile->method("getPathname")->willReturn("./tmp");

    $retriever = new SchemaRetriever("/tmp", $profile);
    $schema = $retriever->retrieve('dataset');
    $json = json_decode($schema);
    $this->assertNotFalse($json);
  }

  /**
   *
   */
  public function testError() {
    $this->expectExceptionMessage("Schema blah not found.");
    $profile = $this->getMockBuilder(ExtensionList::class)
      ->setMethods(['getPathname'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $profile->method("getPathname")->willReturn("./tmp");

    $retriever = new SchemaRetriever("/tmp", $profile);
    $retriever->retrieve('blah');
  }

  /**
   *
   */
  public function testNoDirectory() {
    $this->expectExceptionMessage("No schema directory found.");
    $profile = $this->getMockBuilder(ExtensionList::class)
      ->setMethods(['getPathname'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $profile->method("getPathname")->willReturn("/abcd");

    $retriever = new SchemaRetriever("/abcd", $profile);
    $retriever->retrieve('dataset');
  }

}
