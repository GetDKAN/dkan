<?php

namespace Drupal\dkan_common\Tests\Mock;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class MockChain.
 *
 * @codeCoverageIgnore
 */
class Chain {

  private $testCase;
  private $definitons = [];
  private $root = NULL;
  private $storeIds = [];
  private $store = [];

  /**
   * Constructor.
   */
  public function __construct(TestCase $case) {
    $this->testCase = $case;
  }

  /**
   * Add.
   */
  public function add($objectClass, $method, $return, $storeId = NULL) {
    if (!$this->root) {
      $this->root = $objectClass;
    }

    $this->definitons[$objectClass][$method] = $return;

    if ($storeId) {
      $this->storeIds[$objectClass][$method] = $storeId;
    }

    return $this;
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
  private function setupMethodReturns($objectClass, MockObject $mock, $method) {
    $return = $this->getReturn($objectClass, $method);
    $storeId = $this->getStoreId($objectClass, $method);

    if ($storeId) {
      $this->setStorageWithReturn($mock, $method, $storeId, $return);
    }
    elseif ($return instanceof Options || $return instanceof Sequence) {
      $this->setMultipleReturnsBasedOnInput($mock, $method, $return);
    }
    elseif (is_object($return)) {
      $this->setObjectReturnOrException($mock, $method, $return);
    }
    elseif (is_string($return)) {
      $this->setReturnsBasedOnStringType($mock, $method, $return, $objectClass);
    }
    $mock->method($method)->willReturn($return);
  }

  /**
   * Private.
   */
  private function setStorageWithReturn(MockObject $mock, $method, $storeId, $return) {
    $mock->method($method)->willReturnCallback(function ($input) use ($storeId, $return) {
      $this->store[$storeId] = $input;
      if (is_object($return)) {
        if ($return instanceof \Exception) {
          throw $return;
        }
        return $return;
      }
      if (is_string($return)) {
        if (class_exists($return)) {
          return $this->build($return);
        }
        return $return;
      }
      elseif (is_bool($return) || is_array($return) || is_null($return)) {
        return $return;
      }
    });
  }

  /**
   * Private.
   */
  private function setObjectReturnOrException(MockObject $mock, $method, $return) {
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
  private function setMultipleReturnsBasedOnInput(MockObject $mock, $method, $return) {

    if ($return instanceof Options) {
      $this->setMultipleReturnsBasedOnInputOptions($mock, $method, $return);
    }
    elseif ($return instanceof Sequence) {
      $this->setMultipleReturnsBasedOnInputSequence($mock, $method, $return);
    }
  }

  /**
   * Private.
   */
  private function setMultipleReturnsBasedOnInputOptions(MockObject $mock, $method, Options $return) {
    $storeId = $return->getUse();
    $mock->method($method)
      ->willReturnCallback(function ($input) use ($return, $storeId) {
        foreach ($return->options() as $possible_input) {
          $actual_input = isset($storeId) ? $this->store[$storeId] : $input;
          if ($actual_input == $possible_input) {
            $output = $return->return($actual_input);
            if (is_string($output)) {
              return $this->build($output);
            }
            return $output;
          }
        }
      });
  }

  /**
   * Private.
   */
  private function setMultipleReturnsBasedOnInputSequence(MockObject $mock, $method, Sequence $return) {
    $mock->method($method)
      ->willReturnCallback(function () use ($return) {
        $output = $return->return();
        if (is_string($output)) {
          return $this->build($output);
        }
        return $output;
      });
  }

  /**
   * Private.
   */
  private function setReturnsBasedOnStringType(MockObject $mock, $method, string $return, $objectClass) {
    // We accept complex returns as json strings.
    if (class_exists($return)) {
      if ($return == $objectClass) {
        $mock->method($method)->willReturn($mock);
      }
      else {
        $mock->method($method)->willReturn($this->build($return));
      }
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

  /**
   * Private.
   */
  private function getStoreId($objectClass, $method) {
    if (isset($this->storeIds[$objectClass][$method])) {
      return $this->storeIds[$objectClass][$method];
    }
    return NULL;
  }

}
