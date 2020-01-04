<?php

namespace Drupal\Tests\dkan_metastore\Unit;

use PHPUnit\Framework\TestCase;
use Drupal\Core\Serialization\Yaml;
use Drupal\dkan_api\Controller\Docs;
use MockChain\Chain;
use MockChain\Options;
use Drupal\dkan_metastore\Service;
use Drupal\dkan_metastore\WebServiceApiDocs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class.
 */
class WebServiceApiDocsTest extends TestCase {

  /**
   *
   */
  public function testGetDatasetSpecific() {
    $mockChain = $this->getCommonMockChain();

    $controller = WebServiceApiDocs::create($mockChain->getMock());
    $response = $controller->getDatasetSpecific(1);

    $spec = '{"openapi":"3.0.1","info":{"title":"API Documentation","version":"Alpha"},"tags":[{"name":"Dataset"},{"name":"SQL Query"}],"paths":{"\/api\/1\/datastore\/sql":{"get":{"summary":"Query resources","tags":["SQL Query"],"responses":{"200":{"description":"Ok"}}}},"\/api\/1\/metastore\/schemas\/dataset\/items\/1":{"get":{"summary":"Get this dataset","tags":["Dataset"],"responses":{"200":{"description":"Ok"}}}}}}';

    $this->assertEquals($spec, $response->getContent());
  }

  /**
   *
   */
  private function getCommonMockChain() {
    $serializer = new Yaml();
    // Test against ./docs/dkan_api_openapi_spec.yml.
    $yamlSpec = file_get_contents(__DIR__ . "/docs/dkan_api_openapi_spec.yml");

    $mockChain = new Chain($this);
    $mockChain->add(ContainerInterface::class, 'get',
      (new Options)->add('dkan_api.docs', Docs::class)
        ->add('dkan_metastore.service', Service::class)
    )
      ->add(Docs::class, "getJsonFromYmlFile", $serializer->decode($yamlSpec))
      ->add(Service::class, "get", "{}");
    return $mockChain;
  }

}
