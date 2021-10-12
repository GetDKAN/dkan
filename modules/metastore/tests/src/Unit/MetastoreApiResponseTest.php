<?php

namespace Drupal\Tests\metastore\Unit;

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\metastore\MetastoreApiResponse;
use Drupal\metastore\NodeWrapper\Data as NodeWrapperData;
use Drupal\metastore\NodeWrapper\NodeDataFactory;
use MockChain\Chain;
use MockChain\Options;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 *
 */
class MetastoreApiResponseTest extends TestCase {

  /**
   *
   */
  public function testDatasetCacheTagsAndContext() {
    $container = $this->getContainer();
    \Drupal::setContainer($container);

    $service = new MetastoreApiResponse(\Drupal::service('dkan.metastore.metastore_item_factory'));
    $deps = ['dataset' => ['055e4cf7-4ecc-4a49-98e8-106c6532e743']];
    $response = $service->cachedJsonResponse('{}', 200, $deps, new ParameterBag(['foo' => 'bar']));
    $this->assertEquals(
      ['node:1', 'node:2', 'node:3', 'node:4'],
      $response->getCacheableMetadata()->getCacheTags()
    );
    $this->assertContains('url.query_args:foo', $response->getCacheableMetadata()->getCacheContexts());
  }

  public function testSchemaCacheTags() {
    $container = $this->getContainer();
    \Drupal::setContainer($container);

    $service = new MetastoreApiResponse(\Drupal::service('dkan.metastore.metastore_item_factory'));
    $deps = ['dataset'];
    $response = $service->cachedJsonResponse('{}', 200, $deps);
    $this->assertEquals(['node_list:data'], $response->getCacheableMetadata()->getCacheTags());
  }

  public function testInvalidCacheTags() {
    $container = $this->getContainer();
    \Drupal::setContainer($container);

    $service = new MetastoreApiResponse(\Drupal::service('dkan.metastore.metastore_item_factory'));
    // Identifiers must be in arrays keyed by schema.
    $deps = [['055e4cf7-4ecc-4a49-98e8-106c6532e743']];
    $this->expectException(\InvalidArgumentException::class);
    $service->cachedJsonResponse('{}', 200, $deps);
  }

  /**
   * Private.
   */
  private function getContainer() {

    $options = (new Options)
      ->add('dkan.metastore.metastore_item_factory', NodeDataFactory::class)
      ->add('cache_contexts_manager', CacheContextsManager::class)
      ->index(0);

    $dataset = $this->getDataset();
    $getMetadataResults = (new Sequence())
      ->add($dataset)
      ->add($dataset->{'%Ref:keyword'}[0])
      ->add($dataset->{'%Ref:distribution'}[0]);

    $getCacheTagsResults = (new Sequence())
      ->add(['node:1'])
      ->add(['node:2'])
      ->add(['node:3'])
      ->add(['node:4']);

    $mockChain = (new Chain($this))
      ->add(ContainerInterface::class, 'get', $options)
      ->add(Service::class, 'getSchemas', ['dataset'])
      ->add(CacheContextsManager::class, 'assertValidTokens', TRUE)
      ->add(Service::class, 'getSchema', (object) ["id" => "http://schema"])
      ->add(NodeDataFactory::class, 'getInstance', NodeWrapperData::class)
      ->add(NodeWrapperData::class, 'getMetadata', $getMetadataResults)
      ->add(NodeWrapperData::class, 'getCacheTags', $getCacheTagsResults)
      ->add(NodeWrapperData::class, 'getCacheContexts', ['url'])
      ->add(NodeWrapperData::class, 'getCacheMaxAge', 0);

    return $mockChain->getMock();
  }

  private function getDataset() {
    $json = <<<EOF
{
    "title": "1",
    "description": "Some description.",
    "identifier": "055e4cf7-4ecc-4a49-98e8-106c6532e743",
    "keyword": [
        "some keyword"
    ],
    "nonArrayRef": "foo",
    "distribution": [
        {
            "downloadUrl": "http://blah/data.csv"
        }
    ],
    "%Ref:keyword": [
        {
            "identifier": "f77581b3-6277-5f1b-b4fb-7e865deb6848",
            "data": "some keyword"
        }
    ],
    "%Ref:distribution": [
        {
            "identifier": "199ff2e2-23e5-5253-be14-448cd50f492d",
            "data": {
                "%Ref:downloadURL": [
                    {
                        "identifier": "4c6e70676fe38b4b63c4a5988e07e54b__1631200243__source"
                    }
                ]
            }
        }
    ],
    "%Ref:nonArrayRef": {
        "identifier": "63e96b71-734b-45b1-ae44-57c6871798d2",
        "data": "foo"
    }
}
EOF;
    return json_decode($json);
  }

}
