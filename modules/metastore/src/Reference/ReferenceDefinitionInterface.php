<?php

namespace Drupal\metastore\Reference;

/**
 * Simple container for reference information.
 */
interface ReferenceDefinitionInterface {

  /**
   * Get the property name.
   *
   * @return string
   *   Property name for reference.
   */
  public function property(): string;

  /**
   * Get the schema ID referenced.
   *
   * @return string|null
   *   A valid schema ID, or NULL if n/a.
   */
  public function schemaId(): ?string;

  /**
   * Return a reference ID for a metadata value.
   *
   * @param mixed $value
   *   Any value from a metadata object property.
   *
   * @return string
   *   Metastore identifier for this referenced value.
   */
  public function reference($value): string;

  /**
   * Return the full value for a referencing identifier.
   *
   * @param string $identifier
   *   Metastore identifier or other type of reference value.
   *
   * @return mixed
   *   The full property value ready for insertion into parent object.
   */
  public function dereference(string $identifier);

}
