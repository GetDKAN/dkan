<?php

namespace Drupal\dkan_common\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class MockChain.
 *
 * @codeCoverageIgnore
 */
class MockChain {

  private $testCase;
  private $definitons = [];
  private $root = NULL;

  /**
   * Constructor.
   */
  public function __construct(TestCase $case) {
    $this->testCase = $case;
  }

  /**
   * Add.
   */
  public function add($objectClass, $method, $return) {
    if (!$this->root) {
      $this->root = $objectClass;
    }
    $this->definitons[$objectClass][$method] = $return;
  }

  /**
   * Get Mock.
   */
  public function getMock() {
    return $this->build($this->root);
  }

  /**
   * Private.
   */
  private function build($objectClass) {
    $methods = $this->getMethods($objectClass);

    $mock = $this->testCase->getMockBuilder($objectClass)
      ->disableOriginalConstructor()
      ->setMethods($methods)
      ->getMockForAbstractClass();

    foreach ($methods as $method) {
      $this->setupMethodReturns($objectClass, $mock, $method);
    }
    return $mock;
  }

  /**
   * Private.
   */
  private function setupMethodReturns($objectClass, $mock, $method) {
    $return = $this->getReturn($objectClass, $method);

    if (is_array($return)) {
      $this->setMultipleReturnsBasedOnInput($mock, $method, $return);
    }
    elseif (is_object($return)) {
      $this->setObjectReturnOrException($mock, $method, $return);
    }
    elseif (is_string($return)) {
      $this->setReturnsBasedOnStringType($mock, $method, $return);
    }
    else {
      throw new \Exception("Bad definition");
    }
  }

  /**
   * Private.
   */
  private function setObjectReturnOrException($mock, $method, $return) {
    if ($return instanceof \Exception) {
      $mock->method($method)->willThrowException($return);
    }
    else {
      $mock->method($method)->willReturn($return);
    }
  }

  /**
   * Private.
   */
  private function setMultipleReturnsBasedOnInput($mock, $method, array $return) {
    $mock->method($method)->willReturnCallback(function ($input) use ($return) {
      foreach ($return as $possible_input => $returnObjectClass) {
        if ($input == $possible_input) {
          return $this->build($returnObjectClass);
        }
      }
    });
  }

  /**
   * Private.
   */
  private function setReturnsBasedOnStringType($mock, $method, string $return) {
    // We accept complex returns as json strings.
    $json = json_decode($return);
    if (class_exists($return)) {
      $mock->method($method)->willReturn($this->build($return));
    }
    elseif ($json != FALSE) {
      $mock->method($method)->willReturn($json);
    }
    else {
      $mock->method($method)->willReturn($return);
    }
  }

  /**
   * Private.
   */
  private function getMethods($objectClass) {
    $methods = [];

    if (isset($this->definitons[$objectClass])) {
      foreach ($this->definitons[$objectClass] as $method => $blah) {
        $methods[] = $method;
      }
    }

    return $methods;
  }

  /**
   * Private.
   */
  private function getReturn($objectClass, $method) {
    if (isset($this->definitons[$objectClass][$method])) {
      return $this->definitons[$objectClass][$method];
    }
    return NULL;
  }

}
