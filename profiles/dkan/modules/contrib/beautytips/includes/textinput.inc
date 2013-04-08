<?php

function beautytips_textinput_admin_info() {
  $form['beautytips_text'] = array(
    '#type' => 'fieldset',
    '#title' => 'Text Input Tooltips',
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['beautytips_text']['beautytips_text_input'] = array(
    '#type' => 'checkbox',
    '#title' => 'Display Text input popups',
    '#default_value' => variable_get('beautytips_text_input', FALSE),
  );
  $form['beautytips_text']['beautytips_position'] = array(
    '#type' => 'radios',
    '#title' => 'Which side should the text popups appear on?',
    '#options' => array('bottom' => t('bottom'), 'top' => t('top'), 'left' => t('left'), 'right' => t('right')),
    '#default_value' => variable_get('beautytips_position', 'bottom'),
  );
  $form['beautytips_text']['form_id'] = array(
    '#type' => 'fieldset',
    '#title' => 'Restrict text popups to specific forms. (OPTIONAL)',
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,    
  );
  $form['beautytips_text']['form_id']['beautytips_form_id'] = array(
    '#type' => 'textfield',
    '#title' => 'Enter the form id(s) to use for text popup beautytips',
    '#description' => t('You need to use Drupal php syntax like page_node_form'),
    '#default_value' => variable_get('beautytips_form_id', ''),
  );
  $form['beautytips_text']['form_id']['beautytips_show_form'] = array(
    '#type' => 'checkbox',
    '#title' => 'Display form_ids',
    '#description' => t("Turn this on if you want to see the names of the form ids to enter above.  The names will appear on the pages where the forms are displayed.") . '<div>' . t("Make sure that you turn it off when you're done retrieving the form_ids.") . '</div>',
    '#default_value' => variable_get('beautytips_show_form', ''),
  );
  
  return $form;
}

function beautytips_textinput_menu_items() {
  $items['beautytips/settings/form_ids'] = array(
    'page callback' => 'beautytips_textinput_form_id_off',
    'access arguments' => array('administer site configuration'),
    'file' => 'textinput.inc',
    'file path' => drupal_get_path('module', 'beautytips_ui') . '/includes',
    'type' => MENU_CALLBACK,
  );
  return $items;
}

function beautytips_textinput_form_change(&$form, $form_state, $form_id) {
  if (variable_get('beautytips_text_input', FALSE)) {
    if (variable_get('beautytips_show_form', FALSE)) {
      drupal_set_message(t('The form_id is %form_id.  This message should be ' . l(t('turned off'), 'beautytips/settings/form_ids', array('query' => drupal_get_destination())) . " when finished you're finished checking form_ids.", array('%form_id' => $form_id)));
    }
     
    $add_bt = TRUE;
    if (strlen(variable_get('beautytips_form_id', ''))) {
      if (strpos(variable_get('beautytips_form_id', ''), $form_id) === FALSE) {
        $add_bt = FALSE;
      }
    }
    if ($add_bt) {
      $options = array();
      $options['bt_text_field'] = array(
        'cssSelect' => 'input.form-text',
        'trigger' => array('focus', 'blur'),
        'contentSelector' => "$(this).nextAll('.description:eq(0)').hide().html()",
        'width' => '275px',
        'positions' => array(0 => variable_get('beautytips_position', 'bottom')),
        'preEval' => TRUE,
      );

      // Text areas can have format text with description or just a description.
      // The html structure is different depending on which one is chosen.
      // It would be nice to have a slightly better way of doing this.
      $content = "
        if ($(this).parent('.form-textarea-wrapper').nextAll('.description:eq(0)').length !== 0) {
          $(this).parent('.form-textarea-wrapper').nextAll('.description:eq(0)').hide().html();
        }
        else if ($(this).parent('.form-textarea-wrapper').parent('.form-item').nextAll('.description:eq(0)').length !== 0) {
          $(this).parent('.form-textarea-wrapper').parent('.form-item').nextAll('.description:eq(0)').hide().html();
        }
        else {
          null;
        }";
      $options['bt_text_area'] = array(
        'cssSelect' => 'textarea.form-textarea',
        'trigger' => array('focus', 'dblclick'),
        'contentSelector' => $content,
        'width' => '275px',
        'positions' => array(0 => variable_get('beautytips_position', 'bottom')),
        'preEval' => TRUE,
      );
      beautytips_add_beautytips($options);
    }
  }
}

/**
 * Menu callback.  Turns off the display of form-ids.
 */
function beautytips_textinput_form_id_off() {
  variable_set('beautytips_show_form', FALSE);
  $destination = drupal_get_destination() ? drupal_get_destination() : 'admin/settings/beautytips';
  drupal_goto($destination);
}

