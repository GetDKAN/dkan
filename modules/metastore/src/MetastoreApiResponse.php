<?php

namespace Drupal\metastore\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactory;
use Drupal\metastore\Factory\MetastoreItemFactoryInterface;
use Drupal\metastore\MetastoreItemInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Service to standardize building response objects for API requests.
 */
class MetastoreApiResponse {

  /**
   * Cache page max age config value.
   *
   * @var int
   */
  protected $cacheMaxAge;

  public function __construct(
    ConfigFactory $configFactory, 
    MetastoreItemFactoryInterface $metastoreItemFactory
  ) {
    $this->configFactory = $configFactory;
    $this->cacheMaxAge = $configFactory->get('system.performance')->get('cache.page.max_age') ?: 0;
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

    $response = CacheableJsonResponse::create($data, $code, []);
    if ($cacheMetadata = $this->getCacheMetadata($dependencies, $params)) {
      $response->addCacheableDependency($cacheMetadata);
    }

    return $response;
  }

  private function getCacheMetadata(array $dependencies, ?ParameterBag $params) {
    $cacheMetadata = new CacheableMetadata();

    foreach ($dependencies as $key => $item) {
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

    if ($params) {
      $this->addContexts($cacheMetadata, $params);
    }

    $cacheMetadata->setCacheMaxAge($this->cacheMaxAge);
    return $cacheMetadata;
  }

  private function addItemDependencies(CacheableMetadata $cacheMetadata, $ids) {
    foreach ($ids as $identifier) {
      $item = $this->metastoreItemFactory->getInstance($identifier);
      $cacheMetadata->addCacheableDependency($item);
      $this->addReferenceDependencies($cacheMetadata, $item);
    }
  }

  private function addReferenceDependencies(CacheableMetadata $cacheMetadata, MetastoreItemInterface $item) {
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

  private function addSchemaDependency(CacheableMetadata $cacheMetadata, $schema) {
    $cacheTags = $this->metastoreItemFactory::getCacheTags();
    $cacheMetadata->addCacheTags($cacheTags);
  }

  private function addContexts(CacheableMetadata $cacheMetadata, ParameterBag $params) {
    foreach ($params->keys() as $key) {
      $cacheMetadata->addCacheContexts(["url.query_args:$key"]);
    }
  }

}
