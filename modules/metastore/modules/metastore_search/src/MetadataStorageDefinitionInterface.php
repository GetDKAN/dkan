<?php

namespace Drupal\metastore_search;

use Drupal\Core\TypedData\DataDefinitionInterface;

interface MetadataStorageDefinitionInterface {

  /**
   * Returns the names of the field's subproperties.
   *
   * A field is a list of items, and each item can contain one or more
   * properties. All items for a given field contain the same property names,
   * but the values can be different for each item.
   *
   * For example, an email field might just contain a single 'value' property,
   * while a link field might contain 'title' and 'url' properties, and a text
   * field might contain 'value', 'summary', and 'format' properties.
   *
   * @return string[]
   *   The property names.
   */
  public function getPropertyNames(): array;

  /**
   * Gets the definition of a contained property.
   *
   * @param string $name
   *   The name of property.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface|null
   *   The definition of the property or NULL if the property does not exist.
   */
  public function getPropertyDefintion(): ?DataDefinitionInterface;

  /**
   * Retrieves the properties exposed by the underlying complex data type.
   *
   * Property names have to start with a letter or an underscore, followed by
   * any number of letters, numbers and underscores.
   *
   * @return \Drupal\Core\TypedData\DataDefinitionInterface[]
   *   An associative array of property data types, keyed by the property name.
   */
  public function getPropertyDefintions(): array;

}
