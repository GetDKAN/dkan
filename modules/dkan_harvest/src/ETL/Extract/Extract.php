<?php

namespace Drupal\harvest\ETL\Extract;

/**
 * Abstract base class for harvest extraction.
 */
abstract class Extract implements ExtractInterface {

  /**
   * {@inheritDoc}
   */
  public function run(): array {
    $items = $this->getItems();

    if (empty($items)) {
      throw new \Exception("No Items were extracted.");
    }

    $copy = array_values($items);
    if (!is_object($copy[0])) {
      $item = json_encode($copy[0]);
      throw new \Exception("The items extracted are not php objects: {$item}");
    }

    return $items;
  }

  /**
   * Get the items to be harvested.
   *
   * @return array
   *   The items to be harvested.
   */
  abstract protected function getItems();

}
