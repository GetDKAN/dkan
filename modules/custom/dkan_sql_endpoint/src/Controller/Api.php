<?php

namespace Drupal\dkan_sql_endpoint\Controller;

use Drupal\dkan_common\DataModifierPluginTrait;
use Drupal\dkan_common\Plugin\DataModifierManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\dkan_common\JsonResponseTrait;
use Drupal\dkan_datastore\Service\Factory\Resource;
use Drupal\dkan_datastore\Storage\DatabaseTable;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;
use Drupal\dkan_sql_endpoint\Service;

/**
 * Api class.
 */
class Api implements ContainerInjectionInterface {
  use JsonResponseTrait;
  use DataModifierPluginTrait;

  private $service;
  private $database;
  private $requestStack;
  private $resourceServiceFactory;
  private $databaseTableFactory;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan_sql_endpoint.service'),
      $container->get('database'),
      $container->get('dkan_datastore.service.factory.resource'),
      $container->get('request_stack'),
      $container->get('dkan_datastore.database_table_factory'),
      $container->get('plugin.manager.dkan_common.data_modifier')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(
    Service $service,
    Connection $database,
    Resource $resourceServiceFactory,
    RequestStack $requestStack,
    DatabaseTableFactory $databaseTableFactory,
    DataModifierManager $pluginManager
  ) {
    $this->service = $service;
    $this->database = $database;
    $this->resourceServiceFactory = $resourceServiceFactory;
    $this->requestStack = $requestStack;
    $this->databaseTableFactory = $databaseTableFactory;
    $this->pluginManager = $pluginManager;

    $this->plugins = $this->discover();
  }

  /**
   * Method called by the router.
   */
  public function runQueryGet() {

    $query = NULL;
    $query = $this->requestStack->getCurrentRequest()->get('query');

    if (empty($query)) {
      return $this->getResponse("Missing 'query' query parameter", 400);
    }

    return $this->runQuery($query);
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
      return $this->getResponse("Missing 'query' property in the request's body.", 400);
    }

    return $this->runQuery($query);
  }

  /**
   * Private.
   */
  private function runQuery(string $query) {
    try {
      $queryObject = $this->service->getQueryObject($query);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e);
    }

    $uuid = $this->service->getTableName($query);
    if ($modifyResponse = $this->modifyData($uuid)) {
      return $modifyResponse;
    }
    $databaseTable = $this->getDatabaseTable($uuid);

    try {
      $result = $databaseTable->query($queryObject);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e);
    }

    return $this->getResponse($result, 200);
  }

  /**
   * Provides data modifiers plugins an opportunity to act.
   *
   * @param string $identifier
   *   The distribution's identifier.
   *
   * @return object|bool
   *   The json response if sql endpoint docs needs modifying, FALSE otherwise.
   */
  private function modifyData(string $identifier) {
    foreach ($this->plugins as $plugin) {
      if ($plugin->requiresModification('distribution', $identifier)) {
        return $this->getResponse((object) ["message" => $plugin->message()], $plugin->httpCode());
      }
    }
    return FALSE;
  }

  /**
   * Private.
   */
  private function getDatabaseTable(string $uuid): DatabaseTable {
    $resource = $this->getResource($uuid);
    return $this->databaseTableFactory->getInstance($resource->getId(), ['resource' => $resource]);
  }

  /**
   * Private.
   */
  private function getResource(string $uuid) {
    /* @var $resourceService \Drupal\dkan_datastore\Service\Resource */
    $resourceService = $this->resourceServiceFactory->getInstance($uuid);
    return $resourceService->get();
  }

}
