<?php

/**
 * Display a navigation block.
 * Use this theme function to override the output / display of this block.
 */
function theme_multistep_navigation($variables = array()) {
  if(count($variables['links']['#children']) < 1) {
    return '';
  }
  foreach ($variables['links']['#children'] as $key => $group) {
    extract($group);
    if (isset($url) || $next_unsubmitted_step) {
      $content[$key] = l($label, $url, array('attributes' => array('id' => $id, 'class' => $class), 'query' => array('step' => $step)));
    }
    else {
      $content[$key] = '<span id="'. $id .'" class="' . implode(' ', $class) . '">' . $label . '</span>';
    }
  }
  return theme('item_list', array('items' => $content, 'type' => variable_get('multistep_show_step_numbers') ? 'ol' : 'ul'));
}

/**
 * Block Theme function that displays the default output of a progress bar.
 * Use this theme function to override the output / display of this block.
 * 
 * for small prograss values <50% the percentage is shown to the right of the bar
 */
function theme_multistep_progress_bar($variables = array()) {
  list($left, $right) = array($variables['progress']. '%', '');
  if($variables['progress'] < 50)   list($left, $right) = array($right, $left);
  $output = '<div class="multistep-progress-bar-wrapper">';
  $output .= '<div class="multistep-progress-bar multistep-progress-bar-' . $variables['progress'] . '" style="width: ' . $variables['progress'] . '%">' . $left . '</div>';
  if($right) $output .= '<div class="multistep-progress-bar-percentage-right">'. $right .'</div>';
  $output .= '</div>';
  return $output;
}
