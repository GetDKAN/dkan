<?php

/**
 * @file
 * Implementation of OpenLayers behavior.
 */

/**
 * Map Form Values Behavior
 */
class openlayers_behavior_geofield extends openlayers_behavior {

  /**
   * Provide initial values for options.
   */
  function options_init() {
    return array(
      'feature_types' => array(),
      'allow_edit' => 1,
    );
  }

  function options_form($defaults = array()) {
    $features = array(
      'point' => t('Point'),
      'path' => t('Path'),
      'polygon' => t('Polygon'),
    );

    return array(
      'feature_types' => array(
        '#title' => t('Available Features'),
        '#type' => 'checkboxes',
        '#options' => $features,
        '#description' => t('Select what features are available to draw.'),
        '#default_value' => isset($defaults['feature_types']) ? $defaults['feature_types'] : array(),
      ),
      'allow_edit' => array(
        '#title' => t('Allow shape modification'),
        '#type' => 'checkbox',
        '#description' => t('Can you edit and delete shapes.'),
        '#default_value' => isset($defaults['allow_edit']) ? $defaults['allow_edit'] : 1,
      ),
    );
  }

  /**
   * Render.
   */
  function render(&$map) {
    $geopath = drupal_get_path('module', 'geofield');
    drupal_add_js($geopath . '/includes/behaviors/js/openlayers_behavior_geofield.js');
    return $this->options;
  }
}
