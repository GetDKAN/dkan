<?php

/**
 * Display feature component info
 */
function template_preprocess_features_admin_components(&$vars) {
  drupal_add_css(drupal_get_path('module', 'features') . '/features.css');
  $form = $vars['form'];

  // Basic info
  $vars['name'] = $form['#info']['name'];
  $vars['description'] = isset($form['#info']['description']) ? $form['#info']['description'] : '';

  // Legend/key
  $vars['key'] = array();

  // Dependencies
  $rows = array();
  $modules = features_get_info();
  foreach ($form['#dependencies'] as $dependency => $status) {
    $rows[] = array(
      array(
        'data' => isset($modules[$dependency]->info['name']) ? $modules[$dependency]->info['name'] : $dependency,
        'class' => 'component'
      ),
      theme('features_module_status', array('status' => $status)),
    );
  }
  $vars['dependencies'] = theme('table', array('header' => array(t('Dependency'), t('Status')), 'rows' => $rows));

  // Components
  $rows = array();
  $components = features_get_components();

  // Display key for conflicting elements.
  if (!empty($form['#conflicts'])) {
    $vars['key'][] = array(
      'title' => theme('features_storage_link', array('storage' => FEATURES_CONFLICT, 'text' => t('Conflicts with another feature'))),
      'html' => TRUE,
    );
  }

  if (!empty($form['#info']['features'])) {
    foreach ($form['#info']['features'] as $component => $items) {
      if (!empty($items)) {
        $conflicts = array_key_exists($component, $form['#conflicts'])
                ? $form['#conflicts'][$component]
                : NULL;

        $header = $data = array();
        if (element_children($form['revert'])) {
          $header[] = array(
            'data' => isset($form['revert'][$component]) ? drupal_render($form['revert'][$component]) : '',
            'header' => TRUE
          );
        }
        $header[] = array(
          'data' => isset($components[$component]['name']) ? $components[$component]['name'] : $component,
          'header' => TRUE
        );
        $header[] = array(
          'data' => drupal_render($form['components'][$component]),
          'header' => TRUE
        );
        $rows[] = $header;

        if (element_children($form['revert'])) {
          $data[] = '';
        }
        $data[] = array(
          'data' => theme('features_component_list', array('components' => $items, 'source' => $items, 'conflicts' => $conflicts)),
          'colspan' => 2,
          'class' => 'component'
        );
        $rows[] = $data;
      }
    }
  }
  $vars['components'] = theme('table', array('header' => array(), 'rows' => $rows));

  // Other elements
  $vars['buttons'] = drupal_render($form['buttons']);
  $vars['form'] = $form;
}

/**
 * Themes a module status display.
 */
function theme_features_module_status($vars) {
  switch ($vars['status']) {
    case FEATURES_MODULE_ENABLED:
      $text_status = t('Enabled');
      $class = 'admin-enabled';
      break;
    case FEATURES_MODULE_DISABLED:
      $text_status = t('Disabled');
      $class = 'admin-disabled';
      break;
    case FEATURES_MODULE_MISSING:
      $text_status = t('Missing');
      $class = 'admin-missing';
      break;
    case FEATURES_MODULE_CONFLICT:
      $text_status = t('Enabled');
      $class = 'admin-conflict';
      break;
  }
  $text = !empty($vars['module']) ? $vars['module'] . ' (' . $text_status . ')' : $text_status;
  return "<span class=\"$class\">$text</span>";
}

/**
 * Themes a module status display.
 */
function theme_features_storage_link($vars) {
  $classes = array(
    FEATURES_OVERRIDDEN => 'admin-overridden',
    FEATURES_DEFAULT => 'admin-default',
    FEATURES_NEEDS_REVIEW => 'admin-needs-review',
    FEATURES_REBUILDING => 'admin-rebuilding',
    FEATURES_REBUILDABLE => 'admin-rebuilding',
    FEATURES_CONFLICT => 'admin-conflict',
    FEATURES_DISABLED => 'admin-disabled',
    FEATURES_CHECKING => 'admin-loading',
  );
  $default_text = array(
    FEATURES_OVERRIDDEN => t('Overridden'),
    FEATURES_DEFAULT => t('Default'),
    FEATURES_NEEDS_REVIEW => t('Needs review'),
    FEATURES_REBUILDING => t('Rebuilding'),
    FEATURES_REBUILDABLE => t('Rebuilding'),
    FEATURES_CONFLICT => t('Conflict'),
    FEATURES_DISABLED => t('Disabled'),
    FEATURES_CHECKING => t('Checking...'),
  );
  $text = isset($vars['text']) ? $vars['text'] : $default_text[$vars['storage']];
  if ($vars['path']) {
    $vars['options']['attributes']['class'][] = $classes[$vars['storage']];
    $vars['options']['attributes']['class'][] = 'features-storage';
    return l($text, $vars['path'], $vars['options']);
  }
  else {
    return "<span class='{$classes[$vars['storage']]} features-storage'>{$text}</span>";
  }
}

/**
 * Theme function for displaying form buttons
 */
function theme_features_form_buttons(&$vars) {
  drupal_add_css(drupal_get_path('module', 'features') . '/features.css');

  $output = drupal_render_children($vars['element']);
  return !empty($output) ? "<div class='buttons clearfix'>{$output}</div>" : '';
}

/**
 * Theme for features management form.
 */
