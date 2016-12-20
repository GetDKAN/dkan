<?php

namespace Drupal\DKANExtension\Context;

use Drupal\DKANExtension\ServiceContainer\EntityStore;
use Drupal\DKANExtension\ServiceContainer\PageStore;
use Drupal\DrupalExtension\Context\DrupalAwareInterface;

interface DKANAwareInterface extends DrupalAwareInterface {

  /**
   * Sets EntityStore instance.
   * @param $store
   * @return
   */
  public function setEntityStore(EntityStore $store);

  /**
   * Sets Page Store instance.
   * @param $store
   * @return
   */
  public function setPageStore(PageStore $store);

}
