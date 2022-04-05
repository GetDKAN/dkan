<?php

namespace Drupal\metastore\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;

class ReferenceMap implements ReferenceMapInterface {

  /**
   * Reference type manager service.
   *
   * @var \Drupal\metastore\Reference\ReferenceTypeManager
   */
  private $referenceTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configService
   *   The Drupal config service.
   */
  public function __construct(ReferenceTypeManager $referenceTypeManager, ConfigFactoryInterface $configService) {
    $this->referenceTypeManager = $referenceTypeManager;
    $this->configService = $configService;
    $this->map = $this->buildReferenceMap();
  }

  /**
   * {@inheritdoc}
   */
  public function getAllReferences(string $schemaId): array {
    return $this->map[$schemaId] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getReference(string $schemaId, string $propertyName): ?ReferenceTypeInterface {
    $refs = $this->getAllReferences($schemaId);
    return $refs[$propertyName] ?? NULL;
  }

  /**
   * Temporary solution as we move toward YAML-based schema definitions.
   *
   * @return array[]
   *   An array of arrays of references keyed by schema then property name.
   */
  protected function buildReferenceMap() {
    return [
      "catalog" => [
        'dataset' => $this->createReference('item', 'dataset', 'dataset'),
      ],
      "distribution" => [
        'downloadURL' => $this->createReference('resource', 'downloadURL'),
        'describedBy' => $this->createReference('url', 'describedBy', 'data-dictionary'),
      ],
      'dataset' => $this->getDatasetReferences(),
    ];
  }

  /**
   * Get the list of dataset properties being referenced.
   *
   * @return array
   *   List of dataset properties.
   *
   * @todo consolidate with common RouteProvider's getPropertyList.
   */
  protected function getDatasetReferences() : array {
    $list = $this->configService->get('metastore.settings')->get('property_list');
    foreach (array_values(array_filter($list)) as $propertyName) {
      $refs[$propertyName] = $this->createReference('item', $propertyName, $propertyName);
    }
    return $refs;
  }

  protected function createReference($type, $propertyName, $schemaId = NULL) {
    $config = [
      'property' => $propertyName,
      'schemaId' => $schemaId,
    ];

    return $this->referenceTypeManager->createInstance($type, $config);
  }

}
