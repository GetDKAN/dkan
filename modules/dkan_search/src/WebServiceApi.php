<?php

namespace Drupal\dkan_search;

use Drupal\dkan_common\JsonResponseTrait;

/**
 * Controller.
 */
class WebServiceApi {
  use JsonResponseTrait;

  /**
   * Search.
   */
  public function search() {
    $params = $this->getParams();

    /** @var \Drupal\dkan_search\Service $service */
    $service = \Drupal::service("dkan_search.service");

    $responseBody = $service->search($params);

    return $this->getResponse($responseBody);
  }

  /**
   * Private.
   */
  private function getParams() {
    $defaults = [
      "page-size" => 10,
      "page" => 1,
    ];

    /** @var \Symfony\Component\HttpFoundation\RequestStack $requestStack */
    $requestStack = \Drupal::service('request_stack');
    $request = $requestStack->getCurrentRequest();
    $params = $request->query->all();

    foreach ($defaults as $param => $default) {
      $params[$param] = isset($params[$param]) ? $params[$param] : $default;
    }

    if ($params["page-size"] > 100) {
      $params["page-size"] = 100;
    }

    return $params;
  }

}
