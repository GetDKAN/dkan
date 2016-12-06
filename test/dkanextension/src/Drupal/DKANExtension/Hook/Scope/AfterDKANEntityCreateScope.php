<?php
/**
 * @file
 * Entity scope.
 */
namespace Drupal\DKANExtension\Hook\Scope;

use Behat\Testwork\Hook\Scope\HookScope;

/**
 * Represents an Entity hook scope.
 */
final class AfterDKANEntityCreateScope extends DKANEntityScope {

  /**
   * Return the scope name.
   *
   * @return string
   */
  public function getName() {
    return self::AFTER;
  }

}
