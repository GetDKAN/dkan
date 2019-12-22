<?php

namespace Drupal\Tests\dkan_data\Unit;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Config\Schema\Sequence;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Queue\QueueFactory;
use Drupal\dkan_data\Service\Uuid5;
use Drupal\dkan_data\ValueReferencer;
use Drupal\node\Entity\Node;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

class ValueReferencerTest extends TestCase
{
  public function testReference() {
    $node = (new Chain($this))
      ->add(Node::class, "uuid", "123456789")
      ->getMock();

    $entityTypeManager = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', EntityStorageInterface::class)
      ->add(EntityStorageInterface::class, 'loadByProperties', [$node])
      ->getMock();

    $uuidService = new Uuid5();

    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['publisher'])
      ->getMock();

    $queueService = (new Chain($this))
      ->add(QueueFactory::class, 'blah', NULL)
      ->getMock();

    $valueReferencer = new ValueReferencer($entityTypeManager, $uuidService, $configService, $queueService);
    $referenced = $valueReferencer->reference((object) ['publisher' => (object) ['name' => 'Gerardo', 'company' => 'CivicActions']]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals("123456789", $referenced->publisher);
  }

  public function testReferenceMultiple() {
    $uuids = (new \MockChain\Sequence())
      ->add("123456789")
      ->add("987654321");

    $node = (new Chain($this))
      ->add(Node::class, "uuid", $uuids)
      ->getMock();

    $entityTypeManager = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', EntityStorageInterface::class)
      ->add(EntityStorageInterface::class, 'loadByProperties', [$node])
      ->getMock();

    $uuidService = new Uuid5();

    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['keyword'])
      ->getMock();

    $queueService = (new Chain($this))
      ->add(QueueFactory::class, 'blah', NULL)
      ->getMock();

    $valueReferencer = new ValueReferencer($entityTypeManager, $uuidService, $configService, $queueService);
    $referenced = $valueReferencer->reference((object) ['keyword' => ['Gerardo', 'CivicActions']]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals("123456789", $referenced->keyword[0]);
    $this->assertEquals("987654321", $referenced->keyword[1]);
  }

  public function testDereference() {
    $node = (object) [
      "field_json_metadata" => (object) [
        "value" => json_encode((object) ["data" => (object) ['name' => 'Gerardo', 'company' => 'CivicActions']])
      ]
    ];

    $entityTypeManager = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', EntityStorageInterface::class)
      ->add(EntityStorageInterface::class, 'loadByProperties', [$node])
      ->getMock();

    $uuidService = new Uuid5();
    $uuid = $uuidService->generate('dataset', "some value");

    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['publisher'])
      ->getMock();

    $queueService = (new Chain($this))
      ->add(QueueFactory::class, 'blah', NULL)
      ->getMock();

    $valueReferencer = new ValueReferencer($entityTypeManager, $uuidService, $configService, $queueService);
    $referenced = $valueReferencer->dereference((object) ['publisher' => $uuid]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals((object) ['name' => 'Gerardo', 'company' => 'CivicActions'], $referenced->publisher);
  }

  public function testDereferenceMultiple() {
    $node1 = (object) [
      "field_json_metadata" => (object) [
        "value" => json_encode((object) ["data" => "Gerardo"])
      ]
    ];

    $node2 = (object) [
      "field_json_metadata" => (object) [
        "value" => json_encode((object) ["data" => "CivicActions"])
      ]
    ];

    $nodes = (new \MockChain\Sequence())
      ->add([$node1])
      ->add([$node2]);

    $entityTypeManager = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', EntityStorageInterface::class)
      ->add(EntityStorageInterface::class, 'loadByProperties', $nodes)
      ->getMock();

    $uuidService = new Uuid5();

    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['keyword'])
      ->getMock();

    $queueService = (new Chain($this))
      ->add(QueueFactory::class, 'blah', NULL)
      ->getMock();

    $valueReferencer = new ValueReferencer($entityTypeManager, $uuidService, $configService, $queueService);
    $referenced = $valueReferencer->dereference((object) ['keyword' => ['123456789', '987654321']]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals("Gerardo", $referenced->keyword[0]);
    $this->assertEquals("CivicActions", $referenced->keyword[1]);
  }
}
