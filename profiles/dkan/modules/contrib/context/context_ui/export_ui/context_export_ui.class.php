<?php

/**
 * CTools export UI extending class. Slightly customized for Context.
 */
class context_export_ui extends ctools_export_ui {
  function list_form(&$form, &$form_state) {
    parent::list_form($form, $form_state);
    $form['top row']['submit'] = $form['bottom row']['submit'];
    $form['top row']['reset'] = $form['bottom row']['reset'];
    $form['bottom row']['#access'] = FALSE;
    // Invalidate the context cache.
    context_invalidate_cache();
    return;
  }

  function list_css() {
    ctools_add_css('export-ui-list');
    drupal_add_css(drupal_get_path("module", "context_ui") ."/context_ui.css");
  }

  function list_render(&$form_state) {
    $table = array(
      'header' => $this->list_table_header(),
      'rows' => $this->rows, 
      'attributes' => array(
        'class' => array('context-admin'),
        'id' => 'ctools-export-ui-list-items',
      ),
    );
    return theme('table', $table);
  }

  function list_build_row($item, &$form_state, $operations) {
    $name = $item->name;

    // Add a row for tags.
    $tag = !empty($item->tag) ? $item->tag : t('< Untagged >');
    if (!isset($this->rows[$tag])) {
      $this->rows[$tag]['data'] = array();
      $this->rows[$tag]['data'][] = array('data' => check_plain($tag), 'colspan' => 3, 'class' => array('tag'));
      $this->sorts["{$tag}"] = $tag;
    }

    // Build row for each context item.
    $this->rows["{$tag}:{$name}"]['data'] = array();
    $this->rows["{$tag}:{$name}"]['class'] = !empty($item->disabled) ? array('ctools-export-ui-disabled') : array('ctools-export-ui-enabled');
    $this->rows["{$tag}:{$name}"]['data'][] = array(
      'data' => check_plain($name) . "<div class='description'>" . check_plain($item->description) . "</div>",
      'class' => array('ctools-export-ui-name')
    );
    $this->rows["{$tag}:{$name}"]['data'][] = array(
      'data' => check_plain($item->type),
      'class' => array('ctools-export-ui-storage')
    );
    $this->rows["{$tag}:{$name}"]['data'][] = array(
      'data' => theme('links', array(
        'links' => $operations,
        'attributes' => array('class' => array('links inline'))
      )),
      'class' => array('ctools-export-ui-operations'),
    );

    // Sort by tag, name.
    $this->sorts["{$tag}:{$name}"] = $tag . $name;
  }

  /**
   * Override of edit_form_submit().
   * Don't copy values from $form_state['values'].
   */
  function edit_form_submit(&$form, &$form_state) {
    if (!empty($this->plugin['form']['submit'])) {
      $this->plugin['form']['submit']($form, $form_state);
    }
  }

  /**
   * Override default final validation for ctools. With import wizard
   * it was possible to get default ctools export ui name validation
   * rules, this ensures we always get ours.
   */
  function edit_finish_validate(&$form, &$form_state) {
    if ($form_state['op'] != 'edit') {
      // Validate the name. Fake an element for form_error().
      $export_key = $this->plugin['export']['key'];
      $element = array(
        '#value' => $form_state['item']->{$export_key},
        '#parents' => array('name'),
      );
      $form_state['plugin'] = $this->plugin;
      context_ui_edit_name_validate($element, $form_state);
    }
  }
}


/**
 * Generates the omnibus context definition editing form.
 *
 * @param $form
 *   Form array to populate.
 * @param $form_state
 *   Form state array
 */
