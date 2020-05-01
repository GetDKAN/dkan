<?php

namespace Drupal\Tests\dkan_schema\Unit;

use Drupal\dkan_schema\SchemaRetriever;
use Drupal\Core\Extension\ExtensionList;
use PHPUnit\Framework\TestCase;

/**
 * Tests Drupal\dkan_schema\SchemaRetriever.
 *
 * @coversDefaultClass \Drupal\dkan_schema\SchemaRetriever
 * @group harvest
 */
class SchemaRetrieverTest extends TestCase {

  /**
   *
   */
  public function testSchemaDirectory() {
    $module = $this->getMockBuilder(ExtensionList::class)
      ->setMethods(['getPathname'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $module->method("getPathname")->willReturn("./tmp");

    $retriever = new SchemaRetriever("/tmp", $module);
    $dir = $retriever->getSchemaDirectory();
    $this->assertEquals($dir, './schema');
  }

  /**
   *
   */
  public function testGetAllIds() {
    $module = $this->getMockBuilder(ExtensionList::class)
      ->setMethods(['getPathname'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $module->method("getPathname")->willReturn("./tmp");

    $retriever = new SchemaRetriever("/tmp", $module);
    $ids = $retriever->getAllIds();
    $this->assertEquals(['catalog', 'dataset', 'dataset.ui'], $ids);
  }

  /**
   *
   */
  public function testGet() {
    $module = $this->getMockBuilder(ExtensionList::class)
      ->setMethods(['getPathname'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $module->method("getPathname")->willReturn("./tmp");

    $retriever = new SchemaRetriever("/tmp", $module);
    $schema = $retriever->retrieve('dataset');
    $json = json_decode($schema);
    $this->assertNotFalse($json);
  }

  /**
   *
   */
  public function testError() {
    $this->expectExceptionMessage("Schema blah not found.");
    $module = $this->getMockBuilder(ExtensionList::class)
      ->setMethods(['getPathname'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $module->method("getPathname")->willReturn("./tmp");

    $retriever = new SchemaRetriever("/tmp", $module);
    $retriever->retrieve('blah');
  }

  /**
   *
   */
  public function testNoDirectory() {
    $this->expectExceptionMessage("No schema directory found.");
    $module = $this->getMockBuilder(ExtensionList::class)
      ->setMethods(['getPathname'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $module->method("getPathname")->willReturn("/abcd");

    $retriever = new SchemaRetriever("/abcd", $module);
    $retriever->retrieve('dataset');
  }

}
