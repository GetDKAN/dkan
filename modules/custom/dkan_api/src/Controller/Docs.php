<?php

declare(strict_types = 1);

namespace Drupal\dkan_api\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Serialization\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
   * Serializer to translate yaml to json.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  private $serializer;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container) {
    $moduleHandler = $container->get('module_handler');
    return new Docs($moduleHandler);
  }

  /**
   * Constructor.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
    $this->serializer = new Yaml();
    $this->spec = $this->getJsonFromYmlFile();
  }

  /**
   * Ger version.
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
    $modulePath = $this->moduleHandler->getModule('dkan_api')->getPath();
    $ymlSpecPath = $modulePath . '/docs/dkan_api_openapi_spec.yml';
    $ymlSpec = $this->fileGetContents($ymlSpecPath);

    return $this->serializer->decode($ymlSpec);
  }

  /**
   * Wrapper around file_get_contents to facilitate testing.
   *
   * @param string $path
   *   Path for our yml spec.
   *
   * @return false|string
   *   Our yml file, or FALSE.
   *
   * @codeCoverageIgnore
   */
  private function fileGetContents($path) {
    return file_get_contents($path);
  }

  /**
   * Returns the complete API spec.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   OpenAPI spec response.
   */
  public function getComplete() {
    $jsonSpec = json_encode($this->spec);

    return $this->sendResponse($jsonSpec);
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
