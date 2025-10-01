<?php

declare(strict_types=1);

namespace Drupal\common;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for dataset_info plugins.
 */
abstract class DatasetInfoPluginBase extends PluginBase implements DatasetInfoPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
