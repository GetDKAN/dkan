<?php

namespace Drupal\metastore\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Refernce map service.
 *
 * Maps reference types to schema properties.
 */
class ReferenceMap implements ReferenceMapInterface {

  /**
   * Reference type manager service.
   *
   * @var \Drupal\metastore\Reference\ReferenceTypeManager
   */
  private $referenceTypeManager;

  /**
   * The config service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private ConfigFactoryInterface $configService;

  /**
   * An array of arrays of references keyed by schema then property name.
   *
   * @var array
   */
  private array $map;

  /**
   * Constructor.
   *
   * @param \Drupal\metastore\Reference\ReferenceTypeManager $referenceTypeManager
   *   Reference type manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configService
   *   Drupal config service.
   *
   * @return void
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

  /**
   * Build a reference object.
   *
   * @param string $type
   *   Refernce type - ID of a plugin that implements ReferenceTypeInterface.
   * @param string $propertyName
   *   The property of the schema to build the reference for.
   * @param string $schemaId
   *   ID of the schema to pull the property from.
   *
   * @return \Drupal\metastore\Reference\ReferenceTypeInterface
   *   An instantiated ReferenceType object.
   */
  protected function createReference(string $type, string $propertyName, string $schemaId): ReferenceTypeInterface {
    $config = [
      'property' => $propertyName,
      'schemaId' => $schemaId,
    ];

    return $this->referenceTypeManager->createInstance($type, $config);
  }

}
