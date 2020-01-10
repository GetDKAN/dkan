<?php

namespace Drupal\dkan_common\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Annotation for data modifier plugins.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class DataModifier extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * Communicate the plugin's effect on the modified data.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $result;

  /**
   * HTTP code to include in response when modification has occured.
   *
   * @var int
   */
  public $resultCode;

}
