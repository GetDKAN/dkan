<?php

namespace Drupal\Tests\dkan_api\Unit\Controller;

use Drupal\dkan_common\Tests\Mock\Chain;
use Drupal\dkan_common\Tests\Mock\Options;
use Drupal\dkan_metastore\Controller\Api;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_schema\SchemaRetriever;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dkan_data\Storage\Data;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class.
 */
class DocsTest extends DkanTestBase {

  /**
   *
   */
  public function testGetDatasetSpecific() {
    $mockChain = $this->getCommonMockChain();

    $controller = Api::create($mockChain->getMock());
    $response = $controller->getDatasetSpecific(1);

    $spec = '{"openapi":"3.0.1","info":{"title":"API Documentation","version":"Alpha"},"paths":{"\/api\/v1\/dataset\/1":{"get":{"summary":"Get this dataset","tags":["Dataset"],"responses":{"200":{"description":"Ok"}}}}},"tags":[{"name":"Dataset"},{"name":"SQL Query"}]}';

    $this->assertEquals($spec, $response->getContent());
  }

  /**
   *
   */
  private function getCommonMockChain() {
    $mockChain = new Chain($this);
    $mockChain->add(ContainerInterface::class, 'get',
      (new Options)->add('request_stack', RequestStack::class)
        ->add('dkan_schema.schema_retriever', SchemaRetriever::class)
        ->add('dkan_data.storage', Data::class)
    );
    $mockChain->add(SchemaRetriever::class, 'retrieve', "{}");
    return $mockChain;
  }

}
