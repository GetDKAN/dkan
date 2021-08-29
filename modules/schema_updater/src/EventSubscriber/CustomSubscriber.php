<?php

namespace Drupal\schema_updater\EventSubscriber;

use Drupal\common\Events\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\common\Storage\AbstractDatabaseTable;

/**
 * Subscriber.
 */
class CustomSubscriber implements EventSubscriberInterface {

  /**
   * Inherited.
   *
   * @codeCoverageIgnore
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[AbstractDatabaseTable::EVENT_TABLE_CREATE][] = ['modifySchema'];
    return $events;
  }

  /**
   * Update the table schema stored in the given event.
   *
   * @param \Drupal\common\Events\Event $event
   *   The event object containing the schema for the table being created.
   */
  public function modifySchema(Event $event) {
    // Fetch the table schema from the event.
    $schema = $event->getData();
    $fields = array_keys($schema['fields']);
    // @TODO Mechanism for getting frictionlessSchema.
    $frictionlessSchema = '{
      "schema": {
        "fields": [
          {
            "name": "lon",
            "type": "number"
          },
          {
            "name": "lat",
            "type": "float"
          },
          {
            "name": "last",
            "type": "integer"
          }
        ]
      }
    }';
    $columnDefinitions = json_decode($frictionlessSchema);
    var_dump($columnDefinitions);
    $columnDefinitions = $columnDefinitions->schema->fields;
    foreach ((array) $columnDefinitions as $column) {
      if (in_array($column->name, $fields)) {
        $type = $this->getDatabaseType($column->type);
        $schema['fields'][$column->name]['type'] = $type ? $type : $schema['fields'][$column->name]['type'];
      }
    }
    // Update schema stored in event.
    $event->setData($schema);
  }

  /**
   * Get correct database field type.
   *
   * Allows mapping from frictionless data types to values accepted by the db.
   *
   * @param string $type
   *   Field type from frictionless schema.
   *
   * @return mixed
   *   Returns field type accepted by the db or FALSE if type is not valid.
   */
  public function getDatabaseType($type) {
    // @TODO Improve the mapping mechanism.
    $map = [
      // Frictionless data type => Database type.
      'string' => 'text',
      'number' => 'float',
      'integer' => 'int',
      'boolean' => 'bool',
      'date' => 'date',
      'time' => 'time',
      'datetime' => 'timestamp',
    ];
    if (in_array($type, array_keys($map))) {
      return $map[$type];
    }
    elseif (in_array($type, array_values($map))) {
      return $type;
    }
    return FALSE;
  }

}
