<?php

declare(strict_types=1);

namespace Drupal\json_form_widget\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines json_form_option_source annotation object.
 *
 * @Annotation
 */
final class JsonFormOptionSource extends Plugin {

  /**
   * The plugin ID.
   */
  public readonly string $id;

  /**
   * The human-readable name of the plugin.
   *
   * @ingroup plugin_translatable
   */
  public readonly string $title;

  /**
   * The description of the plugin.
   *
   * @ingroup plugin_translatable
   */
  public readonly string $description;

}
