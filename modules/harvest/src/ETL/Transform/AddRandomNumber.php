<?php

namespace Drupal\harvest\ETL\Transform;

/**
 * Transform to add a random number.
 */
class AddRandomNumber extends Transform {

  /**
   * {@inheritdoc}
   */
  public function run($item): object {
    $copy = clone $item;
    $copy->random_number = random_int(0, 100000);
    return $item;
  }

}
