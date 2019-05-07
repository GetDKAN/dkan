<?php

namespace Drupal\dkan_common\Tests;

use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Trait to sideload some utilities into other Unit tests.
 *
 * @author Yaasir Ketwaroo <yaasir.ketwaroo@semanticbits.com>
 */
trait DkanUnitTestTrait {

  /**
   * Helper to call projected methods.
   *
   * @param object $object
   *   Object with protected method.
   * @param string $methodName
   *   Method name.
   * @param mixed $arguments
   *   Additional arguments to pass to.
   *
   * @return mixed
   *   Results from method.
   *
   * @throws InvalidArgumentException
   *   If method is not defined in object.
   */
  protected function invokeProtectedMethod($object, string $methodName, ...$arguments) {

    $reflection = new \ReflectionClass($object);
    if (!$reflection->hasMethod($methodName)) {
      throw new \InvalidArgumentException("Method not found: {$methodName}");
    }

    $reflectedMethod = $reflection->getMethod($methodName);
    $reflectedMethod->setAccessible(TRUE);

    return $reflectedMethod->invoke($object, ...$arguments);
  }

  /**
   * Helper to get projected property.
   *
   * @param object $object
   *   Object with protected method.
   * @param string $property
   *   Property name.
   *
   * @return mixed
   *   Value of property.
   *
   * @throws InvalidArgumentException
   *   If property not found.
   */
  protected function accessProtectedProperty($object, string $property) {
    $reflection = new \ReflectionClass($object);
    if (!$reflection->hasProperty($property)) {
      throw new \InvalidArgumentException("Property not found: {$property}");
    }
    $reflectionProperty = $reflection->getProperty($property);
    $reflectionProperty->setAccessible(TRUE);
    return $reflectionProperty->getValue($object);
  }

  /**
   * Helper to set projected property.
   *
   * @param object $object
   *   Object with protected method.
   * @param string $property
   *   Property name.
   * @param mixed $value
   *   Value to set.
   *
   * @throws InvalidArgumentException
   *   If property not found.
   */
  protected function writeProtectedProperty($object, string $property, $value) {
    $reflection = new \ReflectionClass($object);
    if (!$reflection->hasProperty($property)) {
      throw new \InvalidArgumentException("Property not found: {$property}");
    }
    $reflectionProperty = $reflection->getProperty($property);
    $reflectionProperty->setAccessible(TRUE);
    $reflectionProperty->setValue($object, $value);
  }

  /**
   * Creates a mock instance of service container with `get` method.
   *
   * @return PHPUnit\Framework\MockObject\MockObject
   *   A mock.
   *
   * @throws \Exception
   *   If not in a unit test case.
   */
  protected function getMockContainer() {

    if (!($this instanceof TestCase)) {
      throw new \Exception('This function is meant to be used only with a PHPUnit test case.');
    }

    return $this->getMockBuilder(ContainerInterface::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();
  }

  /**
   * Creates and set an actual service container for those pesky instances where
   * static \Drupal methods are used.
   *
   * @param array $keyValue
   *   Key => service array.
   *
   * @return \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected function setActualContainer(array $keyValue) {
    $container = new ContainerBuilder();
    foreach ($keyValue as $key => $value) {
      $container->set($key, $value);
    }
    \Drupal::setContainer($container);
    return $container;
  }

}
