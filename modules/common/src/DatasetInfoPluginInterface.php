<?php

declare(strict_types=1);

namespace Drupal\common;

/**
 * Interface for dataset_info plugins.
 */
interface DatasetInfoPluginInterface {

  /**
   * Returns the translated plugin label.
   */
  public function label(): string;

  /**
   * Add dataset info.
   *
   * Return an array that follows the structure of the existing dataset info
   * array, but includes only new keys. Any existing keys will be ignored.
   *
   * @param array $info
   *   The original dataset info.
   *
   * @return array
   *   Additional info array to be combined with the existing info.
   */
  public function addDatasetInfo(array $info): array;

}
