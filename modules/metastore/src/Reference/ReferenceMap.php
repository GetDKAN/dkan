<?php

namespace Drupal\metastore\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;

class ReferenceMap implements ReferenceMapInterface {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configService
   *   The Drupal config service.
   */
  public function __construct(ConfigFactoryInterface $configService) {
    $this->configService = $configService;
    $this->map = $this->buildReferenceMap();
  }

  /**
   * {@inheritdoc}
   */
  public function getAllReferences(string $schemaId): array {
    return $map[$schemaId] ?? [];
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
        ['dataset'] => new ReferenceDefinition('dataset', 'schema', 'dataset')
      ],
      "distribution" => [
        ['downloadURL' => new ReferenceDefinition('downloadURL', 'resource')],
        ['describedBy' => new ReferenceDefinition('describedBy', 'schema', 'data-dictionary')],
      ],
      ['dataset' => $this->getDatasetReferences()],
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
      $refs[] = [$propertyName => new ReferenceDefinition($propertyName, 'schema', $propertyName)];
    }
    return $refs;
  }

}
