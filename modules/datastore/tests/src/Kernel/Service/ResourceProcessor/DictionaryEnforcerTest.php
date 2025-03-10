<?php

namespace Drupal\Tests\datastore\Kernel\Service\ResourceProcessor;

use Drupal\common\DataResource;
use Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer;
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

  /**
   * @covers ::process
   */
  public function testProcessModeNone() {
    // Explicitly set to none.
    $this->config('metastore.settings')
      ->set('data_dictionary_mode', DataDictionaryDiscoveryInterface::MODE_NONE)
      ->save();

    // Mock a DictionaryEnforcer so that we can set expectations on its methods.
    $dictionary_enforcer = $this->getMockBuilder(DictionaryEnforcer::class)
      ->setConstructorArgs([
        $this->container->get('dkan.datastore.data_dictionary.alter_table_query_builder.mysql'),
        $this->container->get('dkan.metastore.service'),
        $this->container->get('dkan.metastore.data_dictionary_discovery'),
        $this->container->get('dkan.datastore.database_table_factory'),
      ])
      ->onlyMethods(['getDataDictionaryForResource', 'applyDictionary'])
      ->getMock();
    // We expect that these methods will never be called.
    $dictionary_enforcer->expects($this->never())
      ->method('getDataDictionaryForResource');
    $dictionary_enforcer->expects($this->never())
      ->method('applyDictionary');

    // We can't assert against the return value because process() returns void.
    $dictionary_enforcer->process(new DataResource('test.csv', 'text/csv'));
  }

}
