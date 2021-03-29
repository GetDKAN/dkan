<?php

namespace Drupal\Tests\metastore\Unit\Reference;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Queue\QueueFactory;
use Drupal\metastore\Reference\Dereferencer;
use Drupal\metastore\Service\Uuid5;
use Drupal\node\NodeStorageInterface;
use MockChain\Chain;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class DereferencerTest extends TestCase {

  /**
   *
   */
  public function testDereference() {
    $node = (object) [
      "field_json_metadata" => (object) [
        "value" => json_encode((object) ["data" => (object) ['name' => 'Gerardo', 'company' => 'CivicActions']]),
      ],
    ];

    $nodeStorage = (new Chain($this))
      ->add(NodeStorageInterface::class, 'loadByProperties', [$node])
      ->getMock();

    $uuidService = new Uuid5();
    $uuid = $uuidService->generate('dataset', "some value");

    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['publisher'])
      ->getMock();

    $queueService = (new Chain($this))
      ->add(QueueFactory::class)
      ->getMock();

    $valueReferencer = new Dereferencer($configService, $nodeStorage);
    $referenced = $valueReferencer->dereference((object) ['publisher' => $uuid]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals((object) ['name' => 'Gerardo', 'company' => 'CivicActions'], $referenced->publisher);
  }

  /**
   *
   */
  public function testDereferenceMultiple() {
    $node1 = (object) [
      "field_json_metadata" => (object) [
        "value" => json_encode((object) ["data" => "Gerardo"]),
      ],
    ];

    $node2 = (object) [
      "field_json_metadata" => (object) [
        "value" => json_encode((object) ["data" => "CivicActions"]),
      ],
    ];

    $nodes = (new Sequence())
      ->add([$node1])
      ->add([$node2]);

    $nodeStorage = (new Chain($this))
      ->add(NodeStorageInterface::class, 'loadByProperties', $nodes)
      ->getMock();

    $uuidService = new Uuid5();

    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['keyword'])
      ->getMock();

    $queueService = (new Chain($this))
      ->add(QueueFactory::class)
      ->getMock();

    $valueReferencer = new Dereferencer($configService, $nodeStorage);
    $referenced = $valueReferencer->dereference((object) ['keyword' => ['123456789', '987654321']]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals("Gerardo", $referenced->keyword[0]);
    $this->assertEquals("CivicActions", $referenced->keyword[1]);
  }

}
