<?php

namespace Drupal\sql_endpoint;

use Drupal\common\DataModifierPluginTrait;
use Drupal\common\Plugin\DataModifierManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\JsonResponseTrait;

/**
 * Api class.
 */
class WebServiceApi implements ContainerInjectionInterface {
  use JsonResponseTrait;
  use DataModifierPluginTrait;

  private $service;
  private $database;
  private $requestStack;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('sql_endpoint.service'),
      $container->get('database'),
      $container->get('request_stack'),
      $container->get('plugin.manager.dkan.data_modifier')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(
    Service $service,
    Connection $database,
    RequestStack $requestStack,
    DataModifierManager $pluginManager
  ) {
    $this->service = $service;
    $this->database = $database;

    $this->requestStack = $requestStack;
    $this->pluginManager = $pluginManager;

    $this->plugins = $this->discover();
  }

  /**
   * Method called by the router.
   */
  public function runQueryGet() {

    $query = NULL;
    $query = $this->requestStack->getCurrentRequest()->get('query');

    $flag = $this->requestStack->getCurrentRequest()->get('show-db-columns');
    $showDbColumns = isset($flag) ? TRUE : FALSE;

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
    $uuid = $this->service->getResourceUuid($query);

    if ($modifyResponse = $this->modifyData($uuid)) {
      return $modifyResponse;
    }

    try {
      $result = $this->service->runQuery($query, $showDbColumns);
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

}
