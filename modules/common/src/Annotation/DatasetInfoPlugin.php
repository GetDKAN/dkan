<?php

declare(strict_types=1);

namespace Drupal\common\Annotation;

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
