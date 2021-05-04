<?php

namespace Drupal\Tests\datastore\Plugin\QueuWorker;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use PHPUnit\Framework\TestCase;
use Drupal\datastore\Plugin\QueueWorker\Import;
use Drupal\datastore\Service;
use MockChain\Chain;
use MockChain\Options;
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

    \Drupal::setContainer($container);

    $queueWorker = Import::create($container, [], '', '');
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

    \Drupal::setContainer($container);

    $queueWorker = Import::create($container, [], '', '');
    $queueWorker->processItem((object) $this->data);

    $input = $containerChain->getStoredInput('create_item');
    $this->assertEquals($this->data['data'], $input[0]);
  }

  /**
   * Private.
   */
  private function getContainerChain($result) {
    $options = (new Options())
      ->add("logger.factory", LoggerChannelFactory::class)
      ->add("dkan.datastore.service", Service::class)
      ->add('queue', QueueFactory::class)
      ->index(0);

    $containerChain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(Service::class, 'import', [$result])
      ->add(QueueFactory::class, 'get', QueueInterface::class)
      ->add(QueueInterface::class, 'createItem', NULL, 'create_item');

    return $containerChain;
  }

}
