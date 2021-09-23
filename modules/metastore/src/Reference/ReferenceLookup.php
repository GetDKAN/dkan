<?php

namespace Drupal\metastore\Reference;

use Drupal\common\LoggerTrait;
use Drupal\metastore\Factory\MetastoreItemFactoryInterface;
use Drupal\metastore\ReferenceLookupInterface;

use Contracts\FactoryInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use RootedData\RootedJsonData;

/**
 * {@inheritdoc}
 */
class ReferenceLookup implements ReferenceLookupInterface {
  use HelperTrait;
  use LoggerTrait;

  /**
   * Metastore Storage service.
   *
   * @var \Contracts\FactoryInterface
   */
  protected $metastoreStorage;

  /**
   * Metastore Item Factory service.
   *
   * @var \Drupal\metastore\Factory\MetastoreItemFactoryInterface
   */
  protected $metastoreItemFactory;

  /**
   * Module Handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  public function __construct(
    FactoryInterface $metastoreStorage,
    MetastoreItemFactoryInterface $metastoreItemFactory,
    CacheTagsInvalidatorInterface $invalidator,
    ModuleHandlerInterface $moduleHandler
  ) {
    $this->metastoreStorage = $metastoreStorage;
    $this->metastoreItemFactory = $metastoreItemFactory;
    $this->invalidator = $invalidator;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   *
   * @todo Refactor when this storage vs item factory mess is resolved.
   */
  public function getReferencers(string $schemaId, string $referenceId, string $propertyId) {
    // This will give us a smaller subset of metastore items to parse through.
    $metastoreItems = $this->metastoreStorage->getInstance($schemaId)->retrieveContains($referenceId);

    $referencers = [];
    foreach ($metastoreItems as $item) {
      [$identifier, $metadata] = $this->decodeJsonMetadata($item);
      $propertyValue = $metadata->{$propertyId};
      // Check if uuid is found either directly or in an array.
      $idIsValue = $referenceId == $propertyValue;
      $idInArray = is_array($propertyValue) && in_array($referenceId, $propertyValue);
      if ($idIsValue || $idInArray) {
        $referencers[] = $identifier;
      }
    }

    return $referencers;
  }

  /**
   * Invalidate cache tags in any items pointing to a reference.
   *
   * @param string $schemaId
   *   The type of metadata to look for references within.
   * @param string $referenceId
   *   The UUID of the reference we're looking for.
   * @param string $propertyId
   *   The metadata property we hope to find it in.
   */
  public function invalidateReferencerCacheTags(string $schemaId, string $referenceId, string $propertyId) {
    $referencers = $this->getReferencers($schemaId, $referenceId, $propertyId);
    $tags = [];
    foreach ($referencers as $identifier) {
      $item = $this->metastoreItemFactory->getInstance($identifier);
      $tags = Cache::mergeTags($tags, $item->getCacheTags());
    }
    $this->invalidator->invalidateTags($tags);
  }

  /**
   * Decode the supplied JSON metadata.
   *
   * @param string $json
   *   JSON metadata string.
   *
   * @return array
   *   JSON metadata identifier and object.
   */
  protected function decodeJsonMetadata(string $json): array {
    // Decode the supplied JSON metadata string.
    $metadata = json_decode($json);
    // Determine the path to the legacy metadata schema file.
    $module_path = $this->moduleHandler->getModule(get_module_name())->getPath();
    $legacy_schema_path = $module_path . '/docs/legacy_metadata.json';
    // Fetch the legacy metadata schema.
    $legacy_schema = file_get_contents($legacy_schema_path);
    // Record metadata identifier.
    $identifier = $metadata->identifier;
    // Get raw metadata using identifier.
    $metadata = $this->metastoreItemFactory->getInstance($identifier)->getRawMetadata();
    // Validate JSON against legacy schema.
    $validation_result = RootedJsonData::validate(json_encode($metadata), $legacy_schema);
    // If the JSON metadata matches the legacy schema, extract the content of
    // the "data" property.
    if ($validation_result->isValid()) {
      $metadata = $metadata->data;
    }

    return [$identifier, $metadata];
  }

}
