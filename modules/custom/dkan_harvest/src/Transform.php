<?php

namespace Drupal\dkan_harvest;

abstract class Transform {

  private $config;

  private $drupal8 = FALSE;

  function __construct($config = NULL) {
    $this->config = $config;
  }

  function run(&$items) {
    $this->hook(strtolower(get_class($this)), $items);
  }

  function hook($name, &$items) {
    if ($this->drupal8) {
			$module_handler = \Drupal::moduleHandler();
			$module_handler
				->invokeAll('hooks_dkan_harvest_transform_' . $name, $items);
		}
  }

}
