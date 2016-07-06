<?php
/**
 * @file
 * Theme and preprocess functions for panels and panes.
 */

/**
 * Implements hook_preprocess().
 * Set up variables for the front page.
 */
function nuboot_radix_preprocess(&$variables, $hook) {
  // Panel heading tag.
  $variables['title_heading'] = 'h2';

  // Front page vars.
	$theme_path = drupal_get_path('theme', 'nuboot_radix');
	$fid = theme_get_setting('hero_file');
	$file = !empty($fid) ? file_load($fid) : FALSE;
	if($file):
	  $uri = $file->uri;
	  $variables['tint'] = 'tint';
	  $variables['bg_color'] = 'transparent';
	  $variables['path'] = file_create_url($uri);
	else :
	  $background_option = theme_get_setting('background_option');
	  if(empty($background_option)):
	    //$uri = $theme_path . '/assets/images/hero.jpg';
	    $variables['tint'] = 'no-tint';
	    $variables['bg_color'] = '#005e9a';
	    $variables['path'] = '';
	  else :
	    $uri = '';
	    $variables['tint'] = 'no-tint';
	    $variables['bg_color'] = '#' . ltrim($background_option, '#');
	    $variables['path'] = '';
	  endif;
	endif;
}

/**
 * Implements hook_preprocess_panels_pane().
 */
function nuboot_radix_preprocess_panels_pane(&$vars) {
  // Add titles to dataset and resource forms.
  if($vars['pane']->subtype == 'form' && empty($vars['content']['nid']['#value'])) {
    switch ($vars['content']['type']['#value']) {
      case 'dataset':
        $vars['title'] = "Add a Dataset";
        break;

      case 'resource':
        $vars['title'] = "Add a Resource";
        break;
      
      default:
        break;
    }
  }
}
