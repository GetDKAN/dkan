<?php

declare(strict_types=1);

namespace Drupal\Tests\dkan_datastore\Kernel\Service;

use Drupal\dkan_common\DataResource;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\dkan_datastore\Events\DatastoreImportedEvent;
use Drupal\dkan_datastore\Plugin\QueueWorker\ImportJob;
use Drupal\dkan_datastore\Service\ImportService;
use Drupal\KernelTests\KernelTestBase;
use Procrastinator\Result;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @coversDefaultClass \Drupal\dkan_datastore\Service\ImportService
 *
 * @group dkan
 * @group dkan_datastore
 * @group kernel
 */
class ImportServiceEventsTest extends KernelTestBase implements EventSubscriberInterface {

  protected $strictConfigSchema = FALSE;

  protected static $modules = [
    'dkan_common',
    'dkan_datastore',
    'dkan_metastore',
    'node',
    'user',
    'field',
    'text',
    'system',
  ];

  /**
   * Store the events we receive.
   *
   * @var \Drupal\dkan_datastore\Events\DatastoreEventBase[]
   */
  protected array $events = [];

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents(): array {
    return [ImportService::EVENT_DATASTORE_IMPORTED => 'catchImportEvent'];
  }

  /**
   * Our event handler.
   *
   * @param \Drupal\dkan_common\Events\Event $event
   *   The event.
   */
  public function catchImportEvent(DatastoreImportedEvent $event) {
    $this->events[] = $event;
  }

  /**
   * {@inheritDoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);
    $container->register('testing.datastore_imported_subscriber', self::class)
      ->addTag('event_subscriber');
    $container->set('testing.datastore_imported_subscriber', $this);
  }

  public function testEvents() {
    $this->installEntitySchema('node');
    $this->installConfig(['node']);
    $this->installConfig(['dkan_metastore']);
    // Our result will be DONE.
    $result = new Result();
    $result->setStatus(Result::DONE);

    // Our import job will return our result.
    $import_job = $this->getMockBuilder(ImportJob::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['run'])
      ->getMock();
    $import_job->expects($this->once())
      ->method('run')
      ->willReturn($result);

    // Mock an ImportService object to test. We'll mock a number of methods
    // so that we can isolate the event dispatch.
    $import_service = $this->getMockBuilder(ImportService::class)
      ->setConstructorArgs([
        $this->createStub(DataResource::class),
        $this->container->get('dkan.datastore.import_job_store_factory'),
        $this->container->get('dkan.datastore.database_table_factory'),
        $this->container->get('dkan.datastore.logger_channel'),
        $this->container->get('event_dispatcher'),
        $this->container->get('dkan.metastore.reference_lookup'),
      ])
      ->onlyMethods(['getImporter', 'getResource'])
      ->getMock();
    // getImporter returns the import job and thus the result.
    $import_service->expects($this->once())
      ->method('getImporter')
      ->willReturn($import_job);
    // getResource will return a known data resource.
    $import_service->expects($this->once())
      ->method('getResource')
      ->willReturn(new DataResource('path', 'text/csv', 'local_file'));

    // This test class, being an event subscriber, will load up $this->events
    // with the events we generated.
    $this->assertCount(0, $this->events);
    $import_service->import();
    $this->assertCount(1, $this->events);
    /** @var \Drupal\dkan_datastore\Events\DatastoreImportedEvent $event */
    $event = reset($this->events);
    $this->assertInstanceOf(
      DataResource::class,
      $data_resource = $event->getDataResource()
    );
    $this->assertNotEmpty($data_resource->getIdentifier());
    $this->assertNotEmpty($data_resource->getVersion());
  }

}
