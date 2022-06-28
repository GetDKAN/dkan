<?php

namespace Drupal\Tests\datastore\Unit\Plugin\QueueWorker;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

use Drupal\common\Resource;
use Drupal\datastore\DataDictionary\AlterTableQueryFactoryInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;
use Drupal\datastore\Plugin\QueueWorker\DictionaryEnforcer;
use Drupal\metastore\DataDictionary\DataDictionaryDiscovery;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Service as MetastoreService;

use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use RootedData\RootedJsonData;

/**
 * Test \Drupal\Tests\datastore\Unit\Plugin\QueueWorker\DictionaryEnforcer.
 */
class DictionaryEnforcerTest extends TestCase {

  /**
   * HTTP host protocol and domain for testing download URL.
   *
   * @var string
   */
  protected const HOST = 'http://example.com';

  /**
   * Test the happy path in processItem().
   */
  public function testProcessItem() {
    $resource = new Resource('test.csv', 'text/csv');

    $containerChain = $this->getContainerChain($resource->getVersion())
      ->add(AlterTableQueryInterface::class, 'applyDataTypes');
    \Drupal::setContainer($containerChain->getMock($resource->getVersion()));

    $dictionaryEnforcer = DictionaryEnforcer::create(
       $containerChain->getMock(), [], '', ['cron' => ['lease_time' => 10800]]
     );

    $dictionaryEnforcer->processItem($resource);

    // Assert no exceptions are thrown in happy path.
    $errors = $containerChain->getStoredInput('error');
    $this->assertEmpty($errors);
  }

  /**
   * Test exception thrown in applyDataTypes() is caught and logged.
   */
  public function testProcessItemApplyDataTypesException() {
    $resource = new Resource('test.csv', 'text/csv');

    $errorMessage = "Something went wrong: " . uniqid();

    $containerChain = $this->getContainerChain($resource->getVersion())
      ->add(AlterTableQueryInterface::class, 'applyDataTypes', new \Exception($errorMessage));

    $dictionaryEnforcer = DictionaryEnforcer::create(
      $containerChain->getMock(), [], '', ['cron' => ['lease_time' => 10800]]
    );

    $dictionaryEnforcer->processItem($resource);

    // Assert the log contains the expected exception message thrown earlier.
    $this->assertEquals($errorMessage, $containerChain->getStoredInput('error')[0]);
  }

  /**
   * Test exception thrown in applyDataTypes() is caught and logged.
   */
  public function testProcessItemUnableToFindDataDictionaryForResourceException() {
    $resource = new Resource('test.csv', 'text/csv');

    $containerChain = $this->getContainerChain($resource->getVersion())
      ->add(DataDictionaryDiscoveryInterface::class, 'dictionaryIdFromResource', NULL);

    $dictionaryEnforcer = DictionaryEnforcer::create(
      $containerChain->getMock(), [], '', ['cron' => ['lease_time' => 10800]]
    );

    $dictionaryEnforcer->processItem($resource);
    $this->assertEquals(
      sprintf('No data-dictionary found for resource with id "%s" and version "%s".', $resource->getIdentifier(), $resource->getVersion()),
      $containerChain->getStoredInput('error')[0]
    );
  }

  /**
   * Get container chain.
   */
  private function getContainerChain(int $resource_version) {

    $options = (new Options())
      ->add('dkan.datastore.data_dictionary.alter_table_query_factory.mysql', AlterTableQueryFactoryInterface::class)
      ->add('dkan.metastore.data_dictionary_discovery', DataDictionaryDiscovery::class)
      ->add('logger.factory', LoggerChannelFactoryInterface::class)
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('dkan.metastore.data_dictionary_discovery', DataDictionaryDiscoveryInterface::class)
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->index(0);

    $json = '{"identifier":"foo","title":"bar","data":{"fields":[]}}';

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(LoggerChannelFactoryInterface::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'error')
      ->add(MetastoreService::class, 'get', new RootedJsonData($json))
      ->add(AlterTableQueryFactoryInterface::class, 'setConnectionTimeout', AlterTableQueryFactoryInterface::class)
      ->add(AlterTableQueryFactoryInterface::class, 'getQuery', AlterTableQueryInterface::class)
      ->add(DataDictionaryDiscoveryInterface::class, 'dictionaryIdFromResource', 'resource_id')
      ->add(PublicStream::class, 'getExternalUrl', self::HOST)
      ->add(StreamWrapperManager::class, 'getViaUri', PublicStream::class)
      ->add(ResourceMapper::class, 'get', Resource::class)
      ->add(Resource::class, 'getVersion', $resource_version);
  }

}
