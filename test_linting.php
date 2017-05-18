<?php

eval("echo");

/**
 * Blah blah.
 */
function my_function(&$context) {
  return $context['hello'];
}

$my_value = arra("hello" => "world");
echo my_function($my_value);
