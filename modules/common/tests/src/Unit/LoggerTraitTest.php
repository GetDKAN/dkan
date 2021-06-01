<?php

namespace Drupal\Tests\common\Unit;

use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Tests\common\Unit\Mocks\Logging;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class LoggerTraitTest extends TestCase {

  /**
   *
   */
  public function test() {
    $loggerChannelFactory = (new Chain($this))
      ->add(LoggerChannelFactory::class)
      ->getMock();

    $class = new Logging();
    $class->setLoggerName('blah');
    $class->setLoggerFactory($loggerChannelFactory);
    $class->do();
    $this->assertTrue(TRUE);
  }

}
