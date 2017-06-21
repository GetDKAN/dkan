<?php

/**
 * @file
 * File doc is required.
 */

eval("echo");

/**
 * Blah blah.
 *
 * @param array $context
 *   Blah blah.
 */
function my_function($context) {
  return $context['hello'];
}

$my_value = array("hello" => "world");
echo my_function($my_value);
