<?php

namespace Drupal\datastore\SqlEndpoint;

use Drupal\common\EventDispatcherTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\JsonResponseTrait;
use Drupal\metastore\MetastoreApiResponse;

/**
 * Api class.
 */
class WebServiceApi implements ContainerInjectionInterface {
  use JsonResponseTrait;
  use EventDispatcherTrait;

  const EVENT_RUN_QUERY = 'dkan_datastore_sql_run_query';

  /**
   * DKAN SQL Endpoint service.
   *
   * @var \Drupal\datastore\SqlEndpoint
   */
  private $service;

  /**
   * The Drupal database connection to use.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * API request data.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.datastore.sql_endpoint.service'),
      $container->get('database'),
      $container->get('request_stack'),
      $container->get('dkan.metastore.api_response')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(
    DatastoreSqlEndpointService $service,
    Connection $database,
    RequestStack $requestStack,
    MetastoreApiResponse $metastoreApiResponse
  ) {
    $this->service = $service;
    $this->database = $database;
    $this->requestStack = $requestStack;
    $this->metastoreApiResponse = $metastoreApiResponse;
  }

  /**
   * Method called by the router.
   */
  public function runQueryGet() {

    $query = NULL;
    $query = $this->requestStack->getCurrentRequest()->get('query');

    // @todo Deprecate parameter show-db-columns in favor of show_db_columns.
    //   Dredd enforces RFC6570 which does not allow hyphens in parameter names.
    $flag = $this->requestStack->getCurrentRequest()->get('show-db-columns');
    $preferredFlag = $this->requestStack->getCurrentRequest()->get('show_db_columns');
    $showDbColumns = isset($preferredFlag) || isset($flag);

    if (empty($query)) {
      return $this->getResponseFromException(
        new \Exception("Missing 'query' query parameter"),
        400
      );
    }

    // The incoming string could contain escaped characters.
    $query = stripslashes($query);

    return $this->runQuery($query, $showDbColumns);
  }

  /**
   * Method called by the router.
   */
  public function runQueryPost() {

    $query = NULL;
    $payloadJson = $this->requestStack->getCurrentRequest()->getContent();
    $payload = json_decode($payloadJson);
    if (isset($payload->query)) {
      $query = $payload->query;
    }

    if (empty($query)) {
      return $this->getResponseFromException(
        new \Exception("Missing 'query' property in the request's body."),
        400
      );
    }

    $showDbColumns = $payload->show_db_columns ?? FALSE;

    return $this->runQuery($query, $showDbColumns);
  }

  /**
   * Private.
   */
  private function runQuery(string $query, $showDbColumns = FALSE) {
    try {
      $uuid = $this->service->getResourceUuid($query);

      $this->dispatchEvent(self::EVENT_RUN_QUERY, $uuid);

      $result = $this->service->runQuery($query, $showDbColumns);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e);
    }

    $request = $this->requestStack->getCurrentRequest();

    return $this->metastoreApiResponse->cachedJsonResponse(
      $result,
      200,
      ['distribution' => [$uuid]],
      $request->query
    );
  }

}
