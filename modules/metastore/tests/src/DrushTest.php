<?php

namespace Drupal\Tests\metastore;

use Drupal\Core\Logger\LoggerChannel;
use Drupal\metastore\Drush;
use Drupal\metastore\Storage\AbstractEntityStorage;
use Drupal\metastore\Storage\MetastoreNodeStorageFactory;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 *
 */
class DrushTest extends TestCase {

  /**
   *
   */
  public function testPublish() {
    $dataFactory = (new Chain($this))
      ->add(MetastoreNodeStorageFactory::class, 'getInstance', AbstractEntityStorage::class)
      ->add(AbstractEntityStorage::class, 'publish', '12345')
      ->getMock();

    $loggerChain = (new Chain($this))
      ->add(LoggerInterface::class, 'info', NULL, 'success');

    $logger = $loggerChain->getMock();

    $drush = new Drush($dataFactory);
    $drush->setLogger($logger);
    $drush->publish('12345');

    $this->assertNotEmpty($loggerChain->getStoredInput('success'));
  }

  /**
   *
   */
  public function testPublishException() {
    $dataFactory = (new Chain($this))
      ->add(MetastoreNodeStorageFactory::class, 'getInstance', AbstractEntityStorage::class)
      ->add(AbstractEntityStorage::class, 'publish', new \Exception("Some error."))
      ->getMock();

    $loggerChain = (new Chain($this))
      ->add(LoggerChannel::class, 'error', NULL, 'error');

    $logger = $loggerChain->getMock();

    $drush = new Drush($dataFactory);
    $drush->setLogger($logger);

    $drush->publish('12345');

    $this->assertNotEmpty($loggerChain->getStoredInput('error'));
  }

}
