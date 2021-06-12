<?php

namespace Drupal\common\Controller;

use Drupal\common\JsonResponseTrait;
use Drupal\common\Plugin\DkanApiDocsGenerator;
use Drupal\common\Plugin\DkanApiDocsPluginManager;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use OpisErrorPresenter\Implementation\MessageFormatterFactory;
use OpisErrorPresenter\Implementation\PresentedValidationErrorFactory;
use OpisErrorPresenter\Implementation\Strategies\BestMatchError;
use OpisErrorPresenter\Implementation\ValidationErrorPresenter;
use RootedData\Exception\ValidationException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Serves openapi spec for dataset-related endpoints.
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
  public function getComplete() {
    try {
      $spec = $this->generator->buildSpec();
    }
    catch (ValidationException $e) {
      $result = $e->getResult();
      $presenter = new ValidationErrorPresenter(
        new PresentedValidationErrorFactory(
            new MessageFormatterFactory()
        ),
        new BestMatchError()
      );
      $presented = $presenter->present(...$result->getErrors());
      return $this->getResponse($presented);
    }
    if ($this->requestStack->getCurrentRequest()->get('authentication') === "false") {
      $publicSpec = AuthCleanupHelper::makePublicSpec($spec);
      return $this->getResponse($publicSpec->{'$'});
    }
    return $this->getResponse($spec->{'$'});
  }

}
