<?php

namespace Drupal\Tests\metastore\Kernel;

use Drupal\common\DataResource;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\KernelTests\KernelTestBase;

/**
 * @group dkan
 * @group metastore
 * @group kernel
 *
 * @covers \Drupal\metastore\ResourceMapper
 * @coversDefaultClass \Drupal\metastore\ResourceMapper
 */
class ResourceMapperTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'metastore',
  ];

  protected function setUp() : void {
    parent::setUp();
    $this->installEntitySchema('resource_mapping');
  }

  /**
   * Test.
   */
  public function test() {
    $url = 'http://blah.blah/file/blah.csv';
    $url2 = 'http://blah.blah/file/blah2.csv';
    $localUrl = 'https://dkan.dkan/resources/file/blah.csv';
    $localUrl2 = 'https://dkan.dkan/resources/file/newblah.csv';

    /** @var \Drupal\metastore\ResourceMapper $mapper */
    $mapper = $this->container->get('dkan.metastore.resource_mapper');

    // Register a resource.
    $resource1 = $this->getResource($url);
    $this->registerResource($resource1, $mapper);

    // Can't register the same url twice.
    try {
      $mapper->register($this->getResource($url));
      $this->assertTrue(FALSE);
    }
    catch (\Exception) {
      // When trying to register an existing url , an exception is thrown with
      // info about the original resource.
      $this->assertTrue(TRUE);
    }

    // Register a second url.
    $resource2 = $this->getResource($url2);
    $this->registerResource($resource2, $mapper);

    // Register a different perspective of the first resource.
    $resource1local = $resource1->createNewPerspective(ResourceLocalizer::LOCAL_URL_PERSPECTIVE, $localUrl);
    $mapper->registerNewPerspective($resource1local);
    $this->retrieveAndCheck($resource1, $mapper);
    $this->retrieveAndCheck($resource1local, $mapper);

    // Add a new revision of the first url.
    $resource1v2 = $resource1->createNewVersion();
    $mapper->registerNewVersion($resource1v2);
    $this->retrieveAndCheck($resource1, $mapper);
    $this->retrieveAndCheck($resource1v2, $mapper);
    $this->assertNotEquals($resource1->getVersion(), $resource1v2->getVersion());

    // Should be able to get local from first revision but not second.
    $this->assertEquals($localUrl,
      $mapper->get($resource1->getIdentifier(), ResourceLocalizer::LOCAL_URL_PERSPECTIVE, $resource1->getVersion())
        ->getFilePath()
    );
    $this->assertNull($mapper->get($resource1v2->getIdentifier(), ResourceLocalizer::LOCAL_URL_PERSPECTIVE, $resource1v2->getVersion()));

    // Add perspective to the new revision.
    $resource1v2local = $resource1v2->createNewPerspective(ResourceLocalizer::LOCAL_URL_PERSPECTIVE, $localUrl2);
    $mapper->registerNewPerspective($resource1v2local);
    $this->assertEquals($localUrl,
      $mapper->get($resource1local->getIdentifier(), ResourceLocalizer::LOCAL_URL_PERSPECTIVE, $resource1local->getVersion())
        ->getFilePath());
    $this->assertEquals($localUrl2,
      $mapper->get($resource1v2local->getIdentifier(), ResourceLocalizer::LOCAL_URL_PERSPECTIVE, $resource1v2local->getVersion())
        ->getFilePath());

    // The file mapper should not register other perspectives as sources.
    try {
      $mapper->register($this->getResource($localUrl));
      $this->assertTrue(FALSE);
    }
    catch (\Exception) {
      $this->assertTrue(TRUE);
    }

  }

  /**
   * Private.
   */
  private function registerResource($resource, $filemapper) {
    $success = $filemapper->register($resource);
    $this->assertTrue($success);
    $this->retrieveAndCheck($resource, $filemapper);
  }

  /**
   * Private.
   */
  private function retrieveAndCheck(DataResource $resource, $filemapper) {
    $retrieved = $filemapper->get($resource->getIdentifier(), $resource->getPerspective(), $resource->getVersion());
    $this->assertEquals($resource, $retrieved);
  }

  /**
   * Private.
   */
  private function getResource($url) {
    return new DataResource($url, 'text/csv', DataResource::DEFAULT_SOURCE_PERSPECTIVE);
  }

}
