<?php

namespace Drupal\DKANExtension\Hook\Call;

use Drupal\DKANExtension\Hook\Scope\DKANEntityScope;
use Drupal\DrupalExtension\Hook\Call\EntityHook;

/**
 * BeforeNodeCreate hook class.
 */
class AfterDKANEntityCreate extends EntityHook {

  /**
   * Initializes hook.
   */
  public function __construct($filterString, $callable, $description = null) {
    parent::__construct(DKANEntityScope::AFTER, $filterString, $callable, $description);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'AfterDKANEntityCreate';
  }
}
