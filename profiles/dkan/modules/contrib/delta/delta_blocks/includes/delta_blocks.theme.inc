<?php
/**
 * @file
 * Theme functions for the Delta blocks module.
 */

/**
 * Returns the rendered branding.
 *
 * @ingroup themeable
 */
function theme_delta_blocks_branding($variables) {
  $logo = theme('delta_blocks_logo', $variables);
  $site_name = theme('delta_blocks_site_name', $variables);
  $site_slogan = theme('delta_blocks_site_slogan', $variables);

  $attributes['class'] = array('site-name-slogan');
  
  if ($variables['site_name_hidden'] && $variables['site_slogan_hidden']) {
    $attributes['class'][] = 'element-invisible';
  }
  
  return $logo . '<hgroup' . drupal_attributes($attributes) . '>' . $site_name . $site_slogan . '</hgroup>';
}

/**
 * Returns the rendered logo.
 *
 * @ingroup themeable
 */
function theme_delta_blocks_logo($variables) {
  if ($variables['logo']) {
    $image = array(
      '#theme' => 'image',
      '#path' => $variables['logo'],
      '#alt' => $variables['site_name'],
    );
    
    $image = render($image);
  
    if ($variables['logo_linked']) {
      $options['html'] = TRUE;
      $options['attributes']['id'] = 'logo';
      $options['attributes']['title'] = t('Return to the @name home page', array('@name' => $variables['site_name']));
      
      $image = l($image, '<front>', $options);
    }
  
    return '<div class="logo-img">' . $image . '</div>';
  }
}

/**
 * Returns the rendered site name.
 *
 * @ingroup themeable
 */
function theme_delta_blocks_site_name($variables) {
  // If there is no page title set for this page, use a h1 for the site name.
  $tag = drupal_get_title() !== '' ? 'h2' : 'h1';
  $site_name = $variables['site_name'];
  
  if ($variables['site_name_linked']) {
    $options['html'] = TRUE;
    $options['attributes']['title'] = t('Return to the @name home page', array('@name' => $variables['site_name']));

    $link = array(
      '#theme' => 'link',
      '#path' => '<front>',
      '#text' => '<span>' . $site_name . '</span>',
      '#options' => $options,
    );
    
    $site_name = render($link);
  }
  
  $attributes['class'] = array('site-name');
  
  if ($variables['site_name_hidden']) {
    $attributes['class'][] = 'element-invisible';
  }

  return '<' . $tag . drupal_attributes($attributes) . '>' . $site_name . '</' . $tag . '>';
}

/**
 * Returns the rendered site slogan.
 *
 * @ingroup themeable
 */
function theme_delta_blocks_site_slogan($variables) {
  if ($variables['site_slogan'] !== '') {
    $attributes['class'] = array('site-slogan');
    
    if ($variables['site_slogan_hidden']) {
      $attributes['class'][] = 'element-invisible';
    }
    
    return '<h6' . drupal_attributes($attributes) . '>' . $variables['site_slogan'] . '</h6>';
  }
}

/**
 * Returns the rendered page title.
 *
 * @ingroup themeable
 */
function theme_delta_blocks_page_title($variables) {
  if ($variables['page_title'] !== '') {
    $attributes['id'] = 'page-title';
    $attributes['class'][] = 'title';
    
    if ($variables['page_title_hidden']) {
      $attributes['class'][] = 'element-invisible';
    }
    
    return '<h1' . drupal_attributes($attributes) . '>' . $variables['page_title'] . '</h1>';
  }
}

/**
 * Returns the rendered breadcrumb.
 *
 * @ingroup themeable
 */
function theme_delta_blocks_breadcrumb($variables) {
  $output = '';
   
  if (!empty($variables['breadcrumb'])) {  
    if ($variables['breadcrumb_current']) {
      $variables['breadcrumb'][] = l(drupal_get_title(), current_path(), array('html' => TRUE));
    }
  
    $output = '<div id="breadcrumb" class="clearfix"><ul class="breadcrumb">';
    $switch = array('odd' => 'even', 'even' => 'odd');
    $zebra = 'even';
    $last = count($variables['breadcrumb']) - 1;    
    
    foreach ($variables['breadcrumb'] as $key => $item) {
      $zebra = $switch[$zebra];
      $attributes['class'] = array('depth-' . ($key + 1), $zebra);
      
      if ($key == 0) {
        $attributes['class'][] = 'first';
      }
      
      if ($key == $last) {
        $attributes['class'][] = 'last';
      }

      $output .= '<li' . drupal_attributes($attributes) . '>' . $item . '</li>';
    }
      
    $output .= '</ul></div>';
  }
  
  return $output;
}

/**
 * Returns the rendered action items.
 *
 * @ingroup themeable
 */
function theme_delta_blocks_action_links($variables) {
  $actions = render($variables['action_links']);
  
  if (!empty($actions)) {
    return '<div id="action-links" class="clearfix"><ul class="action-links">' . $actions . '</ul></div>';
  }
}

/**
 * Returns the rendered feed icons.
 *
 * @ingroup themeable
 */
function theme_delta_blocks_feed_icons($variables) {
  return $variables['feed_icons'];
}

/**
 * Returns the rendered menu local tasks.
 *
 * @ingroup themeable
 */
function theme_delta_blocks_tabs($variables) {
  $tabs = theme('menu_local_tasks', $variables);
  
  if (!empty($tabs)) {
    return '<div id="tabs" class="clearfix">' . $tabs . '</div>';
  }
}