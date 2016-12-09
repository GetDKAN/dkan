<?php

/*
 * This file is part of the Behat MinkExtension.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Drupal\DKANExtension\Listener;

use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Drupal\DKANExtension\ServiceContainer\StoreInterface;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StoresListener implements EventSubscriberInterface
{
  /** @var []StoreInterface $stores*/
  private $stores = array();

  /**
   * Initializes Listener.
   */
  public function __construct() {
  }

  public function setStore($className, StoreInterface $store) {
    $this->stores[$className] = $store;
  }


  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents()
  {
      return array(
          ExerciseCompleted::AFTER => array('flushStores', -10),
          ScenarioTested::AFTER => array('flushStores', -10),
          ExampleTested::AFTER => array('flushStores', -10),
      );
  }

  /**
   * Stops all started Mink sessions.
   */
  public function flushStores()
  {
      foreach($this->stores as $store) {
          $store->flush();
      }
  }
}
