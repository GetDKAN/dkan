<?php

namespace Drupal\common\Controller;

use Drupal\common\CacheableResponseTrait;
use Drupal\common\JsonResponseTrait;
use Drupal\common\DkanApiDocsGenerator;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
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
  use CacheableResponseTrait;

  /**
   * API Docs generator class.
   *
   * @var Drupal\common\Controller\DkanApiDocsGenerator
   */
  protected $generator;

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
      $container->get('request_stack'),
      $container->get('dkan.common.docs_generator')
    );
  }

  /**
   * Constructor.
   *
   * @param Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request stack.
   * @param \Drupal\common\DkanApiDocsGenerator $generator
   *   API Docs generator.
   */
  public function __construct(
    RequestStack $requestStack,
    DkanApiDocsGenerator $generator
  ) {
    $this->requestStack = $requestStack;
    $this->generator = $generator;
  }

  /**
   * Get version.
   */
  public function getVersions() {
    $response = new JsonResponse(["version" => 1, "url" => "/api/1"]);
    return $this->addCacheHeaders($response);
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
    $response = new Response(Yaml::encode($spec), 200, ['Content-type' => 'application/vnd.oai.openapi']);
    return $this->addCacheHeaders($response);
  }

}
