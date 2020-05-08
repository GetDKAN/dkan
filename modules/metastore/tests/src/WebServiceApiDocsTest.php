<?php

namespace Drupal\Tests\metastore\Unit;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\common\Plugin\DataModifierManager;
use Drupal\common\Plugin\DataModifierBase;
use PHPUnit\Framework\TestCase;
use Drupal\Core\Serialization\Yaml;
use Drupal\common\Docs;
use MockChain\Chain;
use MockChain\Options;
use Drupal\metastore\Service;
use Drupal\metastore\WebServiceApiDocs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class.
 */
class WebServiceApiDocsTest extends TestCase {

  /**
   * Tests dataset-specific docs without data modifier plugin.
   */
  public function testDatasetSpecificDocsWithoutSqlModifier() {
    $dataset = json_encode([
      'distribution' => [
      [
        'identifier' => 'dist-1234',
        'data' => [
          'title' => 'Title',
          'description' => 'Description',
        ],
      ],
      ],
    ]);

    $mockChain = $this->getCommonMockChain()
      ->add(Service::class, "get", $dataset)
      ->add(DataModifierManager::class, 'getDefinitions', [])
      ->add(SelectInterface::class, 'fetchCol', []);

    $controller = WebServiceApiDocs::create($mockChain->getMock());
    $response = $controller->getDatasetSpecific(1);

    $spec = '{"openapi":"3.0.1","info":{"title":"API Documentation","version":"Alpha"},"tags":[],"paths":{"\/random\/prefix\/api\/1\/metastore\/schemas\/dataset\/items\/1":{"get":{"summary":"Get this dataset","tags":["Dataset"],"parameters":[{"name":"identifier","in":"path","description":"Dataset uuid","required":true,"schema":{"type":"string"},"example":"1"}],"responses":{"200":{"description":"Ok"}}}},"\/random\/prefix\/api\/1\/datastore\/sql?query=[SELECT * FROM dist-1234];":{"get":{"summary":"Query resources","tags":["SQL Query"],"parameters":[{"name":"query","in":"query","description":"SQL query","required":true,"schema":{"type":"string"},"example":"[SELECT * FROM dist-1234];"}],"responses":{"200":{"description":"Ok"}}}}},"components":{"parameters":{"query":{"name":"query","in":"query","description":"SQL query","required":true,"schema":{"type":"string"},"example":"[SELECT * FROM DATASTORE-UUID];"}}}}';
    $this->assertEquals($spec, $response->getContent());
  }

  /**
   * Tests dataset-specific docs when SQL endpoint is protected.
   */
  public function testDatasetSpecificDocsWithSqlModifier() {
    $mockChain = $this->getCommonMockChain()
      ->add(Service::class, "get", "{}")
      ->add(DataModifierManager::class, 'getDefinitions', [['id' => 'foobar']])
      ->add(DataModifierManager::class, 'createInstance', DataModifierBase::class)
      ->add(DataModifierBase::class, 'requiresModification', TRUE);

    $controller = WebServiceApiDocs::create($mockChain->getMock());
    $response = $controller->getDatasetSpecific(1);

    $spec = '{"openapi":"3.0.1","info":{"title":"API Documentation","version":"Alpha"},"tags":[],"paths":{"\/random\/prefix\/api\/1\/metastore\/schemas\/dataset\/items\/1":{"get":{"summary":"Get this dataset","tags":["Dataset"],"parameters":[{"name":"identifier","in":"path","description":"Dataset uuid","required":true,"schema":{"type":"string"},"example":"1"}],"responses":{"200":{"description":"Ok"}}}}},"components":{"parameters":{"query":{"name":"query","in":"query","description":"SQL query","required":true,"schema":{"type":"string"},"example":"[SELECT * FROM DATASTORE-UUID];"}}}}';
    $this->assertEquals($spec, $response->getContent());
  }

  /**
   *
   */
  private function getCommonMockChain() {
    $serializer = new Yaml();
    // Test against ./docs/common_openapi_spec.yml.
    $yamlSpec = file_get_contents(__DIR__ . "/docs/openapi_spec.yml");

    $mockChain = new Chain($this);
    $mockChain->add(ContainerInterface::class, 'get',
      (new Options)->add('common.docs', Docs::class)
        ->add('metastore.service', Service::class)
        ->add('plugin.manager.common.data_modifier', DataModifierManager::class)
        ->index(0)
    )
      ->add(Docs::class, "getJsonFromYmlFile", $serializer->decode($yamlSpec));

    return $mockChain;
  }

}
