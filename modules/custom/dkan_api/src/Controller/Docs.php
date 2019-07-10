<?php
declare(strict_types=1);

namespace Drupal\dkan_api\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class Docs implements ContainerInjectionInterface {

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
   * @{inheritdocs}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container) {
    return new static($container);
  }

  /**
   * Constructor.
   *
   * @codeCoverageIgnore
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
    $this->moduleHandler = $container->get('module_handler');
    $this->dkanFactory = $container->get('dkan.factory');
  }

  /**
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getYmlSpec() {
    $modulePath = $this->moduleHandler->getModule('dkan_api')->getPath();
    $ymlSpecPath = $modulePath . '/docs/dkan_api_openapi_spec.yml';
    $ymlSpec = file_get_contents($ymlSpecPath);

    $response = $this->dkanFactory
      ->newJsonResponse();
    $response->headers->set('Content-type', 'text/plain');
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->setContent($ymlSpec);

    return $response;
  }
}
