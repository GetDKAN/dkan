<?php

namespace Drupal\harvest\ETL\Extract;

abstract class Extract implements IExtract {

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

  abstract protected function getItems();

}
