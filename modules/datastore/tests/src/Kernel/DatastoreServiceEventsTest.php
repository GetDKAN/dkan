<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\datastore\Events\DatastoreDroppedEvent;
use Drupal\datastore\Events\DatastorePreDropEvent;
use Drupal\KernelTests\KernelTestBase;
use Drupal\common\DataResource;
use Drupal\common\Storage\DatabaseTableInterface;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore\Storage\ImportJobStoreFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @coversDefaultClass \Drupal\datastore\DatastoreService
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class DatastoreServiceEventsTest extends KernelTestBase implements EventSubscriberInterface {

  protected $strictConfigSchema = FALSE;

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
    'node',
    'user',
    'field',
    'text',
    'system',
  ];

  /**
   * Store the events we receive.
   *
   * @var array
   */
  protected array $events = [];

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      DatastoreService::EVENT_DATASTORE_PRE_DROP => 'catchPreDropEvent',
      DatastoreService::EVENT_DATASTORE_DROPPED => 'catchDroppedEvent',
    ];
  }

  /**
   * Event handler.
   *
   * @param \Drupal\common\Events\Event $event
   *   The event.
   */
  public function catchPreDropEvent(DatastorePreDropEvent $event) {
    $this->events[DatastoreService::EVENT_DATASTORE_PRE_DROP] = $event;
  }

  /**
   * Event handler.
   *
   * @param \Drupal\common\Events\Event $event
   *   The event.
   */
  public function catchDroppedEvent(DatastoreDroppedEvent $event) {
    $this->events[DatastoreService::EVENT_DATASTORE_DROPPED] = $event;
  }

  /**
   * {@inheritDoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);
    $container->register('testing.datastore_drop_subscriber', self::class)
      ->addTag('event_subscriber');
    $container->set('testing.datastore_drop_subscriber', $this);
  }

  public function testEvents() {
    $this->installEntitySchema('node');
    $this->installConfig(['node']);
    $this->installConfig(['metastore']);
    // Mock a data resource.
    $data_resource = $this->getMockBuilder(DataResource::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getUniqueIdentifier'])
      ->getMock();

    // Mock the resource localizer.
    $resource_localizer = $this->getMockBuilder(ResourceLocalizer::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();
    $resource_localizer->expects($this->once())
      ->method('get')
      ->willReturn($data_resource);

    // Mock the storage.
    $storage = $this->getMockBuilder(DatabaseTable::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['destruct'])
      ->getMock();
    $storage->expects($this->once())
      ->method('destruct');

    // Mock a database table so we don't need one.
    $database_table = $this->getMockBuilder(DatabaseTableInterface::class)
      ->onlyMethods(['remove'])
      ->getMockForAbstractClass();
    $database_table->expects($this->once())
      ->method('remove');

    // Mock a job store factory so we can avoid actually trying to drop an
    // actual table.
    $job_store_factory = $this->getMockBuilder(ImportJobStoreFactory::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getInstance'])
      ->getMock();
    $job_store_factory->expects($this->once())
      ->method('getInstance')
      ->willReturn($database_table);

    // Mock the datastore service so we can isolate the dispatched events.
    $datastore_service = $this->getMockBuilder(DatastoreService::class)
      // We only want to mock a few of the services.
      ->setConstructorArgs([
        $resource_localizer,
        $this->container->get('dkan.datastore.service.factory.import'),
        $this->container->get('queue'),
        $job_store_factory,
        $this->container->get('dkan.datastore.service.resource_processor.dictionary_enforcer'),
        $this->container->get('dkan.metastore.resource_mapper'),
        $this->container->get('event_dispatcher'),
        $this->container->get('dkan.metastore.reference_lookup'),
      ])
      ->onlyMethods(['getStorage'])
      ->getMock();
    $datastore_service->expects($this->once())
      ->method('getStorage')
      ->willReturn($storage);

    // This test class, being an event subscriber, will load up $this->events
    // with the events we generated.
    $this->assertCount(0, $this->events);
    $datastore_service->drop('id', 'ver', FALSE);
    // 2 events happened because of drop().
    $this->assertCount(2, $this->events);

    // Pre-drop happened first.
    $this->assertEquals(
      ['dkan_datastore_pre_drop', 'dkan_datastore_dropped'],
      array_keys($this->events)
    );

    // Assert that all of our events can return a DataResource object. We
    // can't assert against the id or version because our datastore doesn't
    // exist, so the mapper can't find it.
    /** @var \Drupal\datastore\Events\DatastoreEventInterface $event */
    foreach ($this->events as $event) {
      $this->assertInstanceOf(DataResource::class, $event->getDataResource());
    }
  }

}
