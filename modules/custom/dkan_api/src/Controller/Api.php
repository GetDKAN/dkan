<?php

namespace Drupal\dkan_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sae\Sae;

abstract class Api extends ControllerBase {

  abstract protected function getJsonSchema();

  abstract protected function getStorage();

  public function getAll() {
    /* @var $engine \Sae\Sae */
    $engine = $this->getEngine();

    $datasets = $engine->get();
    $json_string = "[" . implode(",", $datasets) . "]";

    return new JsonResponse(json_decode($json_string), 200, ["Access-Control-Allow-Origin" => "*"]);
  }

  public function get($uuid) {
    /* @var $engine \Sae\Sae */
    $engine = $this->getEngine();

    try {
      $data = $engine->get($uuid);
      return new JsonResponse(json_decode($data), 200, ["Access-Control-Allow-Origin" => "*"]);
    }
    catch (\Exception $e) {
      return new JsonResponse((object) ["message" => $e->getMessage()], 404);
    }
  }

  public function post() {
    /* @var $engine \Sae\Sae */
    $engine = $this->getEngine();

    /* @var $request \Symfony\Component\HttpFoundation\Request */
    $request = \Drupal::request();
    $uri = $request->getRequestUri();
    $data = $request->getContent();

    // If resource already exists, return HTTP 409 Conflict and existing uri.
    $params = json_decode($data, TRUE);
    if (isset($params['identifier'])) {
      $uuid = $params['identifier'];
      $existing = \Drupal::entityQuery('node')
        ->condition('uuid', $uuid)
        ->execute();
      if ($existing) {
        return new JsonResponse(
          (object) ["endpoint" => "{$uri}/{$uuid}"], 409);
      }
    }

    try {
      $uuid = $engine->post($data);
      return new JsonResponse(
        (object) ["endpoint" => "{$uri}/{$uuid}", "identifier" => $uuid],
        201
      );
    }
    catch (\Exception $e) {
      return new JsonResponse((object) ["message" => $e->getMessage()], 406);
    }
  }

  public function put($uuid) {
    /* @var $engine \Sae\Sae */
    $engine = $this->getEngine();

    /* @var $request \Symfony\Component\HttpFoundation\Request */
    $request = \Drupal::request();
    $data = $request->getContent();

    $existing = \Drupal::entityQuery('node')
      ->condition('uuid', $uuid)
      ->execute();

    try {
      $engine->put($uuid, $data);
      $uri = $request->getRequestUri();
      return new JsonResponse(
        (object) ["endpoint" => "{$uri}", "identifier" => $uuid],
        // If a new resource is created, inform the user agent via 201 Created.
        empty($existing) ? 201 : 200
      );
    }
    catch (\Exception $e) {
      return new JsonResponse((object) ["message" => $e->getMessage()], 406);
    }
  }

  public function delete($uuid) {
    /* @var $engine \Sae\Sae */
    $engine = $this->getEngine();

    $engine->delete($uuid);
    return new JsonResponse((object) ["message" => "Dataset {$uuid} has been deleted."], 200);
  }

  public function getEngine() {
    $storage = $this->getStorage();
    return new Sae($storage, $this->getJsonSchema());
  }

}
