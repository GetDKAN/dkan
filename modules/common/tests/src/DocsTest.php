<?php

namespace Drupal\Tests\common;

use Drupal\Core\Extension\Extension;
use Drupal\common\Docs;
use Drupal\Core\Extension\ModuleHandlerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use MockChain\Chain;
use MockChain\Options;

/**
 * Test class Docs.
 */
class DocsTest extends TestCase {

  /**
   *
   */
  public function testGetVersions() {
    $mockChain = $this->getCommonMockChain();
    $controller = Docs::create($mockChain->getMock());
    $response = $controller->getVersions();

    $spec = '{"version":1,"url":"\/api\/1"}';

    $this->assertEquals($spec, $response->getContent());
  }

  /**
   *
   */
  public function testGetComplete() {
    $mock = $this->getCommonMockChain()
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'get', NULL);

    $spec = '{"openapi":"3.0.1","info":{"title":"API Documentation","version":"Alpha"},"components":{"securitySchemes":{"basicAuth":{"type":"http","scheme":"basic"}}},"paths":{"\/api\/1\/metastore\/schemas\/dataset\/items\/{identifier}":{"get":{"summary":"Get this dataset","tags":["Dataset"],"parameters":[{"name":"identifier","in":"path","description":"Dataset uuid","required":true,"schema":{"type":"string"}}],"responses":{"200":{"description":"Ok"}}},"delete":{"summary":"This operation should not be present in dataset-specific docs.","security":[{"basicAuth":[]}],"responses":{"200":{"description":"Ok"}}},"post":null},"\/api\/1\/some\/other\/path":{"patch":{"summary":"This path and operation should not be present in dataset-specific docs.","security":[{"basicAuth":[]}],"responses":{"200":{"description":"Ok"}}}}}}';

    $controller = Docs::create($mock->getMock());
    $response = $controller->getComplete();

    $this->assertEquals($spec, $response->getContent());
  }

  /**
   *
   */
  public function testGetPublic() {
    $mock = $this->getCommonMockChain()
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'get', 'false');

    $spec = '{"openapi":"3.0.1","info":{"title":"API Documentation","version":"Alpha"},"components":[],"paths":{"\/api\/1\/metastore\/schemas\/dataset\/items\/{identifier}":{"get":{"summary":"Get this dataset","tags":["Dataset"],"parameters":[{"name":"identifier","in":"path","description":"Dataset uuid","required":true,"schema":{"type":"string"}}],"responses":{"200":{"description":"Ok"}}},"post":null}}}';

    $controller = Docs::create($mock->getMock());
    $response = $controller->getComplete();

    $this->assertEquals($spec, $response->getContent());
  }

  /**
   * Getter.
   */
  public function getCommonMockChain() {
    $options = (new Options())
      ->add('module_handler', ModuleHandlerInterface::class)
      ->add('request_stack', RequestStack::class)
      ->index(0);

    $mockChain = (new Chain($this))
      ->add(ContainerInterface::class, 'get', $options)
      ->add(ModuleHandlerInterface::class, 'getModule', Extension::class)
      ->add(Extension::class, 'getPath', __DIR__ . "/..");

    return $mockChain;
  }

}
