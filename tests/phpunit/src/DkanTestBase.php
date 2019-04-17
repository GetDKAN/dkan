<?php

namespace Dkan\PhpUnit;

use Drupal\Tests\UnitTestCase;

class DkanTestBase extends UnitTestCase {

    protected $dkanDirectory;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
        parent::setUp();
        $this->dkanDirectory = realpath(dirname(__FILE__) . '/../../../');
    }

    /**
     * Helper to call projected methods
     * 
     * @param object $object
     * @param string $methodName
     * @param variable-lengrh $arguments Additional arguments to pass to 
     * @return mixed
     * @throws InvalidArgumentException If method is not defined in object
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
     * @param string $property
     * @return mixed
     * @throws InvalidArgumentException
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
     * @param string $property
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function writeProtectedProperty($object, string $property, $value) {
        $reflection = new \ReflectionClass($object);
        if (!$reflection->hasProperty($property)) {
            throw new \InvalidArgumentException("Property not found: {$property}");
        }
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(TRUE);
        return $reflectionProperty->setValue($object, $value);
    }

}