function context_ui_form(&$form, &$form_state) {  
  $conditions = array_keys(context_conditions());
  sort($conditions);
  $reactions = array_keys(context_reactions());
  sort($reactions);
    
  $context = $form_state['item'];
  if (!empty($form_state['input'])) {
    $context = _context_ui_rebuild_from_input($context, $form_state['input'], $conditions, $reactions);
  }
  
  $form['#base'] = 'context_ui_form';
  $form['#theme'] = 'context_ui_form';

  // Core context definition
  $form['info']['#type'] = 'fieldset';
  $form['info']['#tree'] = FALSE;


  $form['info']['name']['#element_validate'] = array('context_ui_edit_name_validate');

  $form['info']['tag'] = array(
    '#title' => t('Tag'),
    '#type' => 'textfield',
    '#required' => FALSE,
    '#maxlength' => 255,
    '#default_value' => isset($context->tag) ? $context->tag : '',
    '#description' => t('Example: <code>theme</code>') .'<br/>'. t('A tag to group this context with others.'),
  );

  $form['info']['description'] = array(
    '#title' => t('Description'),
    '#type' => 'textfield',
    '#required' => FALSE,
    '#maxlength' => 255,
    '#default_value' => isset($context->description) ? $context->description: '',
    '#description' => t('The description of this context definition.'),
  );

  // Condition mode
  $form['condition_mode'] = array(
    '#type' => 'checkbox',
    '#default_value' => isset($context->condition_mode) ? $context->condition_mode : FALSE,
    '#title' => t('Require all conditions'),
    '#description' => t('If checked, all conditions must be met for this context to be active. Otherwise, the first condition that is met will activate this context.')
  );

  // Condition plugin forms
  $form['conditions'] = array(
    '#theme' => 'context_ui_plugins',
    '#title' => t('Conditions'),
    '#description' => t('Trigger the activation of this context'),
    '#tree' => TRUE,
    'selector' => array(
      '#type' => 'select',
      '#options' => array(0 => '<'. t('Add a condition') .'>'),
      '#default_value' => 0,
    ),
    'state' => array(
      '#attributes' => array('class' => array('context-plugins-state')),
      '#type' => 'hidden',
    ),
    'plugins' => array('#tree' => TRUE),
  );
  foreach ($conditions as $condition) {
    if ($plugin = context_get_plugin('condition', $condition)) {
      $form['conditions']['plugins'][$condition] = array(
        '#tree' => TRUE,
        '#plugin' => $plugin,
        '#context_enabled' => isset($context->conditions[$condition]), // This flag is used at the theme layer.
        'values' => $plugin->condition_form($context),
        'options' => $plugin->options_form($context),
      );
      $form['conditions']['selector']['#options'][$condition] = $plugin->title;
    }
  }

  // Reaction plugin forms
  $form['reactions'] = array(
    '#theme' => 'context_ui_plugins',
    '#title' => t('Reactions'),
    '#description' => t('Actions to take when this context is active'),
    '#tree' => TRUE,
    'selector' => array(
      '#type' => 'select',
      '#options' => array(0 => '<'. t('Add a reaction') .'>'),
      '#default_value' => 0,
    ),
    'state' => array(
      '#attributes' => array('class' => array('context-plugins-state')),
      '#type' => 'hidden',
    ),
    'plugins' => array('#tree' => TRUE),
  );
  foreach ($reactions as $reaction) {
    if ($plugin = context_get_plugin('reaction', $reaction)) {
      $form['reactions']['plugins'][$reaction] = $plugin->options_form($context) + array(
        '#plugin' => $plugin,
        '#context_enabled' => isset($context->reactions[$reaction]), // This flag is used at the theme layer.
      );
      $form['reactions']['selector']['#options'][$reaction] = $plugin->title;
    }
  }
}

/**
 * Handle the complex job of rebuilding a Context from submission data in the case of a validation error.
 *
 * @param $context
 *   The context object to modify.
 * @param $input
 *   A form submission values
 * @param $conditions
 *   The full list of condition plugins
 * @param $reactions
 *   The full list of reaction plugins
 *
 * @return
 *   A context object
 */
