<?php

namespace Drupal\dkan_common\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Defines an abstract base class for Data modifier plugins.
 */
abstract class DataModifierBase extends PluginBase implements DataModifierInterface {

  /**
   * Translate and render the result annotation.
   *
   * @return string
   *   A message explaining the outcome.
   */
  public function message() : string {
    return $this->getPluginDefinition()['result']->render();
  }

  /**
   * Return the http code annotation.
   *
   * @return int
   *   The http code.
   */
  public function httpCode() : int {
    return (int) $this->getPluginDefinition()['code'];
  }

}
