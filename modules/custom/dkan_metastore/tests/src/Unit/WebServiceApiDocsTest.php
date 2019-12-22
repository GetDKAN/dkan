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

    // Test against ./docs/dkan_api_openapi_spec.yml.
    $endpointsToKeep = [
      // Target paths.
      '/api/1/metastore/schemas/dataset/items/{identifier}' => ['get'],
      '/api/1/datastore/sql' => ['get'],
      // Non-existent operation.
      '/api/1/some/other/path' => ['get'],
      // Non-existent path.
      'api/1/non/existent/path' => ['put'],
    ];

    $controller = WebServiceApiDocs::create($mockChain->getMock());
    $response = $controller->getDatasetSpecific(1, $endpointsToKeep);

    $spec = '{"openapi":"3.0.1","info":{"title":"API Documentation","version":"Alpha"},"paths":{"\/api\/1\/metastore\/schemas\/dataset\/items\/1":{"get":{"summary":"Get this dataset","tags":["Dataset"],"responses":{"200":{"description":"Ok"}}}}},"tags":[{"name":"Dataset"},{"name":"SQL Query"}]}';

    $this->assertEquals($spec, $response->getContent());
  }

  /**
   *
   */
  private function getCommonMockChain() {
    $serializer = new Yaml();
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
