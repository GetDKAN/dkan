<?php

namespace Drupal\metastore\Reference;

/**
 * Interface for metastore referencer service.
 */
interface ReferencerInterface {

  /**
   * Replaces some dataset property values with references.
   *
   * @param object $metadata
   *   Dataset json object.
   * @param string $schemaId
   *   SchemaId for the JSON data.
   *
   * @return object
   *   Json object modified with references to some of its properties' values.
   */
  public function reference(object $metadata, string $schemaId = 'dataset');

}
