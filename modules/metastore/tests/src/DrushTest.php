<?php

namespace Drupal\Tests\metastore;

use Drupal\Core\Logger\LoggerChannel;
use Drupal\metastore\Drush;
use Drupal\metastore\Storage\Data;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

class DrushTest extends TestCase {

  public function test() {
    $data = (new Chain($this))
      ->add(Data::class, 'publish', null)
      ->getMock();

    $loggerChain = (new Chain($this))
      ->add(LoggerChannel::class, 'success', null, 'success');

    $logger = $loggerChain->getMock();

    $drush = new Drush($data);
    $drush->setLogger($logger);
    $drush->publish('12345');

    $this->assertNotEmpty($loggerChain->getStoredInput('success'));

  }

}
