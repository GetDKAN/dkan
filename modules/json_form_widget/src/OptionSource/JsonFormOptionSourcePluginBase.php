<?php

declare(strict_types=1);

namespace Drupal\json_form_widget\OptionSource;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for json_form_option_source plugins.
 */
abstract class JsonFormOptionSourcePluginBase extends PluginBase implements JsonFormOptionSourceInterface {

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
