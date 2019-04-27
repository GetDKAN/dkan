<?php

namespace Drupal\Tests\dkan_api\Unit\Controller;

use Drupal\dkan_api\Controller\Organization as ControllerOrginzation;
use Drupal\dkan_api\Storage\Organization as StorageOrganization;
use Drupal\dkan_common\Tests\DkanTestBase;

/**
 * Tests Drupal\dkan_api\Controller\Organization.
 *
 * @coversDefaultClass Drupal\dkan_api\Controller\Organization
 * @group dkan_api
 * @author Yaasir Ketwaroo <yaasir.ketwaroo@semanticbits.com>
 */
class OrganizationTest extends DkanTestBase {

  /**
   * Tests getJsonSchema().
   */
  public function testGetJsonSchema() {
    $this->markTestIncomplete('Code under test seems to not do anything.');

  }

  /**
   * Tests getStorage().
   */
  public function testGetStorage() {

    // Setup.
    $mock = $this->getMockBuilder(ControllerOrginzation::class)
      ->disableOriginalCOnstructor()
    // Override nothing.
      ->setMethods(NULL)
      ->getMock();
    $mockStorageOrganization = $this->createMock(StorageOrganization::class);
    $mockContainer = $this->getMockContainer();
    $this->writeProtectedProperty($mock, 'container', $mockContainer);

    // Expect.
    $mockContainer->expects($this->once())
      ->method('get')
      ->with('dkan_api.storage.organization')
      ->willReturn($mockStorageOrganization);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getStorage');
    $this->assertSame($mockStorageOrganization, $actual);
  }

}
