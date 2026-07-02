<?php

namespace Drupal\Tests\dkan_metastore\Unit\Reference;

use ColinODell\PsrTestLogger\TestLogger;
use Contracts\FactoryInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\dkan_common\DataResource;
use Drupal\dkan_metastore\Exception\MissingObjectException;
use Drupal\dkan_metastore\Reference\Dereferencer;
use Drupal\dkan_metastore\ResourceMapper;
use Drupal\dkan_metastore\Service\Uuid5;
use Drupal\dkan_metastore\Storage\DataFactory;
use Drupal\dkan_metastore\Storage\NodeData;
use MockChain\Chain;
use MockChain\Options;
use MockChain\Sequence;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;

/**
 * @coversDefaultClass \Drupal\dkan_metastore\Reference\Dereferencer
 *
 * @group dkan
 * @group metastore
 * @group unit
 */
class DereferencerTest extends TestCase {

  /**
   * Mock config factory.
   */
  protected MockObject $configMock;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();
    $this->configMock = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', FALSE)
      ->getMock();
  }

  /**
   * @covers ::dereference
   * @covers ::dereferenceProperty
   * @covers ::dereferencePropertyUuid
   * @covers ::dereferenceSingle
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

    $resourceMapper = $this->createStub(ResourceMapper::class);

    (new Chain($this))
      ->add(QueueFactory::class)
      ->getMock();

    $valueReferencer = new Dereferencer(
      $configService,
      $storageFactory,
      $resourceMapper,
      $this->createStub(LoggerInterface::class)
    );
    $dereferenced = $valueReferencer->dereference((object) ['publisher' => $uuid]);

    $this->assertTrue(is_object($dereferenced));
    $this->assertEquals((object) ['name' => 'Gerardo', 'company' => 'CivicActions'], $dereferenced->publisher);
  }

  /**
   * @covers ::dereference
   * @covers ::dereferenceProperty
   * @covers ::dereferencePropertyUuid
   * @covers ::dereferenceSingle
   */
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

    $valueReferencer = new Dereferencer(
      $configService,
      $storageFactory,
      $this->createStub(ResourceMapper::class),
      $logger
    );
    $dereferenced = $valueReferencer->dereference((object) ['distribution' => $uuid]);
    // Make sure we get the type we expect.
    $this->assertIsObject($dereferenced);
    // Make sure we get the value we expect.
    $this->assertEmpty((array) $dereferenced);

    // Assert that the logging occurred.
    $this->assertTrue(
      $logger->hasErrorThatContains('Property @property_id reference @uuid not found')
    );
  }

  /**
   * @covers ::dereference
   * @covers ::dereferenceMultiple
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

    $valueReferencer = new Dereferencer(
      $configService,
      $storageFactory,
      $this->createStub(ResourceMapper::class),
      $this->createStub(LoggerInterface::class)
    );
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
      $this->createStub(ResourceMapper::class),
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

  /**
   * Test dereferencing a distribution resource with no download URL.
   *
   * @covers ::dereferenceResources
   * @covers ::dereferenceDistributionResource
   */
  public function testDereferenceResourcesNoDownloadUrl() {
    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', FALSE)
      ->getMock();

    $dereferencer = new Dereferencer(
      $configService,
      $this->getMockForAbstractClass(FactoryInterface::class),
      $this->createStub(ResourceMapper::class),
      $this->createStub(LoggerInterface::class)
    );

    $data = (object) [
      'distribution' => [
        (object) [
          'title' => 'No download URL',
          'mediaType' => 'text/csv',
        ],
      ],
    ];

    $dereferencer->dereferenceResources($data);

    $this->assertEquals('No download URL', $data->distribution[0]->title);
    $this->assertFalse(property_exists($data->distribution[0], 'downloadURL'));
    $this->assertFalse(property_exists($data->distribution[0], '%Ref:downloadURL'));
  }


  /**
   * Test dereferencing a download URL that is a reference.
   *
   * @covers ::dereferenceResources
   * @covers ::dereferenceDistributionResource
   */
  public function testDereferenceResourcesWithIdentifier() {
    $this->setContainerForUrlResolver();

    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', FALSE)
      ->getMock();

    $resourceMapper = $this->createMock(ResourceMapper::class);
    $resourceMapper
      ->method('get')
      ->willReturn(new DataResource(
        'http://example.com/test.csv',
        'text/csv')
      );

    $dereferencer = new Dereferencer(
      $configService,
      $this->getMockForAbstractClass(FactoryInterface::class),
      $resourceMapper,
      $this->createStub(LoggerInterface::class)
    );

    $data = (object) [
      'distribution' => [
        (object) [
          'downloadURL' => '5d41402abc4b2a76b9719d911017c592__1783014536__source',
          'mediaType' => 'text/csv',
        ],
      ],
    ];

    $dereferencer->dereferenceResources($data);

    $this->assertEquals('http://example.com/test.csv', $data->distribution[0]->downloadURL);
    $this->assertTrue(property_exists($data->distribution[0], '%Ref:downloadURL'));
  }

  /**
   * Test dereferencing a distribution resource that isn't actually referenced.
   *
   * Just incase there is a regular URL in the downloadURL field, we want to
   * make sure it doesn't get dereferenced or modified.
   *
   * @covers ::dereferenceResources
   * @covers ::dereferenceDistributionResource
   */
  public function testDereferenceResourcesWithValidDownloadUrl() {
    $this->setContainerForUrlResolver();

    $configService = (new Chain($this))
      ->add(ConfigFactory::class, 'get', ImmutableConfig::class)
      ->add(ImmutableConfig::class, 'get', FALSE)
      ->getMock();

    $dereferencer = new Dereferencer(
      $configService,
      $this->getMockForAbstractClass(FactoryInterface::class),
      $this->createStub(ResourceMapper::class),
      $this->createStub(LoggerInterface::class)
    );

    $data = (object) [
      'distribution' => [
        (object) [
          'downloadURL' => 'http://example.com/test.csv',
          'mediaType' => 'text/csv',
        ],
      ],
    ];

    $dereferencer->dereferenceResources($data);

    $this->assertEquals('http://example.com/test.csv', $data->distribution[0]->downloadURL);
    $this->assertFalse(property_exists($data->distribution[0], '%Ref:downloadURL'));
  }

  /**
   * Set the container to support the UrlHostTokenResolver.
   *
   * Note this would not be necessary if we used dependency injection properly
   * for the UrlHostTokenResolver.
   */
  private function setContainerForUrlResolver() {
    $container = (new Chain($this))
      ->add(Container::class, 'get', (new Options())
        ->add('stream_wrapper_manager', StreamWrapperManager::class)
        ->index(0)
      )
      ->add(StreamWrapperManager::class, 'getViaUri', StreamWrapperInterface::class)
      ->add(StreamWrapperInterface::class, 'getExternalUrl', 'http://example.com/sites/default/files')
      ->getMock();

    \Drupal::setContainer($container);
  }

}
