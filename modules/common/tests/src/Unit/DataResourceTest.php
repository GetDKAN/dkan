<?php

namespace Drupal\Tests\common\Unit;

use Drupal\Component\DependencyInjection\Container;
use Drupal\common\DataResource;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Drupal\common\Resource.
 *
 * @coversDefaultClass \Drupal\common\DataResource
 */
class DataResourceTest extends TestCase {

  /**
   * Test getTableName().
   */
  public function testGetTableName() {

    $resource = new DataResource(
      '/foo/bar',
      'txt',
      DataResource::DEFAULT_SOURCE_PERSPECTIVE
    );
    $tableName = $resource->getTableName();

    $this->assertStringStartsWith('datastore_', $tableName);
  }

  /**
   * Test getFolder().
   */
  public function testGetFolder() {

    $resource = new DataResource(
      '/foo/bar',
      'txt',
      DataResource::DEFAULT_SOURCE_PERSPECTIVE
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

    $idAndVersion = DataResource::getIdentifierAndVersion($uuid);

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
    DataResource::getIdentifierAndVersion($id);
  }

  /**
   * @covers ::createNewPerspective
   */
  public function testCreateNewPerspective() {
    $data_resource = new DataResource(
      '/foo/bar',
      'txt',
      DataResource::DEFAULT_SOURCE_PERSPECTIVE
    );

    // Set the checksum.
    $checksum = 'fake_checksum';
    $ref_checksum = new \ReflectionProperty(DataResource::class, 'checksum');
    $ref_checksum->setAccessible(TRUE);
    $ref_checksum->setValue($data_resource, $checksum);

    // Ensure our test checksum is set correctly.
    $this->assertSame($checksum, $data_resource->getChecksum());

    $clone_data_resource = $data_resource->createNewPerspective(ResourceLocalizer::LOCAL_URL_PERSPECTIVE, 'uri://foo/bar');

    // Not the same object.
    $this->assertNotSame($data_resource, $clone_data_resource);
    // Clone contains 'local_url' perspective.
    $this->assertEquals(ResourceLocalizer::LOCAL_URL_PERSPECTIVE, $clone_data_resource->getPerspective());
    $this->assertEquals('uri://foo/bar', $clone_data_resource->getFilePath());

    // A side-effect of createNewPerspective() is that the new object has the
    // same checksum.
    $this->assertSame($data_resource->getChecksum(), $clone_data_resource->getChecksum());
  }

  /**
   * @covers ::createNewVersion
   */
  public function testCreateNewVersion() {
    $data_resource = new DataResource(
      '/foo/bar',
      'txt',
      DataResource::DEFAULT_SOURCE_PERSPECTIVE
    );

    $clone_data_resource = $data_resource->createNewVersion();

    // Not the same object.
    $this->assertNotSame($data_resource, $clone_data_resource);
    // Clone contains new version.
    $this->assertNotEquals(
      $data_resource->getVersion(),
      $clone_data_resource->getVersion()
    );
  }

}
