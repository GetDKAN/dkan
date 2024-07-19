<?php

namespace Drupal\Tests\datastore\Kernel\Service\ResourceProcessor;

use Drupal\common\DataResource;
use Drupal\datastore\Service\ResourceProcessor\ResourceDoesNotHaveDictionary;
use Drupal\KernelTests\KernelTestBase;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;

/**
 * @coversDefaultClass \Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer
 * @covers \Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class DictionaryEnforcerTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

  /**
   * Test exception thrown if no dictionary is found for resource.
   *
   * @covers ::getDataDictionaryForResource
   */
  public function testNoDictionaryIdFoundForResourceException() {
    $resource = new DataResource('test.csv', 'text/csv');

    $discovery = $this->getMockBuilder(DataDictionaryDiscoveryInterface::class)
      ->onlyMethods(['dictionaryIdFromResource'])
      ->getMockForAbstractClass();
    $discovery->expects($this->once())
      ->method('dictionaryIdFromResource')
      ->willReturn(NULL);

    $this->container->set('dkan.metastore.data_dictionary_discovery', $discovery);

    /** @var \Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer $enforcer */
    $enforcer = $this->container->get('dkan.datastore.service.resource_processor.dictionary_enforcer');
    $ref_get = new \ReflectionMethod($enforcer, 'getDataDictionaryForResource');
    $ref_get->setAccessible(TRUE);

    $this->expectException(ResourceDoesNotHaveDictionary::class);
    $this->expectExceptionMessage('No data-dictionary found for resource with id');
    $ref_get->invokeArgs($enforcer, [$resource]);
  }

}
