<?php

/**
 * @file
 * Blocks for the core version of the DKAN site.
 */

/**
 * Implements hook_block_info().
 */
function dkan_sitewide_block_info() {
  $blocks['dkan_sitewide_license'] = array(
    'info' => t('License'),
    'cache' => DRUPAL_NO_CACHE
  );
  $blocks['dkan_sitewide_social'] = array(
    'info' => t('Social'),
    'cache' => DRUPAL_NO_CACHE
  );
  $blocks['dkan_sitewide_resource_add'] = array(
    'info' => t('Additional Information'),
    'cache' => DRUPAL_NO_CACHE
  );
  $blocks['dkan_sitewide_search_bar'] = array(
    'info' => t('Search'),
    'cache' => DRUPAL_NO_CACHE
  );
  $blocks['dkan_sitewide_other_access'] = array(
    'info' => t('Other Access'),
    'cache' => DRUPAL_NO_CACHE
  );
  $blocks['dkan_sitewide_user_menu'] = array(
    'info' => t('User menu'),
    'cache' => DRUPAL_NO_CACHE
  );

  return $blocks;
}

/**
 * Implements hook_block_view().
 */
function dkan_sitewide_block_view($delta = '') {
  $block = array();

  switch ($delta) {
    case 'dkan_sitewide_license':
      $block['subject'] = t('License');
      $block['content'] = dkan_sitewide_license_block();
      break;

    case 'dkan_sitewide_social':
      $block['subject'] = t('Social');
      $block['content'] = dkan_sitewide_social_block();
      break;

    case 'dkan_sitewide_resource_add':
      $block['subject'] = t('Additional Information');
      $block['content'] = dkan_sitewide_resource_additional_block();
      break;

    case 'dkan_sitewide_search_bar':
      $block['subject'] = '';
      $block['content'] = dkan_sitewide_search_bar();
      break;

    case 'dkan_sitewide_other_access':
      $block['subject'] = 'Other Acess';
      $block['content'] = dkan_sitewide_other_access();
      break;

    case 'dkan_sitewide_user_menu':
      $block['subject'] = t('');
      $block['content'] = dkan_sitewide_user_menu_block();
      break;

  }
  return $block;
}

/**
 * Creates "Additional" block for resources.
 * TODO: Make this a faux field as well attached to resource node.
 */
function dkan_sitewide_resource_additional_block($nid  = '') {
  $node = $nid ? node_load($nid) : menu_get_object();
  $file_info = array();
  if (isset($node->field_upload[$node->language]) && $file = $node->field_upload[$node->language][0]) {
    $file_info[] = array(t('mimetype'), $file['filemime']);
    $file_info[] = array(t('filesize'), $file['filesize']);
    $file_info[] = array(t('resource type'), 'file upload');
    $file_info[] = array(t('timestamp'), date('M d, Y', $file['timestamp']));
    return theme('table', array('rows' => $file_info, 'header' => array(t('Field'), t('Value'))));
  }

}

/**
 * Add license info to block for dataset node.
 */
function dkan_sitewide_license_block() {
  $node = menu_get_object();
  if ($node->type != 'dataset') {
    return '';
  }
  else {
    if (isset($node->field_license) && $node->field_license) {
      $key = $node->field_license[$node->language][0];
      $field = field_info_instance('node', 'field_license', 'dataset');
      $raw_options = $field['widget']['settings']['available_options'];
      foreach (explode("\n", $raw_options) as $option) {
        $option = explode("|", $option);
        $options[$option[0]] = $option[1];
      }
      $key = $key['value'];
      $label = $options[$key];

      $output = l($label, 'http://opendefinition.org/licenses/' . $key) . '<br/>';
      if ($key != 'other-closed') {
        $output .= l('<img class="open-data" src="http://assets.okfn.org/images/ok_buttons/od_80x15_blue.png" alt="[Open Data]">', 'http://opendefinition.org/licenses/' . $key, array('html' => TRUE));
      }

      return $output;
    }
    else {
      return t('License not specified');
    }
  }
}

/**
 * Adds social links.
 */
function dkan_sitewide_social_block() {
  $path = isset($_GET['q']) ? $_GET['q'] : '<front>';
  $link = url($path, array('absolute' => TRUE));
  return '
    <ul class="nav nav-simple">
      <li class="nav-item"><a href="https://plus.google.com/share?url=' . $link  .  '" target="_blank"><i class="ckan-icon ckan-icon-gplus"></i> Google+</a></li>
      <li class="nav-item"><a href="https://twitter.com/share?url=' . $link  . '" target="_blank"><i class="ckan-icon ckan-icon-twitter"></i> Twitter</a></li>
      <li class="nav-item"><a href="https://www.facebook.com/sharer.php?u=' . $link  . '" target="_blank"><i class="ckan-icon ckan-icon-fb"></i> Facebook</a></li>
    </ul>';
}

/**
 * Creates "Other Access" block.
 */
function dkan_sitewide_other_access() {
  $node = menu_get_object();
  $output = t('The information on this page (the dataset metadata) is also available in these formats.');
  $output .= '<p class="btn-group">';
  $output .= l('<i class="ckan-icon ckan-icon-json"></i> JSON', 'node/' . $node->nid . '/json', array('html' => TRUE, 'attributes' => array('class' => array('btn'), 'title' => 'json view of content',)));
  $output .= l('<i class="ckan-icon ckan-icon-rdfxml"></i> RDF', 'node/' . $node->nid . '.rdf', array('html' => TRUE, 'attributes' => array('class' => array('btn'), 'title' => 'json view of content',)));
  $output .= '<p><small>via the <a href="http://docs.getdkan.com">DKAN API</a></small>';
  return $output;
}

/**
 * Creates search bar.
 */
function dkan_sitewide_search_bar() {
  if (module_exists('dkan_sitewide_search_db')) {
    $view = views_get_view('dataset');
    $view->set_display('page');
    $view->init_handlers();
    $exposed_form = $view->display_handler->get_plugin('exposed_form');
    $exposed_form->view->filter['search_api_views_fulltext']->options['expose']['description'] = t('eg. Gold Prices');
    $exposed_form->view->filter['search_api_views_fulltext']->options['expose']['label'] = '';
    return $exposed_form->render_exposed_form(true);
  }
}
