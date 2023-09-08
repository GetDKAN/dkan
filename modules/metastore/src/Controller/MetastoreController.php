<?php

namespace Drupal\metastore\Controller;

use Drupal\common\JsonResponseTrait;
use Drupal\metastore\Exception\CannotChangeUuidException;
use Drupal\metastore\Exception\InvalidJsonException;
use Drupal\metastore\Exception\MetastoreException;
use Drupal\metastore\Exception\MissingPayloadException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\metastore\DatasetApiDocs;
use Drupal\metastore\MetastoreApiResponse;
use Drupal\metastore\MetastoreService;

/**
 * Class Api.
 *
 * @todo Move docs stuff.
 */
class MetastoreController implements ContainerInjectionInterface {
  use JsonResponseTrait;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Metastore service.
   *
   * @var \Drupal\metastore\MetastoreService
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
    return new static(
      $container->get('dkan.metastore.api_response'),
      $container->get('dkan.metastore.service'),
      $container->get('dkan.metastore.dataset_api_docs')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(MetastoreApiResponse $apiResponse, MetastoreService $service, DatasetApiDocs $docs) {
    $this->apiResponse = $apiResponse;
    $this->service = $service;
    $this->docs = $docs;
  }

  /**
   * Get schemas.
   */
  public function getSchemas() {
    return $this->apiResponse->cachedJsonResponse($this->service->getSchemas());
  }

  /**
   * Get schema.
   */
  public function getSchema(string $identifier) {
    try {
      return $this->apiResponse->cachedJsonResponse($this->service->getSchema($identifier));
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 404);
    }
  }

  /**
   * Get all.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function getAll(string $schema_id, Request $request) {
    $keepRefs = $this->wantObjectWithReferences($request);

    $output = array_map(function ($object) use ($keepRefs) {
      $modified_object = $keepRefs
        ? $this->service->swapReferences($object)
        : $this->service->removeReferences($object);
      return (object) $modified_object->get('$');
    }, $this->service->getAll($schema_id));

    $output = array_values($output);
    return $this->apiResponse->cachedJsonResponse($output, 200, [$schema_id], $request->query);
  }

  /**
   * Implements GET method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   *
   * @throws \InvalidArgumentException
   *   When an unpublished or invalid resource is requested.
   */
  public function get(string $schema_id, string $identifier, Request $request) {
    try {
      $object = $this->service->get($schema_id, $identifier);
      if ($this->wantObjectWithReferences($request)) {
        $object = $this->service->swapReferences($object);
      }
      else {
        $object = MetastoreService::removeReferences($object);
      }
      $object = (object) $object->get('$');
      return $this->apiResponse->cachedJsonResponse($object, 200, [$schema_id => [$identifier]], $request->query);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 404);
    }
  }

  /**
   * Determine if we want to inject the reference metadata into the response.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return bool
   *   True if we want references.
   */
  private function wantObjectWithReferences(Request $request) {
    $param = $request->get('show-reference-ids', FALSE);
    $param2 = $request->get('show_reference_ids', FALSE);
    if ($param === FALSE && $param2 === FALSE) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Implements POST method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function post(string $schema_id, Request $request) {
    try {
      $data = $request->getContent();
      $this->checkIdentifier($data);
      $data = $this->service->getValidMetadataFactory()->get($data, $schema_id, ['method' => 'POST']);
      $identifier = $this->service->post($schema_id, $data);
      return $this->apiResponse->cachedJsonResponse([
        "endpoint" => "{$request->getRequestUri()}/{$identifier}",
        "identifier" => $identifier,
      ], 201);
    }
    catch (MetastoreException $e) {
      return $this->getResponseFromException($e, $e->httpCode());
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 400);
    }
  }

  /**
   * Publish the latest revision of a dataset.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function publish(string $schema_id, string $identifier, Request $request) {
    try {
      $this->service->publish($schema_id, $identifier);
      return $this->apiResponse->cachedJsonResponse((object) [
        "endpoint" => "{$request->getRequestUri()}/publish",
        "identifier" => $identifier,
      ]);
    }
    catch (MetastoreException $e) {
      return $this->getResponseFromException($e, $e->httpCode());
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 400);
    }
  }

  /**
   * Implements PUT method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function put($schema_id, string $identifier, Request $request) {
    try {
      $data = $request->getContent();
      $this->checkIdentifier($data, $identifier);
      $data = $this->service->getValidMetadataFactory()->get($data, $schema_id);
      $info = $this->service->put($schema_id, $identifier, $data);
      $code = ($info['new'] == TRUE) ? 201 : 200;
      return $this->apiResponse->cachedJsonResponse(
        [
          "endpoint" => $request->getRequestUri(),
          "identifier" => $info['identifier'],
        ],
        $code
      );
    }
    catch (MetastoreException $e) {
      return $this->getResponseFromException($e, $e->httpCode());
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 400);
    }
  }

  /**
   * Implements PATCH method.
   *
   * @param string $schema_id
   *   The {schema_id} slug from the HTTP request.
   * @param string $identifier
   *   Identifier.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The json response.
   */
  public function patch($schema_id, $identifier, Request $request) {

    try {
      $data = $request->getContent();

      if (empty($data)) {
        throw new MissingPayloadException("Empty body");
      }
      $obj = json_decode($data);
      if (!$obj) {
        throw new InvalidJsonException("Invalid JSON");
      }
      $this->checkIdentifier($data, $identifier);

      $this->service->patch($schema_id, $identifier, $data);
      return $this->apiResponse->cachedJsonResponse((object) [
        "endpoint" => $request->getRequestUri(),
        "identifier" => $identifier,
      ]);
    }
    catch (MetastoreException $e) {
      return $this->getResponseFromException($e, $e->httpCode());
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e, 400);
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
      return $this->apiResponse->cachedJsonResponse((object) ["message" => "Dataset {$identifier} has been deleted."]);
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e);
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
      return $this->apiResponse->cachedJsonResponse($this->service->getCatalog());
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e);
    }
  }

  /**
   * Get the API docs spec for a specific dataset.
   *
   * @param string $identifier
   *   Dataset identifier.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response.
   */
  public function getDocs($identifier, Request $request) : JsonResponse {
    try {
      return $this->apiResponse->cachedJsonResponse(
        $this->docs->getDatasetSpecific($identifier),
        200,
        ['dataset' => [$identifier]],
        $request->query
      );
    }
    catch (\Exception $e) {
      return $this->getResponseFromException($e);
    }
  }

  /**
   * Check that the given identifier is the same that in the JSON data.
   *
   * @param string $data
   *   JSON data to decode and check.
   * @param mixed $identifier
   *   Identifier.
   *
   * @throws \Drupal\metastore\Exception\CannotChangeUuidException
   *   Thrown when the identifiers are different.
   */
  private function checkIdentifier(string $data, $identifier = NULL) {
    $obj = json_decode($data);
    if (isset($identifier) && isset($obj->identifier) && $obj->identifier != $identifier) {
      throw new CannotChangeUuidException("Identifier cannot be modified");
    }
  }

}
