<?php

namespace Drupal\Tests\data_content_type\Unit;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Entity\EntityInterface;
use MockChain\Chain;
use MockChain\Options;
use Drupal\dkan\UrlHostTokenResolver;
use Drupal\data_content_type\DataNodeLifeCycle;
use Drupal\node\Entity\Node;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
class DataNodeLifeCycleTest extends TestCase {

  /**
   *
   */
  public function testNotNode() {
    $this->expectExceptionMessage("We only work with nodes.");

    $entity = (new Chain($this))
      ->add(EntityInterface::class, "blah", NULL)
      ->getMock();

    new DataNodeLifeCycle($entity);
  }

  /**
   *
   */
  public function testNonDataNode() {
    $this->expectExceptionMessage("We only work with data nodes.");

    $node = (new Chain($this))
      ->add(Node::class, "bundle", "blah")
      ->getMock();

    new DataNodeLifeCycle($node);
  }

  /**
   *
   */
  public function testPresaveDistribution() {
    $container = (new Chain($this))
      ->add(Container::class, "get", RequestStack::class)
      ->add(RequestStack::class, "getCurrentRequest", Request::class)
      ->add(Request::class, "getHost", "dkan")
      ->add(Request::class, "getSchemeAndHttpHost", "http://dkan")
      ->getMock();

    \Drupal::setContainer($container);

    $metadata = (object) [
      "data" => (object) [
        "downloadURL" => "http://dkan/some/path/blah",
      ],
    ];

    $options = (new Options())
      ->add('field_json_metadata', (object) ["value" => json_encode($metadata)])
      ->add('field_data_type', (object) ["value" => "distribution"])
      ->index(0);

    $nodeChain = new Chain($this);
    $node = $nodeChain
      ->add(Node::class, "bundle", "data")
      ->add(Node::class, "get", $options)
      ->add(Node::class, "set", NULL, "metadata")
      ->getMock();

    $lifeCycle = new DataNodeLifeCycle($node);
    $lifeCycle->presave();

    $metadata = $nodeChain->getStoredInput("metadata");

    $this->assertTrue((substr_count($metadata[1], UrlHostTokenResolver::TOKEN) > 0));
  }

}
