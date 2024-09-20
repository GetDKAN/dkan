<?php

namespace Drupal\Tests\datastore\Kernel\Plugin\QueueWorker;

use Drupal\common\DataResource;
use Drupal\datastore\Plugin\QueueWorker\PostImportResourceProcessor;
use Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer;
use Drupal\datastore\Service\ResourceProcessor\ResourceDoesNotHaveDictionary;
use Drupal\KernelTests\KernelTestBase;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;
use Drupal\metastore\ResourceMapper;

/**
 * Test \Drupal\datastore\Plugin\QueueWorker\PostImportResourceProcessor.
 *
 * @coversDefaultClass \Drupal\datastore\Plugin\QueueWorker\PostImportResourceProcessor
 * @covers \Drupal\datastore\Plugin\QueueWorker\PostImportResourceProcessor
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class PostImportResourceProcessorTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

  protected $strictConfigSchema = FALSE;

  /**
   * @covers ::postImportProcessItem
   */
  public function testPostImportProcessItemNoDictionary() {
    // Tell the processor to use reference mode for dictionary enforcement.
    $this->config('metastore.settings')
      ->set('data_dictionary_mode', DataDictionaryDiscoveryInterface::MODE_REFERENCE)
      ->save();

    // Mock the resource mapper to return a given data resource with no
    // describedBy property.
    $resource = new DataResource('test.csv', 'text/csv');
    $resource_mapper = $this->getMockBuilder(ResourceMapper::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();
    $resource_mapper->expects($this->once())
      ->method('get')
      ->willReturn($resource);
    $this->container->set('dkan.metastore.resource_mapper', $resource_mapper);

    // Mock the dictionary enforcer to throw an exception so that we can avoid
    // node type dependenies.
    $no_dictionary_exception = new ResourceDoesNotHaveDictionary('test', 123);
    $enforcer = $this->getMockBuilder(DictionaryEnforcer::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['process'])
      ->getMock();
    $enforcer->expects($this->once())
      ->method('process')
      ->willThrowException($no_dictionary_exception);
    $this->container->set('dkan.datastore.service.resource_processor.dictionary_enforcer', $enforcer);

    // Create a post import processor.
    /** @var \Drupal\datastore\Plugin\QueueWorker\PostImportResourceProcessor $processor */
    $processor = PostImportResourceProcessor::create(
      $this->container,
      [],
      'post_import',
      [
        'cron' => [
          'time' => 180,
          'lease_time' => 10800,
        ],
      ]
    );

    // The results of post import processing should reflect that the resource
    // does not have a data dictionary.
    $result = $processor->postImportProcessItem($resource);
    $this->assertEquals(
      'Resource test does not have a data dictionary.',
      $result->getPostImportMessage()
    );
    $this->assertEquals(
      'done',
      $result->getPostImportStatus()
    );
  }

}
