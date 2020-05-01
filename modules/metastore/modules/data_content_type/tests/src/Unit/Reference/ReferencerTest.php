<?php

namespace Drupal\Tests\data_content_type\Unit;

use MockChain\Sequence;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\data_content_type\Reference\Referencer;
use Drupal\node\Entity\Node;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class ReferencerTest extends TestCase {

  /**
   *
   */
  public function testReference() {
    $node = (new Chain($this))
      ->add(Node::class, "uuid", "123456789")
      ->getMock();

    $entityTypeManager = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', EntityStorageInterface::class)
      ->add(EntityStorageInterface::class, 'loadByProperties', [$node])
      ->getMock();

    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['publisher'])
      ->getMock();

    $valueReferencer = new Referencer($configService, $entityTypeManager);
    $referenced = $valueReferencer->reference((object) ['publisher' => (object) ['name' => 'Gerardo', 'company' => 'CivicActions']]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals("123456789", $referenced->publisher);
  }

  /**
   *
   */
  public function testReferenceMultiple() {
    $uuids = (new Sequence())
      ->add("123456789")
      ->add("987654321");

    $node = (new Chain($this))
      ->add(Node::class, "uuid", $uuids)
      ->getMock();

    $entityTypeManager = (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', EntityStorageInterface::class)
      ->add(EntityStorageInterface::class, 'loadByProperties', [$node])
      ->getMock();

    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['keyword'])
      ->getMock();

    $valueReferencer = new Referencer($configService, $entityTypeManager);
    $referenced = $valueReferencer->reference((object) ['keyword' => ['Gerardo', 'CivicActions']]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals("123456789", $referenced->keyword[0]);
    $this->assertEquals("987654321", $referenced->keyword[1]);
  }

}
