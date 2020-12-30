<?php

declare(strict_types = 1);

namespace Drupal\common;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Serialization\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Serves openapi spec for dataset-related endpoints.
 */
class Docs implements ContainerInjectionInterface {

  /**
   * The API array spec, to ease manipulation, before json encoding.
   *
   * @var array
   */
  private $spec;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Serializer to translate yaml to json.
   *
   * @var \Drupal\Component\Serialization\SerializationInterface
   */
  private $serializer;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   */
  public static function create(ContainerInterface $container) {
    return new Docs(
      $container->get('module_handler'),
      $container->get('request_stack')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(
    ModuleHandlerInterface $moduleHandler,
    RequestStack $requestStack
  ) {
    $this->moduleHandler = $moduleHandler;
    $this->requestStack = $requestStack;
    $this->serializer = new Yaml();
    $this->spec = $this->getJsonFromYmlFile();
  }

  /**
   * Get version.
   */
  public function getVersions() {
    return new JsonResponse(["version" => 1, "url" => "/api/1"]);
  }

  /**
   * Load the yaml spec file and convert it to an array.
   *
   * @return array
   *   The openapi spec.
   */
  public function getJsonFromYmlFile() {
    $modulePath = $this->moduleHandler->getModule('common')->getPath();
    $ymlSpecPath = $modulePath . '/docs/openapi_spec.yml';
    $ymlSpec = file_get_contents($ymlSpecPath);

    return $this->serializer->decode($ymlSpec);
  }

  /**
   * Returns the complete API spec.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   OpenAPI spec response.
   */
  public function getComplete() {
    if ($this->requestStack->getCurrentRequest()->get('authentication') === "false") {
      $spec = $this->getPublic();
    }
    else {
      $spec = $this->spec;
    }

    $jsonSpec = json_encode($spec);
    return $this->sendResponse($jsonSpec);
  }

  /**
   * Return a publicly-accessible version of the API spec.
   *
   * Remove any endpoint requiring authentication, as well as the security
   * schemes components from the api spec.
   *
   * @return array
   *   The modified API spec, without authentication-related items.
   */
  private function getPublic() {
    $publicSpec = $this->removeAuthenticatedEndpoints($this->spec);
    $cleanSpec = $this->cleanUpEndpoints($publicSpec);
    unset($cleanSpec['components']['securitySchemes']);
    return $cleanSpec;
  }

  /**
   * Remove API spec endpoints requiring authentication.
   *
   * @param array $spec
   *   The original spec.
   *
   * @return array
   *   The modified API spec, without authenticated endpoints.
   */
  private function removeAuthenticatedEndpoints(array $spec) {
    foreach ($spec['paths'] as $path => $operations) {
      $this->removeAuthenticatedOperations($operations, $path, $spec);
    }
    return $spec;
  }

  /**
   * Within a path, remove operations requiring authentication.
   *
   * @param array $operations
   *   Operations for the current path.
   * @param string $path
   *   The path being processed.
   * @param array $spec
   *   Our modified dataset-specific openapi spec.
   */
  private function removeAuthenticatedOperations(array $operations, string $path, array &$spec) {
    foreach ($operations as $operation => $details) {
      if (isset($spec['paths'][$path][$operation]['security'])) {
        unset($spec['paths'][$path][$operation]);
      }
    }
  }

  /**
   * Clean up empty endpoints from the spec.
   *
   * @param array $spec
   *   The original spec.
   *
   * @return array
   *   The cleaned up API spec.
   */
  private function cleanUpEndpoints(array $spec) {
    foreach ($spec['paths'] as $path => $operations) {
      if (empty($operations)) {
        unset($spec['paths'][$path]);
      }
    }
    return $spec;
  }

  /**
   * Helper function to set headers and send response.
   *
   * @param string $jsonSpec
   *   OpenAPI spec encoded json response.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   OpenAPI spec response.
   */
  private function sendResponse(string $jsonSpec) {
    return new JsonResponse(
              $jsonSpec,
              200,
              [
                'Content-type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
              ],
              TRUE
          );
  }

}
