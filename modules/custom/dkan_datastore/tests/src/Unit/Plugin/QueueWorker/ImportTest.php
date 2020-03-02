<?php

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Queue\QueueFactory;
use Drupal\dkan_datastore\Plugin\QueueWorker\Import;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Drupal\dkan_datastore\Service;
use Procrastinator\Result;
use Drupal\Core\Queue\QueueInterface;

/**
 *
 */
class Import2Test extends TestCase {

  /**
   *
   */
  public function testHappyPath() {
    $result = (new Chain($this))
      ->add(Result::class, 'getStatus', Result::DONE)
      ->getMock();

    $containerChain = $this->getContainerChain($result);
    $containerChain
      ->add(LoggerChannelFactory::class, 'get', LoggerChannel::class)
      ->add(LoggerChannel::class, 'log', NULL, 'log');
    $container = $containerChain->getMock();

    $data = [
      'uuid' => '12345',
    ];

    \Drupal::setContainer($container);

    $queueWorker = Import::create($container, [], '', '');
    $queueWorker->processItem($data);

    $this->assertEquals("Import for 12345 completed.", $containerChain->getStoredInput('log')[1]);
  }

  /**
   *
   */
  public function testErrorPath() {
    $result = (new Chain($this))
      ->add(Result::class, 'getStatus', Result::ERROR)
      ->add(Result::class, 'getError', 'Oops')
      ->getMock();

    $containerChain = $this->getContainerChain($result);
    $containerChain
      ->add(LoggerChannelFactory::class, 'get', LoggerChannel::class)
      ->add(LoggerChannel::class, 'log', NULL, 'log');
    $container = $containerChain->getMock();

    $data = [
      'uuid' => '12345',
    ];

    \Drupal::setContainer($container);

    $queueWorker = Import::create($container, [], '', '');
    $queueWorker->processItem($data);

    $this->assertEquals("Import for 12345 returned an error: Oops", $containerChain->getStoredInput('log')[1]);
  }

  /**
   *
   */
  public function testRequeue() {
    $result = (new Chain($this))
      ->add(Result::class, 'getStatus', Result::STOPPED)
      ->getMock();

    $containerChain = $this->getContainerChain($result);
    $container = $containerChain->getMock();

    $data = [
      'uuid' => '12345',
    ];

    \Drupal::setContainer($container);

    $queueWorker = Import::create($container, [], '', '');
    $queueWorker->processItem($data);

    $this->assertEquals([$data], $containerChain->getStoredInput('create_item'));
  }

  /**
   *
   */
  private function getContainerChain($result) {
    $options = (new Options())
      ->add("logger.factory", LoggerChannelFactory::class)
      ->add("dkan_datastore.service", Service::class)
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
