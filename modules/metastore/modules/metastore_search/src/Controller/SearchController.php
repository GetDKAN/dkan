<?php

namespace Drupal\metastore_search\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\JsonResponseTrait;
use Drupal\metastore\MetastoreApiResponse;
use Drupal\metastore\SchemaRetriever;
use Drupal\metastore_search\Search;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller.
 */
class SearchController implements ContainerInjectionInterface {
  use JsonResponseTrait;

  /**
   * Dkan search service.
   *
   * @var \Drupal\metastore_search\Search
   */
  private $service;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * SearchController constructor.
   *
   * @param \Drupal\metastore_search\Search $service
   *   Dkan search service.
   * @param \Drupal\metastore\MetastoreApiResponse $metastoreApiResponse
   *   Metastore API cached response service.
   * @param \Drupal\metastore\SchemaRetriever $schemaRetriever
   *   Schema retriever service.
   */
  public function __construct(
    Search $service,
    MetastoreApiResponse $metastoreApiResponse,
    SchemaRetriever $schemaRetriever
  ) {
    $this->service = $service;
    $this->metastoreApiResponse = $metastoreApiResponse;
    $this->schemaRetriever = $schemaRetriever;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore_search.service'),
      $container->get('dkan.metastore.api_response'),
      $container->get('dkan.metastore.schema_retriever')
    );
  }

  /**
   * Search.
   */
  public function search(Request $request): JsonResponse {
    try {
      $params = $this->getParams($request);
      $responseBody = $this->service->search($params);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e);
    }
    if ($params['facets'] == TRUE) {
      $responseBody->facets = $this->service->facets($params);
    }
    return $this->metastoreApiResponse->cachedJsonResponse(
      $responseBody,
      200,
      $this->getCacheDependencies(),
      $request->query
    );
  }

  /**
   * Get the cache dependencies.
   *
   * @return array
   *   An array of dependencies for \Drupal\metastore\MetastoreApiResponse.
   */
  private function getCacheDependencies() {
    return $this->schemaRetriever->getAllIds();
  }

  /**
   * Facets.
   */
  public function facets(Request $request) {
    $responseBody = (object) [];
    try {
      $params = $this->getParams($request);
      $responseBody = $this->service->search($params);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e);
    }

    $start = microtime(TRUE);
    $facets = $this->service->facets($params);
    $responseBody->facets = $facets;
    $responseBody->time = microtime(TRUE) - $start;

    return $this->metastoreApiResponse->cachedJsonResponse(
      $responseBody,
      200,
      $this->getCacheDependencies(),
      $request->query
    );
  }

  /**
   * Get params from request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return array
   *   Array of parameters.
   *
   * @throws \InvalidArgumentException
   */
  private function getParams(Request $request) {
    $defaults = [
      "page-size" => 10,
      "page" => 1,
      'facets' => TRUE,
    ];

    $params = $request->query->all();

    foreach ($defaults as $param => $default) {
      $params[$param] = isset($params[$param]) ? $params[$param] : $default;
    }

    if (!is_numeric($params['page-size']) || !is_numeric($params['page'])) {
      throw new \InvalidArgumentException("Pagination arguments must be numeric.");
    }

    if ($params["page-size"] > 100) {
      $params["page-size"] = 100;
    }

    return $params;
  }

}
