<?php

namespace Drupal\Tests\datastore\Unit\Plugin\QueueWorker;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryFactoryInterface;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;
use Drupal\datastore\Plugin\QueueWorker\DictionaryEnforcer;
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
   * Test the happy path in processItem().
   */
  public function testProcessItem() {

    $containerChain = $this->getContainerChain()
      ->add(AlterTableQueryInterface::class, 'applyDataTypes');

    $dictionaryEnforcer = DictionaryEnforcer::create(
       $containerChain->getMock(), [], '', ['cron' => ['lease_time' => 10800]]
     );

    $dictionaryEnforcer->processItem((object) [
      'dictionary_identifier' => 'foobar',
      'datastore_table' => 'datastore_foobar',
    ]);

    // Assert no exceptions are thrown in happy path.
    $errors = $containerChain->getStoredInput('error');
    $this->assertEmpty($errors);
  }

  /**
   * Test exception thrown in processItem().
   */
  public function testProcessItemException() {

    $errorMessage = "Something went wrong: " . uniqid();

    $containerChain = $this->getContainerChain()
      ->add(AlterTableQueryInterface::class, 'applyDataTypes', new \Exception($errorMessage));

    $dictionaryEnforcer = DictionaryEnforcer::create(
      $containerChain->getMock(), [], '', ['cron' => ['lease_time' => 10800]]
    );

    $dictionaryEnforcer->processItem((object) [
      'dictionary_identifier' => 'foobar',
      'datastore_table' => 'datastore_foobar',
    ]);

    // Assert the log contains the expected exception message thrown earlier.
    $this->assertEquals($errorMessage, $containerChain->getStoredInput('error')[0]);
  }

  /**
   * Get container chain.
   */
  private function getContainerChain() {

    $options = (new Options())
      ->add('dkan.datastore.data_dictionary.alter_table_query_factory.mysql', AlterTableQueryFactoryInterface::class)
      ->add('logger.factory', LoggerChannelFactoryInterface::class)
      ->add('dkan.metastore.service', MetastoreService::class)
      ->index(0);

    $json = '{"identifier":"foo","title":"bar","data":{"fields":[]}}';

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(LoggerChannelFactoryInterface::class, 'get', LoggerChannelInterface::class)
      ->add(LoggerChannelInterface::class, 'error', NULL, 'error')
      ->add(MetastoreService::class, 'get', new RootedJsonData($json))
      ->add(AlterTableQueryFactoryInterface::class, 'setConnectionTimeout', AlterTableQueryFactoryInterface::class)
      ->add(AlterTableQueryFactoryInterface::class, 'getQuery', AlterTableQueryInterface::class);
  }

}
