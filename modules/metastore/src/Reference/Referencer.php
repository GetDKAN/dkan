<?php

namespace Drupal\metastore\Reference;

/**
 * Metastore referencer service.
 */
class Referencer implements ReferencerInterface {

  /**
   * Default Mime Type to use when mime type detection fails.
   *
   * @var string
   */
  protected const DEFAULT_MIME_TYPE = 'text/plain';
  /**
   * The reference information by property.
   *
   * @var \Drupal\metastore\Reference\ReferenceMap
   */
  private $referenceMap;

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
  public function reference(object $metadata, string $schemaId = 'dataset') {
    $refs = $this->referenceMap->getAllReferences($schemaId);
    // Cycle through the dataset properties we seek to reference.
    foreach ($refs as $propertyName => $reference) {
      if (!isset($metadata->{$propertyName})) {
        continue;
      }

      $value = $metadata->{$propertyName};
      $reference->setContext($metadata);
      if (is_array($value)) {
        $metadata->{$propertyName} = $this->referenceArray($reference, $value);
      }
      else {
        $metadata->{$propertyName} = $reference->reference($value);
      }
    }
    return $metadata;
  }

  /**
   * References a dataset property's value, array case.
   *
   * @param \Drupal\metastore\Reference\ReferenceTypeInterface $reference
   *   The reference information.
   * @param array $values
   *   The array of values to be referenced.
   *
   * @return array
   *   The array of uuid references.
   */
  private function referenceArray(ReferenceTypeInterface $reference, array $values) : array {
    $result = [];
    foreach ($values as $value) {
      $result[] = $reference->reference($value);
    }
    return array_filter($result);
  }

}
