<?php
/**
 * @file
 * Prints the site name.
 */
include './assets/drush/aliases.local.php';

$name = explode('.', $aliases['local']['#name']);

if (empty($name)) {
  $name = 'default';
}
else {
  $name = $name[0];
}

print $name;

