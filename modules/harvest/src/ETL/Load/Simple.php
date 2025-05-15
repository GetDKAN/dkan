<?php

namespace Drupal\harvest\ETL\Load;

/**
 * The most basic implementation of the harvest Load class.
 */
class Simple extends Load {

  /**
   * {@inheritdoc}
   */
  protected function saveItem($item) {
    $id = $item->identifier;
    if (!isset($item->accessLevel)) {
      throw new \Exception("Access level is required");
    }
    $this->itemStorage->store(json_encode($item), $id);
  }

  /**
   * Remove a harvest item from storage.
   *
   * @param string $id
   *   The id of the item to remove.
   */
  public function removeItem(string $id): void {
    $this->itemStorage->remove($id);
  }

}
