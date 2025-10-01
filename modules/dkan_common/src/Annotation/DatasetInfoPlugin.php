<?php

declare(strict_types=1);

namespace Drupal\dkan_common\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines dataset_info annotation object.
 *
 * @Annotation
 */
final class DatasetInfoPlugin extends Plugin {

  /**
   * The plugin ID.
   */
  public readonly string $id;

}
