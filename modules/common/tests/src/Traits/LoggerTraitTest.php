<?php

namespace Drupal\Tests\common\Traits;

use Drupal\common\LoggerTrait;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 * Class LoggerTraitTest.
 *
 * @package Drupal\Tests\common\Traits
 */
class LoggerTraitTest extends TestCase {

  use LoggerTrait;

  /**
   *
   */
  public function testLog() {
    $logger = $this->getLoggerChain();
    $this->setLoggerFactory($logger->getMock());

    $context = ['foo' => 'bar'];
    $this->log('log name', 'message', $context, 'critical');

    $this->assertEquals('log name', $logger->getStoredInput('names')[0]);
    $this->assertEquals('critical', $logger->getStoredInput('logs')[0]);
    $this->assertEquals('message', $logger->getStoredInput('logs')[1]);
    $this->assertEquals($context, $logger->getStoredInput('logs')[2]);
  }

  /**
   *
   */
  public function testError() {
    $logger = $this->getLoggerChain();
    $this->setLoggerFactory($logger->getMock());

    $this->error('some error');

    $this->assertEquals('some error', $logger->getStoredInput('errors')[0]);
  }

  /**
   *
   */
  public function testWarning() {
    $logger = $this->getLoggerChain();
    $this->setLoggerFactory($logger->getMock());

    $this->warning('some warning');

    $this->assertEquals('some warning', $logger->getStoredInput('warnings')[0]);
  }

  /**
   *
   */
  public function testNotice() {
    $logger = $this->getLoggerChain();
    $this->setLoggerFactory($logger->getMock());

    $this->notice('some notice');

    $this->assertEquals('some notice', $logger->getStoredInput('notices')[0]);
  }

  /**
   * Getter.
   */
  public function getLoggerChain() {
    return (new Chain($this))
      ->add(LoggerChannelFactory::class, 'get', LoggerChannelInterface::class, "names")
      ->add(LoggerChannelInterface::class, 'log', NULL, "logs")
      ->add(LoggerChannelInterface::class, 'error', NULL, "errors")
      ->add(LoggerChannelInterface::class, 'warning', NULL, "warnings")
      ->add(LoggerChannelInterface::class, 'notice', NULL, "notices");
  }

}
