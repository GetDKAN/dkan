<?php

namespace Drupal\Tests\dkan_datastore\Unit\Service\ResourceProcessor;

use Drupal\dkan_datastore\Service\ResourceProcessor\ResourceDoesNotHaveDictionary;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Drupal\dkan_datastore\Service\ResourceProcessor\ResourceDoesNotHaveDictionary
 * @coversDefaultClass \Drupal\dkan_datastore\Service\ResourceProcessor\ResourceDoesNotHaveDictionary
 *
 * @group dkan
 * @group datastore
 * @group unit
 */
class ResourceDoesNotHaveDictionaryTest extends TestCase {

  public function testResourceDoesNotHaveDictionary() {
    $resource_id = 'test_id';
    $resource_version = 123;

    $exception = new ResourceDoesNotHaveDictionary($resource_id, $resource_version);

    $this->assertEquals($resource_id, $exception->getResourceId());
    $this->assertEquals(
      'No data-dictionary found for resource with id "' . $resource_id . '" and version "' . $resource_version . '".',
      $exception->getMessage()
    );
  }

}
