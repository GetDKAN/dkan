<?php

namespace Drupal\dkan_harvest;

abstract class Transform {

  protected $config;

  protected $drupal8 = FALSE;

  protected $log;

  function __construct($config = NULL, $log) {
    $this->config = $config;
    $this->log = $log;
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