function _context_ui_rebuild_from_input($context, $input, $conditions, $reactions) {
  $condition_defaults = array();  
  foreach ($conditions as $condition) {
    if ($plugin = context_get_plugin('condition', $condition)) {
      $condition_defaults[$condition] = array(
        'values' => $plugin->condition_form($context),
        'options' => $plugin->options_form($context),
      );
    }
  }
  $input['conditions']['plugins'] = array_merge($condition_defaults, $input['conditions']['plugins']);
  
  $reaction_defaults = array();
  foreach ($reactions as $reaction) {
    if ($plugin = context_get_plugin('reaction', $reaction)) {
      $reaction_defaults[$reaction] = $plugin->options_form($context);
    }
  }
  $input['reactions']['plugins'] = array_merge($reaction_defaults, $input['reactions']['plugins']);

  return context_ui_form_process($context, $input, FALSE);
}

/**
 * Modifies a context object from submitted form values.
 *
 * @param $context
 *   The context object to modify.
 * @param $form
 *   A form array with submitted values
 * @param $submit
 *   A flag indicating if we are building a context on submit. If on
 *   submit, it will clear out conditions/reactions that are empty.
 *
 * @return
 *   A context object
 */
function context_ui_form_process($context, $form, $submit = TRUE) {
  $context->name = isset($form['name']) ? $form['name'] : $context->name;
  $context->description = isset($form['description']) ? $form['description'] : NULL;
  $context->tag = isset($form['tag']) ? $form['tag'] : NULL;
  $context->condition_mode = isset($form['condition_mode']) ? $form['condition_mode'] : NULL;
  $context->conditions = array();
  $context->reactions = array();
  if (!empty($form['conditions'])) {
    $enabled = explode(',', $form['conditions']['state']);
    foreach ($form['conditions']['plugins'] as $condition => $values) {
      if (in_array($condition, $enabled, TRUE) && ($plugin = context_get_plugin('condition', $condition))) {
        if (isset($values['values'])) {
          $context->conditions[$condition]['values'] = $plugin->condition_form_submit($values['values']);
        }
        if (isset($values['options'])) {
          $context->conditions[$condition]['options'] = $plugin->options_form_submit($values['options']);
        }
        if ($submit && context_empty($context->conditions[$condition]['values'])) {
          unset($context->conditions[$condition]);
        }
      }
    }
  }
  if (!empty($form['reactions'])) {
    $enabled = explode(',', $form['reactions']['state']);
    foreach ($form['reactions']['plugins'] as $reaction => $values) {
      if (in_array($reaction, $enabled, TRUE) && ($plugin = context_get_plugin('reaction', $reaction))) {
        if (isset($values)) {
          $context->reactions[$reaction] = $plugin->options_form_submit($values);
        }
        if ($submit && context_empty($context->reactions[$reaction])) {
          unset($context->reactions[$reaction]);
        }
      }
    }
  }
  return $context;
}

/**
 * Submit handler for main context_ui form.
 */
function context_ui_form_submit($form, &$form_state) {
  $form_state['item'] = context_ui_form_process($form_state['item'], $form_state['values']);
}

/**
 * Replacement for ctools_export_ui_edit_name_validate(). Allow dashes.
 */
function context_ui_edit_name_validate($element, &$form_state) {
  $plugin = $form_state['plugin'];
  // Check for string identifier sanity
  if (!preg_match('!^[a-z0-9_-]+$!', $element['#value'])) {
    form_error($element, t('The name can only consist of lowercase letters, underscores, dashes, and numbers.'));
    return;
  }

  // Check for name collision
  if ($form_state['op'] != 'edit') {
    if (empty($form_state['item']->export_ui_allow_overwrite) && $exists = ctools_export_crud_load($plugin['schema'], $element['#value'])) {
      form_error($element, t('A @plugin with this name already exists. Please choose another name or delete the existing item before creating a new one.', array('@plugin' => $plugin['title singular'])));
    }
  }
}
