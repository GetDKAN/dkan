<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore\Kernel\Service;

use Drupal\common\DataResource;
use Drupal\common\Events\Event;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\datastore\Service\ImportService;
use Drupal\KernelTests\KernelTestBase;
use Procrastinator\Result;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @covers \Drupal\datastore\Service\ImportService
 * @coversDefaultClass \Drupal\datastore\Service\ImportService
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class ImportServiceEventsTest extends KernelTestBase implements EventSubscriberInterface {

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
    return [ImportService::EVENT_DATASTORE_IMPORTED => 'catchImportEvent'];
  }

  /**
   * Our event handler.
   *
   * @param \Drupal\common\Events\Event $event
   *   The event.
   */
  public function catchImportEvent(Event $event) {
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

  /**
   * @covers ::import
   */
  public function testEvents() {
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
      ->disableOriginalConstructor()
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
    /** @var \Drupal\common\Events\Event $event */
    $event = reset($this->events);
    $this->assertIsArray($data = $event->getData());
    // We don't know what the identifier or version actually will be, so we
    // check that the data has the correct keys.
    $this->assertArrayHasKey('identifier', $data);
    $this->assertArrayHasKey('version', $data);
  }

}
