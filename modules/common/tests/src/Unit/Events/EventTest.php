<?php

namespace Drupal\Tests\common\Unit\Events;

use Drupal\common\Events\Event;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventTest extends TestCase
{
  public function testDataIntegrityAcrossEventSubscribers() {
    $this->expectExceptionMessage("Invalid event data.");

    $dispatcher = new EventDispatcher();
    $dispatcher->addListener('test_event', function(Event $event) {
      $event->setData(1);
    });

    $container = (new Chain($this))
      ->add(Container::class, 'get', $dispatcher)
      ->getMock();

    \Drupal::setContainer($container);

    $this->dispatchEvent('test_event', 'hello', function($data) {
      return is_string($data);
    });
  }

  /**
   * Mimics the original dispatchEvent() method from the deprecated trait.
   *
   * @param string   $eventName
   *   The event name.
   * @param mixed    $data
   *   The initial event data.
   * @param callable $validator
   *   A callback that validates the event data.
   *
   * @throws \Exception
   *   If the validator returns FALSE.
   */
  private function dispatchEvent($eventName, $data, callable $validator) {
    /** @var EventDispatcherInterface $dispatcher */
    $dispatcher = \Drupal::getContainer()->get('event_dispatcher');
    $event = new Event($data);
    $dispatcher->dispatch($event, $eventName);

    if (!$validator($event->getData())) {
      throw new \Exception("Invalid event data.");
    }
  }
}
