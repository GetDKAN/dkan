<?php

namespace Drupal\metastore\Reference;

/**
 * Metastore dereferencer.
 */
class Dereferencer implements DereferencerInterface {

  /**
   * Reference map service.
   *
   * @var \Drupal\metastore\Reference\ReferenceMapInterface
   */
  private ReferenceMapInterface $referenceMap;

  /**
   * Constructor.
   *
   * @param \Drupal\metastore\Reference\ReferenceMapInterface $referenceMap
   *   ReferenceMap service, to find a schema's referenced properties and
   *   their types.
   */
  public function __construct(ReferenceMapInterface $referenceMap) {
    $this->referenceMap = $referenceMap;
  }

  /**
   * {@inheritdoc}
   */
  public function dereference(object $metadata, string $schemaId = 'dataset') {
    $refs = $this->referenceMap->getAllReferences($schemaId);
    // Cycle through the dataset properties we seek to dereference.
    foreach ($refs as $propertyName => $reference) {
      if (!isset($metadata->{$propertyName})) {
        continue;
      }

      $value = $metadata->{$propertyName};
      $metadata->{$propertyName} = $this->dereferenceProperty($reference, $value, FALSE);

      if (is_null($metadata->{$propertyName})) {
        unset($metadata->{$propertyName});
      }
      else {
        $metadata->{"%Ref:{$propertyName}"} = $this->dereferenceProperty($reference, $value, TRUE);
      }
    }
    return $metadata;
  }

  /**
   * Dereferences property and handles empty values if any.
   *
   * @param \Drupal\metastore\Reference\ReferenceTypeInterface $reference
   *   The reference information.
   * @param string|string[] $value
   *   The value to dereference.
   * @param bool $showId
   *   Wrap the value in an object with identifier/data properties?
   */
  private function dereferenceProperty(ReferenceTypeInterface $reference, $value, bool $showId = FALSE) {
    if (!is_array($value)) {
      return $reference->dereference($value, $showId);
    }

    $dereferenced = [];
    foreach ($value as $identifier) {
      $dereferenced[] = $reference->dereference($identifier, $showId);
    }
    return $dereferenced;
  }

}
