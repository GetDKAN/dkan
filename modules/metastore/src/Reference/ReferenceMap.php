<?php

namespace Drupal\metastore\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;

class ReferenceMap {

  public function __construct(ConfigFactoryInterface $configService) {
    $this->configService = $configService;
    $this->map = $this->buildReferenceMap();
  }

  public function getAllReferences(string $schemaId): array {
    return $map[$schemaId] ?? [];
  }

  public function getReference($schemaId, $propertyName): ?ReferenceDefinition {
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
