<?php

/**
 * @file
 * This file holds style plugin for Geofield Maps
 *
 * @ingroup geofield_map
 */

/**
 * @class
 * Extension of the Views Plugin Syle for Geofield Map
 */
class geofield_map_plugin_style_map extends views_plugin_style {
  /**
   * Set default options
   */
  function option_definition() {
    $options = parent::option_definition();
    $options['data_source'] = array('default' => '');
    $options['popup_source'] = array('default' => '');
    $options['alt_text'] = array('default' => '');
    $options['geofield_map_width'] = array('default' => '100%');
    $options['geofield_map_height'] = array('default' => '300px');
    $options['geofield_map_zoom'] = array('default' => '8');
    $options['geofield_map_controltype'] = array('default' => 'default');
    $options['geofield_map_mtc'] = array('default' => 'standard');
    $options['geofield_map_pancontrol'] = array('default' => 1);
    $options['geofield_map_maptype'] = array('default' => 'map');
    $options['geofield_map_baselayers_map'] = array('default' => 1);
    $options['geofield_map_baselayers_satellite'] = array('default' => 1);
    $options['geofield_map_baselayers_hybrid'] = array('default' => 1);
    $options['geofield_map_baselayers_physical'] = array('default' => 0);
    $options['geofield_map_scale'] = array('default' => 0);
    $options['geofield_map_overview'] = array('default' => 0);
    $options['geofield_map_overview_opened'] = array('default' => 0);
    $options['geofield_map_scrollwheel'] = array('default' => 0);
    $options['geofield_map_draggable'] = array('default' => 0);
    $options['geofield_map_streetview_show'] = array('default' => 0);
    $options['icon'] = array('default' => '');
    return $options;
  }

  /**
   * Options form
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $handlers = $this->display->handler->get_handlers('field');

    $data_source_options = $popup_source_options = array(
      '' => '<none>',
    );

    foreach ($handlers as $handle) {
      $popup_source_options[$handle->options['id']] = (!empty($handle->options['label'])) ? $handle->options['label'] : $handle->options['id'];

      if (!empty($handle->field_info['type']) && $handle->field_info['type'] == 'geofield') {
        $data_source_options[$handle->options['id']] = (!empty($handle->options['label'])) ? $handle->options['label'] : $handle->options['id'];
      }
    }

    if (count($data_source_options) == 1) {
      $form['error'] = array(
        '#markup' => 'Please add at least 1 geofield to the view',
      );
    }
    else {

      // Map Preset
      $form['data_source'] = array(
        '#type' => 'select',
        '#title' => t('Data Source'),
        '#description' => t('Which field contains geodata?'),
        '#options' => $data_source_options,
        '#default_value' => $this->options['data_source'] ? $this->options['data_source'] : '',
      );

      $form['popup_source'] = array(
        '#type' => 'select',
        '#title' => t('Popup Text'),
        '#options' => $popup_source_options,
        '#default_value' => $this->options['popup_source'] ? $this->options['popup_source'] : '',
      );

      $form['alt_text'] = array(
        '#type' => 'textarea',
        '#title' => t('Alternate Text'),
        '#description' => t('This text shows up when a user does not have javascript enabled'),
        '#default_value' => $this->options['alt_text'] ? $this->options['alt_text'] : '',
      );

      $form = geofield_map_settings_form($this->options, $form);
    }
  }

  /**
   * Renders views (map)
   */
  function render() {
    geophp_load();
    $style_options = $this->view->style_plugin->options;

    $geo_data = (!empty($style_options['data_source'])) ? 'field_' . $style_options['data_source']: NULL;
    $popup_data = (!empty($style_options['popup_source'])) ? $style_options['popup_source'] : NULL;

    if ($geo_data) {
      $this->render_fields($this->view->result);
      $data = array();

      foreach ($this->view->result as $id => $result) {
        $geofield = (!empty($result->$geo_data)) ? $result->$geo_data : NULL;

        if (!empty($geofield)) {
          $description = ($popup_data ? $this->rendered_fields[$id][$popup_data] : '');
          $geometry = geoPHP::load($geofield[0]['raw']['wkt'], 'wkt');
          $datum = json_decode($geometry->out('json'));
          $datum->properties = array(
            'description' => $description,
          );
          $data[] = $datum;
        }
      }

      if (count($data) == 1) {
        $data = $data[0];
      }
      elseif (count($data) > 1) {
        $tmp = $data;
        $data = array(
          'type' => 'GeometryCollection',
          'geometries' => $tmp,
        );
      }

      $map_settings = geofield_map_settings_do($style_options);
      $map_id = drupal_html_id('geofield_map_' . $this->view->name . '_' . $this->view->current_display);

      $js_settings = array(
        $map_id => array(
          'map_id' => $map_id,
          'map_settings' => $map_settings,
          'data' => $data,
        ),
      );

      drupal_add_js(array('geofieldMap' => $js_settings), 'setting');
    }

    drupal_add_js('//maps.googleapis.com/maps/api/js?sensor=false', 'external');
    drupal_add_js(drupal_get_path('module', 'geofield_map') . '/js/GeoJSON.js');
    drupal_add_js(drupal_get_path('module', 'geofield_map') . '/js/geofield_map.js');
    drupal_add_css(drupal_get_path('module', 'geofield_map') . '/css/geofield_map.css');

    // defaults
    $width = '100%';
    $height = '300px';
    if ($style_options['geofield_map_width']) {
      $width = $style_options['geofield_map_width'];
    }
    if ($style_options['geofield_map_height']) {
      $height = $style_options['geofield_map_height'];
    }

    return '<div style="width: ' . $width . '; height: ' . $height . '" id="' . $map_id . '" class="geofieldMap">' . $style_options['alt_text'] . '</div>';
  }
}
