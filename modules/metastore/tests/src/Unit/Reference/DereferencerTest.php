<?php

namespace Drupal\Tests\metastore\Unit\Reference;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\metastore\Reference\Dereferencer;
use Drupal\metastore\Service;
use Drupal\metastore\Service\Uuid5;
use Drupal\metastore\ValidMetadataFactory;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;
use Drupal\Tests\metastore\Unit\ServiceTest;
use MockChain\Chain;
use MockChain\Options;
use MockChain\Sequence;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class DereferencerTest extends TestCase {

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->validMetadataFactory = ServiceTest::getValidMetadataFactory($this);
  }

  /**
   *
   */
  public function testDereference() {
    $metadata = '{"name":"Gerardo","company":"CivicActions"}';

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

    $publisherData = $this->validMetadataFactory->get('publisher', json_encode([
      'name' => 'Gerardo',
      'company' => 'CivicActions',
    ]));
    $wrappedPublisher = $this->validMetadataFactory->get('publisher', json_encode([
      'identifier' => $uuid,
      'data' => [
        'name' => 'Gerardo',
        'company' => 'CivicActions',
      ],
    ]));
    $service = (new Chain($this))
      ->add(Service::class, "getValidMetadataFactory", ValidMetadataFactory::class)
      ->add(ValidMetadataFactory::class, "get", $publisherData)
      ->add(Service::class, "wrapMetadata", $wrappedPublisher)
      ->getMock();

    $valueReferencer = new Dereferencer($configService, $storageFactory, $service);
    $referenced = $valueReferencer->dereference((object) ['publisher' => $uuid]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals((object) ['name' => 'Gerardo', 'company' => 'CivicActions'], $referenced->publisher);
  }

  /**
   *
   */
  public function testDereferenceMultiple() {
    $keyword1 = json_encode('Gerardo');
    $keyword2 = json_encode('CivicActions');

    $keywords = (new Sequence())
      ->add($keyword1)
      ->add($keyword2);

    $storageFactory = (new Chain($this))
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      ->add(NodeData::class, 'retrieve', $keywords)
      ->getMock();

    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', ['keyword'])
      ->getMock();

    $keywordData1 = $this->validMetadataFactory->get('keyword', json_encode("Gerardo"));
    $keywordData2 = $this->validMetadataFactory->get('keyword', json_encode("CivicActions"));
    $getOptions = (new Options())
      ->add(['keyword', json_encode("Gerardo")], $keywordData1)
      ->add(['keyword', json_encode("CivicActions")], $keywordData2);

    $wrappedKeyword1 = $this->validMetadataFactory->get('keyword', json_encode([
      'identifier' => '123456789',
      'data' => 'Gerardo',
    ]));
    $wrappedKeyword2 = $this->validMetadataFactory->get('keyword', json_encode([
      'identifier' => '987654321',
      'data' => 'CivicActions',
    ]));
    $wrapMetadataOptions = (new Options())
      ->add(['123456789', $keywordData1], $wrappedKeyword1)
      ->add(['987654321', $keywordData2], $wrappedKeyword2);

    $service = (new Chain($this))
      ->add(Service::class, "getValidMetadataFactory", ValidMetadataFactory::class)
      ->add(ValidMetadataFactory::class, "get", $getOptions)
      ->add(Service::class, "wrapMetadata", $wrapMetadataOptions)
      ->getMock();

    $valueReferencer = new Dereferencer($configService, $storageFactory, $service);
    $referenced = $valueReferencer->dereference((object) ['keyword' => ['123456789', '987654321']]);

    $this->assertTrue(is_object($referenced));
    $this->assertEquals("Gerardo", $referenced->keyword[0]);
    $this->assertEquals("CivicActions", $referenced->keyword[1]);
  }

}
