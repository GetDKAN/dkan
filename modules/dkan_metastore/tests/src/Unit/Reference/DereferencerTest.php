<?php

namespace Drupal\Tests\metastore\Unit\Reference;

use ColinODell\PsrTestLogger\TestLogger;
use Contracts\FactoryInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
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
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \Drupal\metastore\Reference\Dereferencer
 *
 * @group dkan
 * @group metastore
 * @group unit
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

    (new Chain($this))
      ->add(QueueFactory::class)
      ->getMock();

    $valueReferencer = new Dereferencer($configService, $storageFactory, $this->createStub(LoggerInterface::class));
    $referenced = $valueReferencer->dereference((object) ['publisher' => $uuid]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals((object) ['name' => 'Gerardo', 'company' => 'CivicActions'], $referenced->publisher);
  }

  public function testDereferenceDeletedReference() {
    $storageFactory = (new Chain($this))
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      ->add(NodeData::class, 'retrieve', new MissingObjectException('bad'))
      ->getMock();

    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['distribution'])
      ->getMock();

    $uuidService = new Uuid5();
    $uuid = $uuidService->generate('dataset', 'some value');

    $logger = new TestLogger();

    $valueReferencer = new Dereferencer($configService, $storageFactory, $logger);
    $referenced = $valueReferencer->dereference((object) ['distribution' => $uuid]);
    // Make sure we get the type we expect.
    $this->assertIsObject($referenced);
    // Make sure we get the value we expect.
    $this->assertEmpty((array) $referenced);

    // Assert that the logging occurred.
    $this->assertTrue(
      $logger->hasErrorThatContains('Property @property_id reference @uuid not found')
    );
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

    new Uuid5();

    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['keyword'])
      ->getMock();

    (new Chain($this))
      ->add(QueueFactory::class)
      ->getMock();

    $valueReferencer = new Dereferencer($configService, $storageFactory, $this->createStub(LoggerInterface::class));
    $referenced = $valueReferencer->dereference((object) ['keyword' => ['123456789', '987654321']]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals("Gerardo", $referenced->keyword[0]);
    $this->assertEquals("CivicActions", $referenced->keyword[1]);
  }

  /**
   * @covers ::dereferencePropertyUuid
   */
  public function testDereferencePropertyUuidLogging() {
    $logger = new TestLogger();

    $dereferencer = new Dereferencer(
      $this->getMockForAbstractClass(ConfigFactoryInterface::class),
      $this->getMockForAbstractClass(FactoryInterface::class),
      $logger
    );

    $ref_dereference = new \ReflectionMethod($dereferencer, 'dereferencePropertyUuid');
    $ref_dereference->setAccessible(TRUE);

    $this->assertNull(
      $ref_dereference->invokeArgs($dereferencer, [
        'property id',
        // This method will log if the UUID is not a string or an array, so we
        // pass it a boolean.
        TRUE,
      ])
    );

    $this->assertTrue(
      $logger->hasErrorThatContains('Unexpected data type when dereferencing property_id: @property_id with uuid: @uuid')
    );
  }

}
