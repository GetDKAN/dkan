<?php

/**
 * Processor for making searches case-insensitive.
 */
class SearchApiIgnoreCase extends SearchApiAbstractProcessor {

  protected function process(&$value) {
    $value = drupal_strtolower($value);
  }

}