function theme_features_form_package(&$vars) {
  drupal_add_css(drupal_get_path('module', 'features') . '/features.css');
  drupal_add_js(drupal_get_path('module', 'features') . '/features.js');

  $output = '';

  $header = array('', t('Feature'), t('Signature'));
  if (isset($vars['form']['state'])) {
    $header[] = t('State');
  }
  if (isset($vars['form']['actions'])) {
    $header[] = t('Actions');
  }

  $rows = array();
  foreach (element_children($vars['form']['status']) as $element) {
    // Yank title & description fields off the form element for
    // rendering in their own cells.
    $name = "<div class='feature'>";
    $name .= "<strong>{$vars['form']['status'][$element]['#title']}</strong>";
    $name .= "<div class='description'>{$vars['form']['status'][$element]['#description']}</div>";
    $name .= "</div>";
    unset($vars['form']['status'][$element]['#title']);
    unset($vars['form']['status'][$element]['#description']);


    // Determine row & cell classes
    $class = $vars['form']['status'][$element]['#default_value'] ? 'enabled' : 'disabled';

    $row = array();
    $row['status'] = array('data' => drupal_render($vars['form']['status'][$element]), 'class' => array('status'));
    $row['name'] = array('data' => $name, 'class' => 'name');
    $row['sign'] = array('data' => drupal_render($vars['form']['sign'][$element]), 'class' => array('sign'));

    if (isset($vars['form']['state'])) {
      $row['state'] = array('data' => drupal_render($vars['form']['state'][$element]), 'class' => array('state'));
    }
    if (isset($vars['form']['actions'])) {
      $row['actions'] = array('data' => drupal_render($vars['form']['actions'][$element]), 'class' => array('actions'));
    }
    $rows[] = array('data' => $row, 'class' => array($class));
  }

  if (empty($rows)) {
    $rows[] = array('', array('data' => t('No features available.'), 'colspan' => count($header)));
  }

  $class = count($header) > 3 ? 'features features-admin' : 'features features-manage';
  $output .= theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('id' => 'features-form-table', 'class' => array($class))));

  // Prevent section from being rendered by drupal_render().

  $output .= drupal_render($vars['form']['buttons']);
  $output .= drupal_render_children($vars['form']);
  return $output;
}

/**
 * Theme functions ====================================================
 */

/**
 * Export selection / display for features export form.
 */
function theme_features_form_export(&$vars) {
  drupal_add_css(drupal_get_path('module', 'features') . '/features.css');
  drupal_add_js(drupal_get_path('module', 'features') . '/features.js');

  $output = '';
  $output .= "<div class='clearfix features-components'>";
  $output .= "<div class='column'>" . drupal_render($vars['form']['components']) . drupal_render($vars['form']['sources']) . "</div>";
  $output .= "<div class='column'>" . drupal_render($vars['form']['preview']) . drupal_render($vars['form']['features']) . "</div>";
  $output .= "</div>";
  $output .= drupal_render_children($vars['form']);
  return $output;
}

/**
 * Theme a set of features export components.
 */
function theme_features_form_components(&$vars) {
  $output = '';
  foreach (element_children($vars['form']) as $key) {
    unset($vars['form'][$key]['#title']);
    $output .= "<div class='features-select features-select-{$key}'>" . drupal_render($vars['form'][$key]) . "</div>";
  }
  $output .= drupal_render_children($vars['form']);
  return $output;
}

/**
 * Theme a set of features export components.
 */
function theme_features_components($vars) {
  $info = $vars['info'];
  $sources = $vars['sources'];

  $output = '';
  $rows = array();
  $components = features_get_components();
  if (!empty($info['features']) || !empty($info['dependencies']) || !empty($sources)) {
    $export = array_unique(array_merge(
      array_keys($info['features']),
      array_keys($sources),
      array('dependencies')
    ));
    foreach ($export as $component) {
      if ($component === 'dependencies') {
        $feature_items = isset($info[$component]) ? $info[$component] : array();
      }
      else {
        $feature_items = isset($info['features'][$component]) ? $info['features'][$component] : array();
      }
      $source_items = isset($sources[$component]) ? $sources[$component] : array();
      if (!empty($feature_items) || !empty($source_items)) {
        $rows[] = array(array(
          'data' => isset($components[$component]['name']) ? $components[$component]['name'] : $component,
          'header' => TRUE
        ));
        $rows[] = array(array(
          'data' => theme('features_component_list', array('components' => $feature_items, 'source' => $source_items)),
          'class' => 'component'
        ));
      }
    }
    $output .= theme('table', array('header' => array(), 'rows' => $rows));
    $output .= theme('features_component_key', array());
  }
  return $output;
}

/**
 * Theme individual components in a component list.
 */
function theme_features_component_list($vars) {
  $components = $vars['components'];
  $source = $vars['source'];
  $conflicts = $vars['conflicts'];

  $list = array();
  foreach ($components as $component) {
    // If component is not in source list, it was autodetected
    if (!in_array($component, $source)) {
      $list[] = "<span class='features-detected'>". check_plain($component) ."</span>";
    }
    elseif (is_array($conflicts) && in_array($component, $conflicts)) {
      $list[] = "<span class='features-conflict'>". check_plain($component) ."</span>";
    }
    else {
      $list[] = "<span class='features-source'>". check_plain($component) ."</span>";
    }
  }
  foreach ($source as $component) {
    // If a source component is no longer in the items, it was removed because
    // it is provided by a dependency.
    if (!in_array($component, $components)) {
      $list[] = "<span class='features-dependency'>". check_plain($component) ."</span>";
    }
  }
  return "<span class='features-component-list'>". implode(' ', $list) ."</span>";
}

/**
 * Provide a themed key for a component list.
 */
function theme_features_component_key($vars) {
  $list = array();
  $list[] = "<span class='features-source'>" . t('Normal') . "</span>";
  $list[] = "<span class='features-detected'>" . t('Auto-detected') . "</span>";
  $list[] = "<span class='features-dependency'>" . t('Provided by dependency') . "</span>";
  return "<span class='features-component-list features-component-key'>" . implode(' ', $list) . "</span>";
}
