<?php
declare(strict_types=1);

namespace Drupal\dkan_api\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Docs implements ContainerInjectionInterface {

  /**
   * The API array spec, to ease manipulation, before json encoding.
   *
   * @var array
   */
  protected $spec;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Factory to generate various dkan classes.
   *
   * @var \Drupal\dkan_common\Service\Factory
   */
  protected $dkanFactory;

  /**
   * Serializer to translate yaml to json.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $ymlSerializer;

  /**
   * @{inheritdocs}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container) {
    return new static($container);
  }

  /**
   * Constructor.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
    $this->moduleHandler = $container->get('module_handler');
    $this->dkanFactory = $container->get('dkan.factory');
    $this->ymlSerializer = $container->get('serialization.yaml');

    $this->spec = $this->getJsonFromYmlFile();
  }

  /**
   * Load the yaml spec file and convert it to an array
   *
   * @return array
   */
  protected function getJsonFromYmlFile() {
    $modulePath = $this->moduleHandler->getModule('dkan_api')->getPath();
    $ymlSpecPath = $modulePath . '/docs/dkan_api_openapi_spec.yml';
    $ymlSpec = $this->fileGetContents($ymlSpecPath);

    return $this->ymlSerializer->decode($ymlSpec);
  }

  /**
   * Wrapper around file_get_contents to facilitate testing.
   *
   * @param string $path
   *
   * @return false|string
   *
   * @codeCoverageIgnore
   */
  protected function fileGetContents($path) {
    return file_get_contents($path);
  }

  /**
   * Returns the complete API spec.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getComplete() {
    $jsonSpec = json_encode($this->spec);

    return $this->sendResponse($jsonSpec);
  }

  /**
   * Returns only the publicly accessible GET requests for the API spec.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getUnauthenticated() {
    $specAnon =  $this->filterSpecOperations($this->spec, ['get']);
    $jsonSpecAnon = json_encode($specAnon);

    return $this->sendResponse($jsonSpecAnon);
  }

  /**
   * Helper function to set headers and send response.
   *
   * @param string $jsonSpecAnon
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function sendResponse(string $jsonSpecAnon) {
    $response = $this->dkanFactory
      ->newJsonResponse();
    $response->headers->set('Content-type', 'application/json');
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->setContent($jsonSpecAnon);

    return $response;
  }

  /**
   * Removes from the api spec's paths the operations not whitelisted.
   *
   * @param \stdClass $original
   *   The original spec array.
   * @param array $operations_allowed
   *   Array of operations allowed.
   *
   * @return array
   *   Modified spec, keeping only the specified operations.
   */
  protected function filterSpecOperations(array $original, array $operations_allowed) {
    $spec = $original;

    foreach ($spec['paths'] as $path => $operations) {
      foreach ($operations as $verb => $details) {
        if (!in_array($verb, $operations_allowed)) {
          unset($spec['paths'][$path][$verb]);
        }
      }
      if (empty($spec['paths'][$path])) {
        unset($spec['paths'][$path]);
      }
    }

    return $spec;
  }

}
