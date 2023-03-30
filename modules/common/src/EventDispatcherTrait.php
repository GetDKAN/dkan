<?php

namespace Drupal\common;

use Drupal\common\Events\Event;

/**
 * Event dispatcher trait.
 *
 * @todo Refactor as service.
 *
 * @codeCoverageIgnore
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
   *   A callable used to validate that the data in the event as it is modified
   *   keeps its integrity.
   *
   * @return mixed
   *   The data returned by the listeners/subscribers.
   *
   * @throws \Exception
   *   If any of the subscribers registered and Exception it is thrown.
   */
  private function dispatchEvent($eventName, $data, $validator = NULL) {
    if ($this->useLegacyDispatcher()) {
      $data = $this->legacyDispatchEvent($eventName, $data, $validator);
      return $data;
    }
    $dispatcher = \Drupal::service('event_dispatcher');

    if ($event = $dispatcher->dispatch(new Event($data, $validator), $eventName)) {
      if ($e = $event->getException()) {
        throw $e;
      }

      $data = $event->getData();
    }

    return $data;
  }

  /**
   * If we're on Drupal 8.9 or 9.0, we have to use the old dispatch sig.
   *
   * @see https://www.drupal.org/node/3154407
   *
   * @return bool
   *   True if the newer Symfony event system is available.
   */
  private function useLegacyDispatcher() {
    return !class_exists('\Symfony\Contracts\EventDispatcher\Event');
  }

  /**
   * Legacy version of the dispatchEvent() method.
   *
   * @param mixed $eventName
   *   The name of the event.
   * @param mixed $data
   *   The data that will be given to the listeners/subscribers.
   * @param mixed $validator
   *   A callable used to validate that the data in the event as it is modified
   *   keeps its integrity.
   *
   * @return mixed
   *   The data returned by the listeners/subscribers.
   *
   * @throws \Exception
   *   If any of the subscribers registered and Exception it is thrown.
   */
  private function legacyDispatchEvent($eventName, $data, $validator = NULL) {
    $dispatcher = \Drupal::service('event_dispatcher');

    if ($event = $dispatcher->dispatch($eventName, new Event($data, $validator))) {
      if ($e = $event->getException()) {
        throw $e;
      }

      $data = $event->getData();
    }

    return $data;
  }

}
