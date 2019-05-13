<?php

namespace Drupal\dkan_common\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sae\Sae;
use Contracts\Storage as ContractsStorageInterface;

/**
 * Factory to generate DKAN API responses.
 *
 * Coverage is ignored for this class since it's mostly a proxy to
 * initialize new instances.
 *
 * @codeCoverageIgnore
 */
class Factory {

  /**
   * Creates a new json response.
   *
   * @param mixed $data
   *   The response data.
   * @param int $status
   *   The response status code.
   * @param array $headers
   *   An array of response headers.
   * @param bool $json
   *   If the data is already a JSON string.
   */
  public function newJsonResponse($data = NULL, $status = 200, array $headers = [], $json = FALSE) {
    return new JsonResponse($data, $status, $headers, $json);
  }

  /**
   * Creates new HTTP Response.
   *
   * @param mixed $content
   *   The response content, see setContent()
   * @param int $status
   *   The response status code.
   * @param array $headers
   *   An array of response headers.
   *
   * @return static
   */
  public function newHttpResponse($content = '', $status = 200, array $headers = []) {
    return Response::create($content, $status, $headers);
  }

  /**
   * Creates new ServiceApiEngine.
   *
   * @param \Contracts\Storage $storage
   *   Storage.
   * @param string $jsonSchema
   *   Json Schema.
   *
   * @return \Sae\Sae
   *   New Service Api Engine.
   */
  public function newServiceApiEngine(ContractsStorageInterface $storage, string $jsonSchema) {
    return new Sae($storage, $jsonSchema);
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container) {
    return new static($container);
  }

}
