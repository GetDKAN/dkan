<?php

namespace Drupal\common;

use Drupal\common\Events\Event;

/**
 * Trait EventDispatcherTrait.
 *
 * @package Drupal\common
 */
trait EventDispatcherTrait {

  /**
   * Dispatch and event and give back any modified data from the listeners.
   *
   * @param mixed $eventName
   *   The name of the event.
   * @param mixed $data
   *   The data that will be given to the listeners/subscribers.
   * @param mixed $validator
   *   A callable used to validate that the data in the event as it is modified keeps its integrity.
   *
   * @return mixed
   *   The data returned by the listeners/subscribers.
   *
   * @throws \Exception
   *   If any of the subscribers registered and Exception it is thrown.
   */
  private function dispatchEvent($eventName, $data, $validator = NULL) {
    /* @var ContainerAwareEventDispatcher $dispatcher */
    $dispatcher = \Drupal::service('event_dispatcher');

    /* @var \Drupal\common\Events\Event $event */
    if ($event = $dispatcher->dispatch($eventName, new Event($data, $validator))) {
      if ($e = $event->getException()) {
        throw $e;
      }

      $data = $event->getData();
    }

    return $data;
  }

}
