<?php

/**
 * @file
 * Plugin definition.
 */

$plugin = array(
  'title' => t('Custom Full Width'),
  'category' => t('Custom'),
  'icon' => 'full_width.png',
  'theme' => 'panels_full_width',
  'css' => 'full_width.css',
  'regions' => array(
    'top-first' => t('Top First Column'),
    'top-second' => t('Top Second Column'),
    'middle' => t('Middle Row'),
    'bottom-first' => t('Bottom First Column'),
    'bottom-second' => t('Bottom Second Column'),
    'bottom-third' => t('Bottom Third Column'),
    'footer' => t('Footer'),
  ),
);
