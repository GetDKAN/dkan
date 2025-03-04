<?php

namespace Drupal\Tests\common\Unit\Events;

use Drupal\common\Events\Event;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventTest extends TestCase
{
  public function testDataIntegrityAcrossEventSubscribers() {
    $this->expectExceptionMessage("Invalid event data.");

    $dispatcher = new EventDispatcher();
    $dispatcher->addListener('test_event', function (Event $event) {
      $event->setData(1);
    });

    $container = (new Chain($this))
      ->add(Container::class, 'get', $dispatcher)
      ->getMock();

    \Drupal::setContainer($container);

    $event = new Event('hello');
    $dispatcher->dispatch($event, 'test_event');

    if (!is_string($event->getData())) {
      throw new \Exception("Invalid event data.");
    }
  }

}
