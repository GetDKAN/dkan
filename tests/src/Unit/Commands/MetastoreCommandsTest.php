<?php

namespace Drupal\Tests\metastore\Unit\Commands;

use Drupal\Core\Logger\LoggerChannel;
use Drupal\metastore\Commands\MetastoreCommands;
use Drupal\metastore\Storage\Data;
use Drupal\metastore\Storage\DataFactory;
use Drush\Log\DrushLoggerManager;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 *
 */
class MetastoreCommandsTest extends TestCase {

  /**
   *
   */
  public function testPublish() {
    // Set up for the difference between Drush 10 and Drush 11.
    $loggerClass = LoggerInterface::class;
    if (class_exists(DrushLoggerManager::class)) {
      $loggerClass = DrushLoggerManager::class;
    }
    $dataFactory = (new Chain($this))
      ->add(DataFactory::class, 'getInstance', Data::class)
      ->add(Data::class, 'publish', TRUE)
      ->getMock();

    $loggerChain = (new Chain($this))
      ->add($loggerClass, 'info', NULL, 'success');

    $logger = $loggerChain->getMock();

    $drush = new MetastoreCommands($dataFactory);
    $drush->setLogger($logger);
    $drush->publish('12345');

    $this->assertNotEmpty($loggerChain->getStoredInput('success'));
  }

  /**
   *
   */
  public function testPublishException() {
    // Set up for the difference between Drush 10 and Drush 11.
    $loggerClass = LoggerInterface::class;
    if (class_exists(DrushLoggerManager::class)) {
      $loggerClass = DrushLoggerManager::class;
    }
    $dataFactory = (new Chain($this))
      ->add(DataFactory::class, 'getInstance', Data::class)
      ->add(Data::class, 'publish', new \Exception("Some error."))
      ->getMock();

    $loggerChain = (new Chain($this))
      ->add($loggerClass, 'error', NULL, 'error');

    $logger = $loggerChain->getMock();

    $drush = new MetastoreCommands($dataFactory);
    $drush->setLogger($logger);

    $drush->publish('12345');

    $this->assertNotEmpty($loggerChain->getStoredInput('error'));
  }

}
