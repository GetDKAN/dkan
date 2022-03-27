<?php

namespace Drupal\metastore\Reference;

/**
 * Interface for Metastore Reference Map Service.
 *
 * This is intended as a transitional service to help define reference
 * types for different schema properties. We hope to soon replace with a
 * more flexible schema system.
 *
 * @see https://github.com/GetDKAN/dkan/issues/3761
 */
interface ReferenceMapInterface {

  /**
   * Get a map of all reference types keyed by property name.
   */
  public function getAllReferences(string $schemaId): array;

  /**
   * Load a full reference object for a schema and property name.
   *
   * @return null|\Drupal\metastore\Reference\ReferenceTypeInterface
   */
  public function getReference(string $schemaId, string $propertyName): ?ReferenceTypeInterface;

}
