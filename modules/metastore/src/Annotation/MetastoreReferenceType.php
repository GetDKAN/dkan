<?php

namespace Drupal\metastore\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a DKAN ReferenceDefinition annotation object.
 *
 * @see \Drupal\metastore\Plugin\ReferenceDefinitionManager
 * @see plugin_api
 *
 * @Annotation
 */
class MetastoreReferenceType extends Plugin {

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
