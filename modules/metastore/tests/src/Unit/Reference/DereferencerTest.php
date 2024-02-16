<?php

namespace Drupal\Tests\metastore\Unit\Reference;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Queue\QueueFactory;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Reference\Dereferencer;
use Drupal\metastore\Service\Uuid5;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;
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
    $metadata = '{"data":{"name":"Gerardo","company":"CivicActions"}}';

    $storageFactory = (new Chain($this))
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      ->add(NodeData::class, 'retrieve', $metadata)
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

    $valueReferencer = new Dereferencer($configService, $storageFactory);
    $referenced = $valueReferencer->dereference((object) ['publisher' => $uuid]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals((object) ['name' => 'Gerardo', 'company' => 'CivicActions'], $referenced->publisher);
  }

  public function testDereferenceDeletedReference() {
    $storageFactory = (new Chain($this))
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      ->add(NodeData::class, 'retrieve', new MissingObjectException("bad"))
      ->getMock();

    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['distribution'])
      ->getMock();

    $uuidService = new Uuid5();
    $uuid = $uuidService->generate('dataset', "some value");

    $valueReferencer = new Dereferencer($configService, $storageFactory);
    $referenced = $valueReferencer->dereference((object) ['distribution' => $uuid]);

    $this->assertEmpty((array) $referenced);
  }

  /**
   *
   */
  public function testDereferenceMultiple() {
    $keyword1 = '{"data":"Gerardo"}';
    $keyword2 = '{"data":"CivicActions"}';

    $keywords = (new Sequence())
      ->add($keyword1)
      ->add($keyword2);

    $storageFactory = (new Chain($this))
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      ->add(NodeData::class, 'retrieve', $keywords)
      ->getMock();

    $uuidService = new Uuid5();

    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['keyword'])
      ->getMock();

    $queueService = (new Chain($this))
      ->add(QueueFactory::class)
      ->getMock();

    $valueReferencer = new Dereferencer($configService, $storageFactory);
    $referenced = $valueReferencer->dereference((object) ['keyword' => ['123456789', '987654321']]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals("Gerardo", $referenced->keyword[0]);
    $this->assertEquals("CivicActions", $referenced->keyword[1]);
  }

}
