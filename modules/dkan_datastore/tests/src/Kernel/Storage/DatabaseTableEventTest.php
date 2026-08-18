<?php

namespace Drupal\Tests\dkan_datastore\Kernel\Storage;

use Drupal\KernelTests\KernelTestBase;
use Drupal\dkan_common\DataResource;
use Drupal\dkan_common\Events\Event;
use Drupal\dkan_common\Storage\AbstractDatabaseTable;
use Drupal\dkan_datastore\Events\DatastoreTableCreateEvent;
use Drupal\dkan_datastore\Storage\DatabaseTable;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @covers \Drupal\dkan_datastore\Storage\DatabaseTable
 * @covers \Drupal\dkan_datastore\Events\DatastoreTableCreateEvent
 *
 * @runTestsInSeparateProcesses
 *
 * @group dkan
 * @group dkan_datastore
 * @group kernel
 */
class DatabaseTableEventTest extends KernelTestBase implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'dkan_common',
    'dkan_datastore',
    'dkan_metastore',
  ];

  protected function setUp(): void {
    parent::setUp();
    // Add this object as an event subscriber.
    $this->container
      ->get('event_dispatcher')
      ->addSubscriber($this);
  }

  /**
   * Our subscriber methods, so we can change the schemas in a testable way.
   */
  public static function getSubscribedEvents(): array {
    return [
      DatabaseTable::EVENT_DATABASE_TABLE_CREATE => 'onDatabaseTableCreate',
      AbstractDatabaseTable::EVENT_TABLE_CREATE => 'onTableCreate',
    ];
  }

  /**
   * Ensure that we have a round-trip on EVENT_DATABASE_TABLE_CREATE.
   */
  public function testDatabaseTableEvent() {
    // Mock some stuff.
    $data_resource = new DataResource('path', 'text/csv');

    // Get a real DatabaseTable instance.
    /** @var DatabaseTableFactory $factory */
    $factory = $this->container->get('dkan.datastore.database_table_factory');
    /** @var DatabaseTable $data_table */
    $data_table = $factory->getInstance('id', ['resource' => $data_resource]);

    // Sneak our way into the tableCreate() method.
    $ref_table_create = new \ReflectionMethod($data_table, 'tableCreate');

    $id_schema = [
      'primary key' => ['id'],
      'fields' => [
        'id' => [
          'type' => 'text',
          'not null' => TRUE,
          'mysql_type' => 'varchar(38)',
          'description' => 'ID',
        ],
      ],
    ];

    $ref_table_create->invokeArgs($data_table, [
      'table_name',
      $id_schema
    ]);

    $schema = $data_table->getSchema();

    // DatabaseTable added this primary key.
    $this->assertSame(['record_number'], $schema['primary key']);
    $this->assertSame([
      'type' => 'serial',
      'unsigned' => TRUE,
      'not null' => TRUE,
    ], $schema['fields']['record_number']);

    // Our ID field made the round-trip.
    $this->assertSame(
      $id_schema['fields']['id'],
      $schema['fields']['id'] ?? []
    );

    // Both subscribers made changes.
    $this->assertArrayHasKey('on_database_table_create', $schema['fields'] ?? []);
    $this->assertArrayHasKey('on_table_create', $schema['fields'] ?? []);

    // EVENT_DATABASE_TABLE_CREATE happened first.
    $this->assertArrayHasKey('on_database_table_create_was_first', $schema['fields'] ?? []);
    $this->assertArrayNotHasKey('on_table_create_was_first', $schema['fields'] ?? []);

    // Data resource made it through.
    $this->assertEquals(
      'd6fe1d0be6347b8ef2427fa629c04485',
      $schema['fields']['data_resource']['description'] ?? NULL
    );
  }

  /**
   * Respond to EVENT_DATABASE_TABLE_CREATE.
   */
  public function onDatabaseTableCreate(DatastoreTableCreateEvent $event) {
    $schema = $event->getSchema();
    $schema['fields']['on_database_table_create'] = ['type' => 'text'];
    if ($schema['fields']['on_table_create'] ?? FALSE) {
      $schema['fields']['on_table_create_was_first'] = ['type' => 'text'];
    }
    if ($data_resource = $event->getDataResource()) {
      $schema['fields']['data_resource'] = [
        'type' => 'text',
        'description' => $data_resource->getIdentifier(),
      ];
    }
    $event->setSchema($schema);
  }

  /**
   * Respond to EVENT_TABLE_CREATE.
   */
  public function onTableCreate(Event $event) {
    $schema = $event->getData();
    $schema['fields']['on_table_create'] = ['type' => 'text'];
    if ($schema['fields']['on_database_table_create'] ?? FALSE) {
      $schema['fields']['on_database_table_create_was_first'] = ['type' => 'text'];
    }
    $event->setData($schema);
  }

}
