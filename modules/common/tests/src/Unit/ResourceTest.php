<?php

namespace Drupal\Tests\common\Unit;

use Drupal\Component\DependencyInjection\Container;
use Drupal\common\Resource;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Drupal\common\Resource.
 */
class ResourceTest extends TestCase {

  /**
   * Test getTableName().
   */
  public function testGetTableName() {

    $resource = new Resource(
      '/foo/bar',
      'txt',
      Resource::DEFAULT_SOURCE_PERSPECTIVE
    );
    $tableName = $resource->getTableName();

    $this->assertStringStartsWith('datastore_', $tableName);
  }

  /**
   * Test getFolder().
   */
  public function testGetFolder() {

    $resource = new Resource(
      '/foo/bar',
      'txt',
      Resource::DEFAULT_SOURCE_PERSPECTIVE
    );
    $folder = dirname($resource->getFilePath());

    $this->assertEquals('/foo', $folder);
  }

  /**
   * Test getIdentifierAndVersion()'s happy path.
   */
  public function testGetIdentifierAndVersionHappyPath() {

    $identifier = uniqid();
    $version = uniqid();
    $perspective = uniqid();
    $uuid = "{$identifier}__{$version}__{$perspective}";

    $idAndVersion = Resource::getIdentifierAndVersion($uuid);

    $expected = [$identifier, $version];
    $this->assertEquals($expected, $idAndVersion);
  }

  /**
   * Test getIdentifierAndVersion()'s final exception.
   */
  public function testGetIdentifierAndVersionException() {

    $options = (new Options())
      ->add('dkan.metastore.storage', DataFactory::class)
      ->index(0);
    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      ->add(NodeData::class, 'retrieve');
    \Drupal::setContainer($container->getMock());

    $id = uniqid();
    $expectedMessage = "Could not find identifier and version for {$id}";

    $this->expectExceptionMessage($expectedMessage);
    $result = Resource::getIdentifierAndVersion($id);
  }

}
