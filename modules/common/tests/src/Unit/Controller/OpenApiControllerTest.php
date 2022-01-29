<?php

namespace Drupal\Tests\common\Controller;

use Drupal\common\Controller\OpenApiController;
use Drupal\common\Plugin\DkanApiDocsBase;
use Drupal\common\DkanApiDocsGenerator;
use Drupal\common\Plugin\DkanApiDocsPluginManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Site\Settings;
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
    $spec = Yaml::decode(file_get_contents(__DIR__ . '/../../../docs/openapi_spec.yml'));
    $request = new Request();
    $controller = $this->getControllerMock($spec, $request);
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
    $spec = Yaml::decode(file_get_contents(__DIR__ . '/../../../docs/openapi_spec.yml'));
    unset($spec['openapi']);
    $request = new Request();
    $controller = $this->getControllerMock($spec, $request);
    $response = $controller->getComplete();

    $data = json_decode($response->getContent(), TRUE);
    $this->assertEquals(400, $response->getStatusCode());
    $this->assertEquals("JSON Schema validation failed.", $data['message']);
  }

  /**
   * Test removal of authenticated methods.
   */
  public function testGetPublic() {
    $spec = Yaml::decode(file_get_contents(__DIR__ . '/../../../docs/openapi_spec.yml'));
    $request = new Request(['authentication' => 'false']);
    $controller = $this->getControllerMock($spec, $request);
    $response = $controller->getComplete();

    $data = json_decode($response->getContent(), TRUE);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertArrayNotHasKey('components', $data);
  }

  /**
   * Test cache headers.
   */
  public function testGetCompleteCacheHeaders() {
    $spec = Yaml::decode(file_get_contents(__DIR__ . '/../../../docs/openapi_spec.yml'));
    $request = new Request();

    // Create a container with caching turned on.
    $container = $this->getContainerMock($spec, $request)
      ->add(Container::class, 'has', TRUE)
      ->add(ConfigFactoryInterface::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', 600);
    \Drupal::setContainer($container->getMock());

    $generator = $this->getGenerator($container->getMock());
    $controller = new OpenApiController($container->getMock()->get('request_stack'), $generator);

    // JSON. Caching on.
    $response = $controller->getComplete();
    $headers = $response->headers;

    $this->assertEquals('application/json', $headers->get('content-type'));
    $this->assertEquals('max-age=600, public', $headers->get('cache-control'));
    $this->assertNotEmpty($headers->get('last-modified'));

    // YAML. Caching on.
    $response = $controller->getComplete('yaml');
    $headers = $response->headers;

    $this->assertEquals('application/vnd.oai.openapi', $headers->get('content-type'));
    $this->assertEquals('max-age=600, public', $headers->get('cache-control'));
    $this->assertNotEmpty($headers->get('last-modified'));

    // Turn caching off.
    $container->add(ImmutableConfig::class, 'get', 0);
    \Drupal::setContainer($container->getMock());

    $generator = $this->getGenerator($container->getMock());
    $controller = new OpenApiController($container->getMock()->get('request_stack'), $generator);

    // JSON. No caching.
    $response = $controller->getComplete();
    $headers = $response->headers;

    $this->assertEquals('application/json', $headers->get('content-type'));
    $this->assertEquals('no-cache, private', $headers->get('cache-control'));
    $this->assertEmpty($headers->get('vary'));
    $this->assertEmpty($headers->get('last-modified'));

    // YAML. No caching.
    $response = $controller->getComplete('yaml');
    $headers = $response->headers;

    $this->assertEquals('application/vnd.oai.openapi', $headers->get('content-type'));
    $this->assertEquals('no-cache, private', $headers->get('cache-control'));
    $this->assertEmpty($headers->get('vary'));
    $this->assertEmpty($headers->get('last-modified'));
  }

  /**
  *
  */
  private function getGenerator($containerMock): DkanApiDocsGenerator {
    $manager = $containerMock->get('plugin.manager.dkan_api_docs');
    $settings = $containerMock->get('settings');
    return new DkanApiDocsGenerator($manager, $settings);
  }

  /**
   * Generate mock controller.
   */
  private function getControllerMock($spec, Request $request) {
    $container = $this->getContainerMock($spec, $request)->getMock();

    $generator = $this->getGenerator($container);
    return new OpenApiController($container->get('request_stack'), $generator);
  }

  /**
   * Generate mock container.
   */
  private function getContainerMock($spec, Request $request) {
    $options = (new Options())
      ->add('module_handler', ModuleHandler::class)
      ->add('request_stack', RequestStack::class)
      ->add('plugin.manager.dkan_api_docs', DkanApiDocsPluginManager::class)
      ->add('config.factory', ConfigFactoryInterface::class)
      ->add('settings', new Settings([]))
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
      ->add(RequestStack::class, 'getCurrentRequest', $request);
  }

}
