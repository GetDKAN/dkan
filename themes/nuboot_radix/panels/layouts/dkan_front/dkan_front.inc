<?php

/**
 * @file
 * Plugin definition.
 */

$plugin = array(
  'title' => t('DKAN Front'),
  'category' => t('Custom'),
  'icon' => 'dkan_front.png',
  'theme' => 'panels_dkan_front',
  'css' => 'dkan_front.css',
  'regions' => array(
    'hero-first' => t('Hero First Column'),
    'hero-second' => t('Hero Second Column'),
    'top-full' => t('Top Full Row'),
    'triplet-first' => t('Triplet First Column'),
    'triplet-second' => t('Triplet Second Column'),
    'triplet-third' => t('Triplet Third Column'),
    'bottom-full' => t('Bottom Full Row'),
    'bottom-first' => t('Bottom First Column'),
    'bottom-second' => t('Bottom Second Column'),
  ),
);
