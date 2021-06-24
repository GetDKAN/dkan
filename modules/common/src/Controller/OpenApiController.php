<?php

namespace Drupal\common\Controller;

use Drupal\common\JsonResponseTrait;
use Drupal\common\Plugin\DkanApiDocsGenerator;
use Drupal\common\Plugin\DkanApiDocsPluginManager;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Serialization\Yaml;
use RootedData\Exception\ValidationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves openapi spec for dataset-related endpoints.
 *
 * @codeCoverageIgnore
 */
class OpenApiController implements ContainerInjectionInterface {
  use JsonResponseTrait;

  /**
   * API Docs generator class.
   *
   * @var Drupal\common\Controller\DkanApiDocsGenerator
   */
  protected $generator;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Serializer to translate yaml to json.
   *
   * @var \Drupal\Component\Serialization\SerializationInterface
   */
  protected $serializer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new OpenApiController(
      $container->get('module_handler'),
      $container->get('request_stack'),
      $container->get('plugin.manager.dkan_api_docs')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(
    ModuleHandlerInterface $moduleHandler,
    RequestStack $requestStack,
    DkanApiDocsPluginManager $manager
  ) {
    $this->moduleHandler = $moduleHandler;
    $this->requestStack = $requestStack;
    $this->generator = new DkanApiDocsGenerator($manager);
  }

  /**
   * Get version.
   */
  public function getVersions() {
    return new JsonResponse(["version" => 1, "url" => "/api/1"]);
  }

  /**
   * Returns the complete API spec.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   OpenAPI spec response.
   */
  public function getComplete($format = "json") {
    try {
      $spec = $this->generator->buildSpec();
    }
    catch (ValidationException $e) {
      return $this->getResponseFromException($e);
    }
    if ($this->requestStack->getCurrentRequest()->get('authentication') === "false") {
      $spec = AuthCleanupHelper::makePublicSpec($spec);
    }
    if ($format == "yaml") {
      return $this->getYamlResponse($spec->{'$'});
    }
    return $this->getResponse($spec->{'$'});
  }

  /**
   * Helper function to set headers and send response.
   *
   * @param string $spec
   *   OpenAPI spec encoded json response.
   * @param int $code
   *   HTTP response code.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   OpenAPI spec response.
   */
  private function getYamlResponse($spec, $code = 200) {
    return new Response(Yaml::encode($spec), 200, ['Content-type' => 'application/vnd.oai.openapi']);
  }

}
