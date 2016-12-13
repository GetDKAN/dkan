<?php

namespace Drupal\DKANExtension\Context\Annotation;

use Behat\Behat\Context\Annotation\AnnotationReader;
use Drupal\DrupalExtension\Hook\Dispatcher;
use ReflectionMethod;

/**
 * Annotated contexts reader.
 *
 * @see \Behat\Behat\Context\Loader\AnnotatedLoader
 */
class Reader implements AnnotationReader {

  /**
   * @var string
   */
  private static $regex = '/^\@(beforenodecreate|afternodecreate|beforetermcreate|aftertermcreate|beforeusercreate|afterusercreate|beforedkanentitycreate|afterdkanentitycreate)(?:\s+(.+))?$/i';

  /**
   * @var string[]
   */
  private static $classes = array(
    'afternodecreate' => 'Drupal\DrupalExtension\Hook\Call\AfterNodeCreate',
    'aftertermcreate' => 'Drupal\DrupalExtension\Hook\Call\AfterTermCreate',
    'afterusercreate' => 'Drupal\DrupalExtension\Hook\Call\AfterUserCreate',
    'beforenodecreate' => 'Drupal\DrupalExtension\Hook\Call\BeforeNodeCreate',
    'beforetermcreate' => 'Drupal\DrupalExtension\Hook\Call\BeforeTermCreate',
    'beforeusercreate' => 'Drupal\DrupalExtension\Hook\Call\BeforeUserCreate',
    'beforedkanentitycreate' => 'Drupal\DKANExtension\Hook\Call\BeforeDKANEntityCreate',
    'afterdkanentitycreate' => 'Drupal\DKANExtension\Hook\Call\AfterDKANEntityCreate',
  );

  /**
   * {@inheritDoc}
   */
  public function readCallee($contextClass, ReflectionMethod $method, $docLine, $description) {

    if (!preg_match(self::$regex, $docLine, $match)) {
      return null;
    }

    $type = strtolower($match[1]);
    $class = self::$classes[$type];
    $pattern = isset($match[2]) ? $match[2] : null;
    $callable = array($contextClass, $method->getName());

    return new $class($pattern, $callable, $description);
  }

}
