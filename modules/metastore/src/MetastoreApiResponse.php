<?php

namespace Drupal\metastore;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\metastore\Factory\MetastoreItemFactoryInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Service to standardize building response objects for API requests.
 */
class MetastoreApiResponse {

  /**
   * Constructor.
   *
   * @param \Drupal\metastore\Factory\MetastoreItemFactoryInterface $metastoreItemFactory
   *   Metastore Item factory service.
   */
  public function __construct(MetastoreItemFactoryInterface $metastoreItemFactory) {
    $this->metastoreItemFactory = $metastoreItemFactory;
  }

  /**
   * Create a basic, cacheable JSON response.
   *
   * @param mixed $data
   *   Array or object that can be encoded as JSON.
   * @param int $code
   *   An HTTP response code.
   * @param array $dependencies
   *   Cacheable dependencies for the response that will be used for tagging.
   *   Should be an array of arrays of metastore IDs, keyed by schema type. For
   *   instance, ['dataset' => ['5160a9f1-ee5d-4e94-ab53-183104e2ef4b']]. If you
   *   want to add a general schema tag -- for instance, for a route that lists
   *   all items of a particular schema, simply add that schema to the array as
   *   a string. For instance, ['dataset'].
   * @param \Symfony\Component\HttpFoundation\ParameterBag|null $params
   *   The parameter object from the request.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   A response, ready to be returned to a route.
   */
  public function cachedJsonResponse(
    $data,
    int $code = 200,
    array $dependencies = [],
    ?ParameterBag $params = NULL
  ): CacheableJsonResponse {

    $response = new CacheableJsonResponse($data, $code, []);
    if ($cacheMetadata = $this->getCacheMetadata($dependencies, $params)) {
      $response->addCacheableDependency($cacheMetadata);
    }

    return $response;
  }

  /**
   * Create cache metadata for response.
   *
   * @param array $dependencies
   *   Array of dependencies. See cachedJsonResponse().
   * @param null|\Symfony\Component\HttpFoundation\ParameterBag $params
   *   Parameters from request.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   Complete cache metadata object.
   *
   * @throws \InvalidArgumentException
   */
  private function getCacheMetadata(array $dependencies, ?ParameterBag $params) {
    $cacheMetadata = new CacheableMetadata();

    foreach ($dependencies as $key => $item) {
      $this->addDependency($cacheMetadata, $key, $item);
    }

    $this->addContexts($cacheMetadata, $params ?? new ParameterBag([]));

    return $cacheMetadata;
  }

  /**
   * Add a dependency from a dependency array.
   *
   * @param \Drupal\Core\Cache\CacheableMetadata $cacheMetadata
   *   Cache metadata object.
   * @param mixed $key
   *   Key from dependency array.
   * @param mixed $item
   *   Item from dependency array.
   */
  private function addDependency(CacheableMetadata $cacheMetadata, $key, $item) {
    if (is_string($key) && is_array($item)) {
      $this->addItemDependencies($cacheMetadata, $item);
    }
    elseif (is_string($item)) {
      $this->addSchemaDependency($cacheMetadata, $item);
    }
    else {
      throw new \InvalidArgumentException("Invalid cacheable dependency. " . print_r([$key => $item], TRUE));
    }
  }

  /**
   * Add metastore item dependencies to cache metadata.
   *
   * @param \Drupal\Core\Cache\CacheableMetadata $cacheMetadata
   *   Cache metadata object.
   * @param array $ids
   *   Array of metastore identifiers.
   */
  private function addItemDependencies(CacheableMetadata $cacheMetadata, array $ids) {
    foreach ($ids as $identifier) {
      $item = $this->getMetastoreItemFactory()->getInstance($identifier);
      $cacheMetadata->addCacheableDependency($item);
      $this->addReferenceDependencies($cacheMetadata, $item);
    }
  }

  /**
   * Add more dependencies for a metastore item's references.
   *
   * @param \Drupal\Core\Cache\CacheableMetadata $cacheMetadata
   *   Cache metadata object.
   * @param \Drupal\metastore\MetastoreItemInterface $item
   *   Metastore item, such as a dataset.
   */
  protected function addReferenceDependencies(CacheableMetadata $cacheMetadata, MetastoreItemInterface $item) {
    $metadata = $item->getMetaData();
    $ids = [];
    foreach ($metadata as $propertyId => $value) {
      if (substr($propertyId, 0, 4) == '%Ref') {
        $this->addReferenceIdentifier($ids, $value);
      }
    }
    $ids = array_filter($ids);
    $this->addItemDependencies($cacheMetadata, $ids);
  }

  /**
   * Get UUID from reference property. Normalizes for string or array values.
   *
   * @param array $ids
   *   Array of IDs to add as dependencies.
   * @param mixed $value
   *   The value for the reference field.
   */
  private function addReferenceIdentifier(array &$ids, $value) {
    if (is_array($value)) {
      foreach ($value as $ref) {
        $ids[] = $ref->identifier ?? NULL;
      }
    }
    else {
      $ids[] = $value->identifier ?? NULL;
    }
  }

  /**
   * Add a metastore dependency for a whole schema/type.
   *
   * This is necessary so that the page cache distinguishes between
   * requests with different GET queries.
   *
   * @param \Drupal\Core\Cache\CacheableMetadata $cacheMetadata
   *   Cache metadata object to modify.
   * @param mixed $schema
   *   The schemaID (e.g., "dataset").
   */
  private function addSchemaDependency(CacheableMetadata $cacheMetadata, $schema) {
    // Silly line to make linters happy. Right now the itemFactory doesn't
    // require a schema so it's not used.
    $schema = $schema;
    $cacheTags = $this->getMetastoreItemFactory()->getCacheTags();
    $cacheMetadata->addCacheTags($cacheTags);
  }

  /**
   * Return the metastore item factory service.
   *
   * @return \Drupal\metastore\Factory\MetastoreItemFactoryInterface
   *   Metastore item factory.
   */
  protected function getMetastoreItemFactory() {
    return $this->metastoreItemFactory;
  }

  /**
   * Add cache contexts based on request parameters.
   *
   * @param \Drupal\Core\Cache\CacheableMetadata $cacheMetadata
   *   Cache metadata object to modify.
   * @param \Symfony\Component\HttpFoundation\ParameterBag $params
   *   Request parameters.
   */
  private function addContexts(CacheableMetadata $cacheMetadata, ParameterBag $params) {
    $cacheMetadata->addCacheContexts(["url"]);
    foreach ($params->keys() as $key) {
      $cacheMetadata->addCacheContexts(["url.query_args:$key"]);
    }
  }

}
