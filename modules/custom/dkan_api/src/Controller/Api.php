<?php

namespace Drupal\dkan_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sae\Sae;

abstract class Api extends ControllerBase {

  abstract protected function getJsonSchema();

  abstract protected function getStorage();

  public function get($uuid) {

    $engine = $this->getEngine();

    try {
      $data = $engine->get($uuid);
      return new JsonResponse(json_decode($data), 200, ["Access-Control-Allow-Origin" => "*"]);
    }
    catch (\Exception $e) {
      return new JsonResponse((object) ["message" => $e->getMessage()], 404);
    }
  }

  public function postAndGetAll() {
    /* @var $request \Symfony\Component\HttpFoundation\Request */
    $request = \Drupal::request();

    $method = $request->getMethod();

    $engine = $this->getEngine();

    if ($method == "GET") {
      return new JsonResponse(json_decode($engine->get()), 200, ["Access-Control-Allow-Origin" => "*"]);
    }
    elseif ($method == "POST") {

      $data = $request->getContent();

      try {
        $id = $engine->post($data);
        $uri = $request->getRequestUri();
        return new JsonResponse((object)["identifier" => "{$uri}/{$id}"]);
      } catch (\Exception $e) {
        return new JsonResponse((object)["message" => $e->getMessage()], 406);
      }
    }
  }

  public function getEngine() {
    $storage = $this->getStorage();
    return new Sae($storage, $this->getJsonSchema());
  }
}

