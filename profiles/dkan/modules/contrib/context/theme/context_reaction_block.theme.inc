<?php

/**
 * Block form.
 */
function theme_context_block_form($vars) {
  $row = array(
    array('data' => drupal_render($vars['form']['blocks']), 'class' => array('blocks')),
    array('data' => drupal_render($vars['form']['selector']) . drupal_render($vars['form']['block']['help']), 'class' => array('selector')),
  );

  $output = drupal_render_children($vars['form']);

  $table = array(
    'rows' => array($row),
    'attributes' => array('id' => 'context-blockform'),
  );
  $output .= theme('table', $table);
  return $output;
}

/**
 * Generates the AJAX enabled block administration portion of the context_ui admin form.
 */
function theme_context_block_regions_form($vars) {
  $form = $vars['form'];

  // Add draggable weights
  drupal_add_js('misc/tableheader.js');
  drupal_add_js(drupal_get_path('module', 'context') . '/plugins/context_reaction_block.js');
  drupal_add_css(drupal_get_path('module', 'context') . '/plugins/context_reaction_block.css');

  $output = '';

  foreach (element_children($form) as $region) {
    $attr = array(
      'id' => "context-blockform-region-{$region}",
      'class' => array("context-blockform-region"),
    );
    drupal_add_tabledrag($attr['id'], 'order', 'sibling', 'tabledrag-hide', NULL, NULL, FALSE);
    $rows = array();
    foreach (element_children($form[$region]) as $id) {
      $form[$region][$id]['weight']['#attributes'] = array('class' => array('tabledrag-hide'));
      $label = $form[$region][$id]['#value'];
      $remove = l(t('X'), $_GET['q'], array('fragment' => 'remove', 'attributes' => array('class' => array('remove'))));
      $rows[] = array(
        'data' => array($label, drupal_render($form[$region][$id]['weight']), $remove),
        'class' => array('draggable'),
        'id' => $id,
      );
    }
    $output .= "<div class='label context-blockform-regionlabel-{$region}'>";
    $output .= l(t('+') . ' ' . t('Add'), $_GET['q'], array('fragment' => $region, 'attributes' => array('class' => array('add-block'))));
    $output .= $form[$region]['#title'];
    $output .= "</div>";
    $output .= theme('table', array('rows' => $rows, 'attributes' => $attr));
  }
  return $output;
}

/**
 * Use placeholder content for script tags that need to be replaced.
 */
function theme_context_block_script_placeholder($vars) {
  $text = $vars['text'];
  $message = t('Please reload the page to view this block.');
  return "<div class='script-placeholder'><strong>{$text}</strong><div class='description'>{$message}</div></div>";
}

/**
 * Preprocessor for theme('context_block_browser').
 */
function template_preprocess_context_block_browser(&$vars) {
  $categories = array(
    '#type' => 'select',
    '#options' => array(0 => '<' . t('All Categories') . '>'),
    '#attributes' => array('class' => array('context-block-browser-categories')),
    '#value' => 0,
    '#size' => 1,
    '#id' => '',
    '#name' => '',
    '#parents' => array(''),
    '#multiple' => FALSE,
    '#required' => FALSE,
  );
  $blocks = array();
  // Group blocks by module.
  foreach ($vars['blocks'] as $block) {
    $group = isset($block->context_group) ? $block->context_group : $block->module;
    // Normalize the $group, borrowed from drupal_html_id
    $group = strtr(drupal_strtolower($group), array(' ' => '-', '_' => '-', '[' => '-', ']' => ''));
    if (!isset($categories[$group])) {
      $info = system_get_info('module', $block->module);
      $title = isset($block->context_group) ? $block->context_group : (!empty($info['name']) ? $info['name'] : $block->module);
      $categories['#options'][$group] = $title;
    }
    $blocks[$group][$block->bid] = $block; // Don't call theme('context_block_browser_item') to allow others to alter.
  }

  //add help text to tell people how to use the block browser
  $help_text = array(
    '#prefix' => '<div class="context_ui-help-text">',
    '#markup' => t('To add a block to the current region, simply click on the block.  You may use the category filter to filter by
      block type or the search filter to find the block that you wish to add.'),
    '#suffix' => '</div>',
  );

  $filter_label = array(
    '#prefix' => '<div class="filter-label">',
    '#markup' => t('Search Filter'),
    '#suffix' => '</div>',
  );

  $vars['categories'] = $categories; // Don't call theme('select') here to allow further preprocesses to alter the element.
  $vars['blocks'] = $blocks;
  $vars['help_text'] = $help_text;
  $vars['filter_label'] = $filter_label;
}

/**
 * Preprocessor for theme('context_block_browser_item').
 */
function template_preprocess_context_block_browser_item(&$vars) {
  $vars['bid'] = $vars['block']->bid;
  $vars['info'] = check_plain($vars['block']->info);
}

/**
 * Theme wrapper for editable blocks.
 *
 * @ingroup themeable
 */
function theme_context_block_edit_wrap($vars) {
  $block = $vars['element']['#block'];
  return $vars['element']['#children'] . "<a id='context-block-{$block->module}-{$block->delta}' class='context-block editable edit-{$block->context}'></a>";
}
