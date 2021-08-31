<?php

namespace Drupal\metastore;

use Drupal\common\ApiResponse;
use Drupal\metastore\Exception\CannotChangeUuidException;
use Drupal\metastore\Exception\InvalidJsonException;
use Drupal\metastore\Exception\MetastoreException;
use Drupal\metastore\Exception\MissingPayloadException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Class Api.
 *
 * @todo Move docs stuff.
 */
class WebServiceApi implements ContainerInjectionInterface {

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Metastore service.
   *
   * @var \Drupal\metastore\Service
   */
  private $service;

  /**
   * Metastore dataset docs service.
   *
   * @var \Drupal\metastore\DatasetApiDocs
   */
  private $docs;

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new WebServiceApi(
      $container->get('request_stack'),
      $container->get('dkan.metastore.service'),
      $container->get('dkan.metastore.dataset_api_docs')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(RequestStack $requestStack, Service $service, DatasetApiDocs $docs) {
    $this->requestStack = $requestStack;
    $this->service = $service;
    $this->docs = $docs;
  }

  /**
   * Get schemas.
   */
  public function getSchemas() {
    return ApiResponse::jsonResponse($this->service->getSchemas());
  }

  /**
   * Get schema.
   */
  public function getSchema(string $identifier) {
    try {
      return ApiResponse::jsonResponse($this->service->getSchema($identifier));
    }
    catch (\Exception $e) {
      return ApiResponse::jsonResponseFromException($e, 404);
    }
  }

  /**
   * Get all.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function getAll(string $schema_id) {
    $keepRefs = $this->wantObjectWithReferences();

    $output = array_map(function ($object) use ($keepRefs) {
      $modified_object = $keepRefs
        ? $this->service->swapReferences($object)
        : $this->service->removeReferences($object);
      return (object) $modified_object->get('$');
    }, $this->service->getAll($schema_id));

    $output = array_values($output);
    return ApiResponse::jsonResponse($output);
  }

  /**
   * Implements GET method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function get(string $schema_id, string $identifier) {
    try {
      $object = $this->service->get($schema_id, $identifier);
      if ($this->wantObjectWithReferences()) {
        $object = $this->service->swapReferences($object);
      }
      else {
        $object = Service::removeReferences($object);
      }
      $object = (object) $object->get('$');
      return ApiResponse::jsonResponse($object);
    }
    catch (\Exception $e) {
      return ApiResponse::jsonResponseFromException($e, 404);
    }
  }

  /**
   * Private.
   */
  private function wantObjectWithReferences() {
    $param = $this->requestStack->getCurrentRequest()
      ->get('show-reference-ids', FALSE);
    $param2 = $this->requestStack->getCurrentRequest()
      ->get('show_reference_ids', FALSE);
    if ($param === FALSE && $param2 === FALSE) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * GET all resources associated with a dataset.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function getResources(string $schema_id, string $identifier) {
    try {
      return ApiResponse::jsonResponse($this->service->getResources($schema_id, $identifier));
    }
    catch (\Exception $e) {
      return ApiResponse::jsonResponseFromException($e, 404);
    }
  }

  /**
   * Implements POST method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function post(string $schema_id) {
    try {
      $data = $this->getRequestContent();
      $this->checkIdentifier($data);
      $data = $this->service->getValidMetadataFactory()->get($data, $schema_id, ['method' => 'POST']);
      $identifier = $this->service->post($schema_id, $data);
      return ApiResponse::jsonResponse([
        "endpoint" => "{$this->getRequestUri()}/{$identifier}",
        "identifier" => $identifier,
      ], 201);
    }
    catch (MetastoreException $e) {
      return ApiResponse::jsonResponseFromException($e, $e->httpCode());
    }
    catch (\Exception $e) {
      return ApiResponse::jsonResponseFromException($e, 400);
    }
  }

  /**
   * Publish the latest revision of a dataset.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function publish(string $schema_id, string $identifier) {
    try {
      $this->service->publish($schema_id, $identifier);
      return ApiResponse::jsonResponse((object) [
        "endpoint" => $this->getRequestUri(),
        "identifier" => $identifier,
      ]);
    }
    catch (MetastoreException $e) {
      return ApiResponse::jsonResponseFromException($e, $e->httpCode());
    }
    catch (\Exception $e) {
      return ApiResponse::jsonResponseFromException($e, 400);
    }
  }

  /**
   * Implements PUT method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function put($schema_id, string $identifier) {
    try {
      $data = $this->getRequestContent();
      $this->checkIdentifier($data, $identifier);
      $data = $this->service->getValidMetadataFactory()->get($data, $schema_id);
      $info = $this->service->put($schema_id, $identifier, $data);
      $code = ($info['new'] == TRUE) ? 201 : 200;
      return ApiResponse::jsonResponse(
        [
          "endpoint" => $this->getRequestUri(),
          "identifier" => $info['identifier'],
        ],
        $code
      );
    }
    catch (MetastoreException $e) {
      return ApiResponse::jsonResponseFromException($e, $e->httpCode());
    }
    catch (\Exception $e) {
      return ApiResponse::jsonResponseFromException($e, 400);
    }
  }

  /**
   * Implements PATCH method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function patch($schema_id, $identifier) {

    try {
      $data = $this->getRequestContent();

      if (empty($data)) {
        throw new MissingPayloadException("Empty body");
      }
      $obj = json_decode($data);
      if (!$obj) {
        throw new InvalidJsonException("Invalid JSON");
      }
      $this->checkIdentifier($data, $identifier);

      $this->service->patch($schema_id, $identifier, $data);
      return ApiResponse::jsonResponse((object) [
        "endpoint" => $this->getRequestUri(),
        "identifier" => $identifier,
      ]);
    }
    catch (MetastoreException $e) {
      return ApiResponse::jsonResponseFromException($e, $e->httpCode());
    }
    catch (\Exception $e) {
      return ApiResponse::jsonResponseFromException($e, 400);
    }
  }

  /**
   * Implements DELETE method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function delete($schema_id, $identifier) {
    try {
      $this->service->delete($schema_id, $identifier);
      return ApiResponse::jsonResponse((object) ["message" => "Dataset {$identifier} has been deleted."]);
    }
    catch (\Exception $e) {
      return ApiResponse::jsonResponseFromException($e);
    }
  }

  /**
   * Provides the data catalog.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A json response, either the catalog or an exception.
   */
  public function getCatalog() : JsonResponse {
    try {
      return ApiResponse::jsonResponse($this->service->getCatalog());
    }
    catch (\Exception $e) {
      return ApiResponse::jsonResponseFromException($e);
    }
  }

  /**
   * Get the API docs spec for a specific dataset.
   *
   * @param string $identifier
   *   Dataset identifier.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response.
   */
  public function getDocs($identifier) : JsonResponse {
    try {
      return ApiResponse::jsonResponse($this->docs->getDatasetSpecific($identifier));
    }
    catch (\Exception $e) {
      return ApiResponse::jsonResponseFromException($e);
    }
  }

  /**
   * Checks identifier.
   */
  private function checkIdentifier(string $data, $identifier = NULL) {
    $obj = json_decode($data);
    if (isset($identifier) && isset($obj->identifier) && $obj->identifier != $identifier) {
      throw new CannotChangeUuidException("Identifier cannot be modified");
    }
  }

  /**
   * Get the request's uri.
   *
   * @return string
   *   The uri.
   */
  private function getRequestUri(): string {
    return $this->requestStack->getCurrentRequest()->getRequestUri();
  }

  /**
   * Get the request's content.
   *
   * @return string
   *   The content.
   */
  private function getRequestContent(): string {
    return $this->requestStack->getCurrentRequest()->getContent();
  }

}
