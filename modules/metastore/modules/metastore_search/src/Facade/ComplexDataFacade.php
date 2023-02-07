<?php

namespace Drupal\metastore_search\Facade;

use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\TraversableTypedDataInterface;

/**
 * Base complex data facade class.
 *
 * @codeCoverageIgnore
 */
abstract class ComplexDataFacade implements \Iterator, ComplexDataInterface {

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getDataDefinition() {
    // @todo Implement getDataDefinition() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function get($property_name) {
    // @todo Implement get() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function set($property_name, $value, $notify = TRUE) {
    // @todo Implement set() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getProperties($include_computed = FALSE) {
    // @todo Implement getProperties() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  #[\ReturnTypeWillChange]
  public function toArray() {
    // @todo Implement toArray() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  #[\ReturnTypeWillChange]
  public function isEmpty() {
    // @todo Implement isEmpty() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  #[\ReturnTypeWillChange]
  public function current() {
    // @todo Implement current() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  #[\ReturnTypeWillChange]
  public function next() {
    // @todo Implement next() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  #[\ReturnTypeWillChange]
  public function key() {
    // @todo Implement key() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  #[\ReturnTypeWillChange]
  public function valid() {
    // @todo Implement valid() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  #[\ReturnTypeWillChange]
  public function rewind() {
    // @todo Implement rewind() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  #[\ReturnTypeWillChange]
  public function onChange($name) {
    // @todo Implement onChange() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public static function createInstance(
    $definition,
    $name = NULL,
    TraversableTypedDataInterface $parent = NULL
  ) {
    // @todo Implement createInstance() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getValue() {
    // @todo Implement getValue() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function setValue($value, $notify = TRUE) {
    // @todo Implement setValue() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getString() {
    // @todo Implement getString() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getConstraints() {
    // @todo Implement getConstraints() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function validate() {
    // @todo Implement validate() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function applyDefaultValue($notify = TRUE) {
    // @todo Implement applyDefaultValue() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getName() {
    // @todo Implement getName() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getParent() {
    // @todo Implement getParent() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getRoot() {
    // @todo Implement getRoot() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getPropertyPath() {
    // @todo Implement getPropertyPath() method.
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function setContext(
    $name = NULL,
    TraversableTypedDataInterface $parent = NULL
  ) {
    // @todo Implement setContext() method.
  }

}
