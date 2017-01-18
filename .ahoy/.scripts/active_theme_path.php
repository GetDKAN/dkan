<?php

$theme = variable_get('theme_default');
echo implode(
  '/',
  array(
    DRUPAL_ROOT,
    drupal_get_path('theme', $theme)
  )
);
