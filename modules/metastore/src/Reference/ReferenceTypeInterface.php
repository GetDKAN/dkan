<?php

namespace Drupal\metastore\Reference;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Simple container for reference information.
 */
interface ReferenceTypeInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {

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
   * Get the reference type.
   *
   * @return string
   *   Current options: schema, id, resource.
   */
  public function type(): string;

  /**
   * Set a metadata context for the current reference.
   *
   * Use of this method is optional and may not be needed in most reference
   * types. But when needed you may pass the full metadata object and chain
   * additional methods such as reference().
   *
   * @param mixed $context
   *   The metadata context for the reference. Usually, a JSON object.
   *
   * @return $this
   */
  public function setContext($context): self;

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
   * @param bool $showId
   *   Return format for value includes identifier.
   *
   * @return mixed
   *   The full property value ready for insertion into parent object.
   */
  public function dereference(string $identifier, bool $showId);

}
