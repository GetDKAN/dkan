<?php

namespace Drupal\common\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a DkanApiDocs annotation object.
 *
 * @see \Drupal\common\Plugin\DkanApiDocsPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class DkanApiDocs extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * A brief, human readable, description of the API docs.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}
