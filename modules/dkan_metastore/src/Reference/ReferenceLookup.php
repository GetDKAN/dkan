<?php

namespace Drupal\dkan_metastore\Reference;

use Contracts\FactoryInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\dkan_metastore\Factory\MetastoreItemFactoryInterface;
use Drupal\dkan_metastore\ReferenceLookupInterface;
use RootedData\RootedJsonData;

/**
 * Service to find metastore items referencing an identifier.
 */
class ReferenceLookup implements ReferenceLookupInterface {
  use HelperTrait;

  /**
   * DKAN metastore module name.
   */
  private const MODULE_NAME = 'dkan_metastore';

  /**
   * Module Handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  public function __construct(
    protected FactoryInterface $metastoreStorage,
    protected MetastoreItemFactoryInterface $metastoreItemFactory,
    protected CacheTagsInvalidatorInterface $invalidator,
    protected ModuleHandlerInterface $moduleHandler,
  ) {
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
      $propertyValue = NULL;
      if (is_array($metadata)) {
        $propertyValue = $metadata[$propertyId] ?? NULL;
      }
      elseif (is_object($metadata)) {
        $propertyValue = $metadata->{$propertyId} ?? NULL;
      }
      $referencers[] = self::valueContainsStartsWith($referenceId, $propertyValue) ? $identifier : NULL;
    }

    return array_filter($referencers);
  }

  /**
   * Check recursively whether a value contains a string with the ID prefix.
   *
   * @param string $needle
   *   The ID or ID fragment.
   * @param mixed $value
   *   Any metadata value to inspect.
   *
   * @return bool
   *   TRUE when a nested string starts with the supplied fragment.
   */
  private static function valueContainsStartsWith(string $needle, $value): bool {
    if (is_string($value)) {
      return str_starts_with($value, $needle);
    }

    if (is_array($value)) {
      foreach ($value as $item) {
        if (self::valueContainsStartsWith($needle, $item)) {
          return TRUE;
        }
      }
      return FALSE;
    }

    if (is_object($value)) {
      foreach (get_object_vars($value) as $item) {
        if (self::valueContainsStartsWith($needle, $item)) {
          return TRUE;
        }
      }
      return FALSE;
    }

    return FALSE;
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
    $module_path = $this->moduleHandler->getModule(self::MODULE_NAME)->getPath();
    $legacy_schema_path = $module_path . '/docs/legacy_metadata.json';
    // Fetch the legacy metadata schema.
    // @todo This file load happens for every metadata item that is processed.
    //   The schema JSON is then garbage-collected when we leave the scope of
    //   this function. Find a way to keep and reuse the schema data.
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
