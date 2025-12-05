<?php

namespace Drupal\metastore;

/**
 * Service to find metastore items referencing an identifier.
 */
interface ReferenceLookupInterface {

  /**
   * Get UUIDs of all metastore items referencing an ID through a property.
   *
   * @param string $schemaId
   *   The type of metadata to look for references within.
   * @param string $referenceId
   *   The UUID of the reference we're looking for.
   * @param string $propertyId
   *   The metadata property we hope to find it in.
   *
   * @return array
   *   Array of metastore UUIDs for matching items.
   */
  public function getReferencers(string $schemaId, string $referenceId, string $propertyId);

}
