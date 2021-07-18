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
    $frictionlessSchema = '{
      "schema": {
        "fields": [
          {
            "name": "lon",
            "type": "float"
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
        $schema['fields'][$column->name]['type'] = $column->type;
      }
    }
    // Update schema stored in event.
    $event->setData($schema);
  }

}
