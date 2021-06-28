<?php

namespace Drupal\Tests\common\Controller;

use Drupal\common\Controller\OpenApiController;
use Drupal\common\Plugin\DkanApiDocsBase;
use Drupal\common\Plugin\DkanApiDocsPluginManager;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Serialization\Yaml;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Test the DKAN Docs controller.
 */
class OpenApiControllerTest extends TestCase {

  /**
   * Test spec generation from sample yaml file.
   */
  public function testGetComplete() {
    $spec = Yaml::decode(file_get_contents(__DIR__ . '/../../docs/openapi_spec.yml'));
    $request = new Request();
    $container = $this->getContainerMock($spec, $request);

    $controller = OpenApiController::create($container);
    $response = $controller->getComplete();

    $data = json_decode($response->getContent(), TRUE);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals("Test Spec (valid)", $data['info']['title']);
    $this->assertArrayHasKey('components', $data);
  }

  /**
   * Test invalid spec.
   */
  public function testGetInvalid() {
    $spec = Yaml::decode(file_get_contents(__DIR__ . '/../../docs/openapi_spec.yml'));
    unset($spec['openapi']);
    $request = new Request();
    $container = $this->getContainerMock($spec, $request);

    $controller = OpenApiController::create($container);
    $response = $controller->getComplete();

    $data = json_decode($response->getContent(), TRUE);
    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals("JSON Schema validation failed.", $data['message']);
  }

  /**
   * Test removal of authenticated methods.
   */
  public function testGetPublic() {
    $spec = Yaml::decode(file_get_contents(__DIR__ . '/../../docs/openapi_spec.yml'));
    $request = new Request(['authentication' => 'false']);
    $container = $this->getContainerMock($spec, $request);

    $controller = OpenApiController::create($container);
    $response = $controller->getComplete();

    $data = json_decode($response->getContent(), TRUE);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertArrayNotHasKey('components', $data);
  }

  /**
   * Generate mock container.
   */
  private function getContainerMock($spec, Request $request) {
    $options = (new Options())
      ->add('module_handler', ModuleHandler::class)
      ->add('request_stack', RequestStack::class)
      ->add('plugin.manager.dkan_api_docs', DkanApiDocsPluginManager::class)
      ->index(0);

    $pluginDefinition = [
      'id' => 'test_dkan_api_docs',
      'description' => 'Testing the docs',
      'class' => 'Drupal\common\Tests\Controller\TestApiDocs',
      'provider' => 'common',
    ];

    $pluginMock = (new Chain($this))
      ->add(DkanApiDocsBase::class, 'spec', $spec)
      ->getMock();

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(DkanApiDocsPluginManager::class, 'getDefinitions', [$pluginDefinition])
      ->add(DkanApiDocsPluginManager::class, 'createInstance', $pluginMock)
      ->add(RequestStack::class, 'getCurrentRequest', $request)
      ->getMock();
  }

}
