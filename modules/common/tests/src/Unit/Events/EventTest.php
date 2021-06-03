<?php

namespace Drupal\Tests\common\Unit\Events;

use Drupal\common\EventDispatcherTrait;
use Drupal\common\Events\Event;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class EventTest extends TestCase
{
  use EventDispatcherTrait;

  public function testDataIntegrityAcrossEventSubscribers() {
    $this->expectExceptionMessage("Invalid event data.");
    $containerx = (new Chain($this))
      ->add(Container::class)
      ->getMock();

    $dispatcher = new ContainerAwareEventDispatcher($containerx);
    $dispatcher->addListener('test_event', function(Event $event) {
      $event->setData(1);
    });

    $container = (new Chain($this))
      ->add(Container::class, 'get', $dispatcher)
      ->getMock();

    \Drupal::setContainer($container);

    $result = $this->dispatchEvent('test_event', 'hello', function($data) {
      return is_string($data);
    });
  }

}
