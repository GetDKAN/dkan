<?php

namespace Drupal\Tests\datastore\Unit\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;

use Drupal\datastore\Plugin\QueueWorker\Import;
use Drupal\datastore\Service;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\metastore\Reference\ReferenceLookup;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;

/**
 * Test.
 */
class ImportTest extends TestCase {

  private $data = [
    'data' => [
      'identifier' => '12345',
      'version' => '23456',
    ],
  ];

  /**
   * Test.
   */
  public function testErrorPath() {

    $resultChain = (new Chain($this))
      ->add(Result::class, 'getStatus', Result::ERROR)
      ->add(Result::class, 'getError', 'Oops');

    $containerChain = $this->getContainerChain($resultChain->getMock());
    $containerChain
      ->add(LoggerChannelFactory::class, 'get', LoggerChannel::class)
      ->add(LoggerChannel::class, 'log', NULL, 'log');
    $container = $containerChain->getMock();

    $queueWorker = Import::create($container, [], '', ['cron' => ['lease_time' => 10800]]);
    $queueWorker->processItem((object) $this->data);

    // @todo Don't do this.
    $this->assertTrue(TRUE);
  }

  /**
   * Test.
   */
  public function testRequeue() {
    $result = (new Chain($this))
      ->add(Result::class, 'getStatus', Result::STOPPED)
      ->getMock();

    $containerChain = $this->getContainerChain($result);
    $container = $containerChain->getMock();

    $queueWorker = Import::create($container, [], '', ['cron' => ['lease_time' => 10800]]);
    $queueWorker->processItem((object) $this->data);

    $input = $containerChain->getStoredInput('create_item');
    $this->assertEquals($this->data['data'], $input[0]);
  }

  /**
   * Create base container chain object for mocking.
   */
  private function getContainerChain($result) {
    $config_factory = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', [])
      ->getMock();

    $options = (new Options())
      ->add('config.factory', $config_factory)
      ->add('dkan.datastore.service', Service::class)
      ->add('file_system', FileSystem::class)
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.metastore.reference_lookup', ReferenceLookup::class)
      ->add('queue', QueueFactory::class)
      ->add('database', Connection::class)
      ->add('dkan.datastore.database', Connection::class)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'import', [$result])
      ->add(Service::class, 'getQueueFactory', QueueFactory::class)
      ->add(Service::class, 'getResourceLocalizer', ResourceLocalizer::class)
      ->add(ResourceLocalizer::class, 'getFileSystem', FileSystem::class)
      ->add(QueueFactory::class, 'get', QueueInterface::class)
      ->add(QueueInterface::class, 'createItem', NULL, 'create_item')
      ->add(Connection::class, 'query', NULL);

    return $container_chain;
  }

}
