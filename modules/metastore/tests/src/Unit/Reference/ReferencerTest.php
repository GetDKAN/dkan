<?php

namespace Drupal\Tests\metastore\Unit\Reference;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\metastore\Reference\Referencer;
use Drupal\metastore\ResourceMapper;
use Drupal\node\NodeStorage;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ReferencerTest extends TestCase {

  public function testHostify() {
    $container = (new Chain($this))
      ->add(Container::class, 'get', RequestStack::class)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getHost', 'test.test')
      ->getMock();

    \Drupal::setContainer($container);

    $this->assertEquals(
      'http://h-o.st/mycsv.txt',
      Referencer::hostify("http://test.test/mycsv.txt"));
  }

  /**
   *
   */
  private function getData($downloadUrl) {
    $data = '
    {
      "title": "Test Dataset No Media Type",
      "description": "Hi",
      "identifier": "12345",
      "accessLevel": "public",
      "modified": "06-04-2020",
      "keyword": ["hello"],
        "distribution": [
          {
            "title": "blah",
            "downloadURL": "' . $downloadUrl . '"
          }
        ]
    }';
    return json_decode($data);
  }

  /**
   *
   */
  public function testNoMediaType() {
    $immutableConfig = (new Chain($this))
      ->add(ImmutableConfig::class, 'get', $this->getPropertyList())
      ->getMock();

    $configService = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', $immutableConfig)
      ->getMock();

    $node = new class {
      public function uuid() {
        return '0398f054-d712-4e20-ad1e-a03193d6ab33';
      }
    };
    $nodeStorage = (new Chain($this))
      ->add(NodeStorage::class, 'loadByProperties', [$node])
      ->getMock();

    $options = (new Options())
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('request_stack', RequestStack::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getHost', 'test.test')
      ->add(ResourceMapper::class, 'register', TRUE, 'resource');

    $container = $container_chain->getMock();
    \Drupal::setContainer($container);

    $referencer = new Referencer($configService, $nodeStorage);
    $data = $this->getData('https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv');
    $metadata = $referencer->reference($data);
    $this->assertEquals('text/csv', $container_chain->getStoredInput('resource')[0]->getMimeType());
  }

  /**
   * Get list of properties.
   */
  private function getPropertyList() {
    return [
      'theme' => 0,
      'keyword' => 0,
      'publisher' => 0,
      'distribution' => 'distribution',
      'contactPoint' => 0,
      '@type' => 0,
      'title' => 0,
      'identifier' => 0,
      'description' => 0,
      'accessLevel' => 0,
      'accrualPeriodicity' => 0,
      'describedBy' => 0,
      'describedByType' => 0,
      'issued' => 0,
      'license' => 0,
      'modified' => 0,
      'references' => 0,
      'spatial' => 0,
      'temporal' => 0,
      'isPartOf' => 0,
    ];
  }
}
