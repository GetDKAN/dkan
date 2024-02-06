<?php

namespace Drupal\data_dictionary_widget\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Various operations for the Data Dictionary index Widget.
 */
class DictionaryIndexes extends ControllerBase {
  
  /**
   * Setting ajax elements.
   */
  public static function setIndexAjaxElements(array $dictionaryIndexes) {
    foreach ($dictionaryIndexes['data']['#rows'] as $row => $data) {
      $edit_button = $dictionaryIndexes['edit_buttons'][$row] ?? NULL;
      $edit_indexes = $dictionaryIndexes['edit_indexes'][$row] ?? NULL;
      // Setting the ajax fields if they exsist.
      if ($edit_button) {
        $dictionaryIndexes['data']['#rows'][$row] = array_merge($data, $edit_button);
        unset($dictionaryIndexes['edit_buttons'][$row]);
      }
      elseif ($edit_indexes) {
        unset($dictionaryIndexes['data']['#rows'][$row]);
        $dictionaryIndexes['data']['#rows'][$row]['field_collection'] = $edit_indexes;
        // Remove the buttons so they don't show up twice.
        unset($dictionaryIndexes['edit_fields'][$row]);
        ksort($dictionaryIndexes['data']['#rows']);
      }

    }
    return $dictionaryIndexes;
  }

  // $element['dictionary_indexes']['index'] = [
  //   '#type' => 'fieldset',
  //   '#title' => $this->t('Index'),
  //   '#prefix' => '<div id = field-json-metadata-dictionary-indexes-index>',
  //   '#suffix' => '</div>',
  //   '#collapsible' => TRUE,
  //   '#collapsed' => FALSE,
  // ];

  // $element['dictionary_indexes']['index']['fields'] = [
  //   '#type' => 'fieldset',
  //   '#title' => $this->t('Fields'),
  //   '#prefix' => '<div id = field-json-metadata-dictionary-indexes-index-fields>',
  //   '#suffix' => '</div>',
  //   '#description' => 'One or more fields included in index. Must be keys from the fields object.',
  //   '#description_display' => 'before',
  // ];

  // $element['dictionary_indexes']['index']['fields']['field'] = [
  //   '#type' => 'fieldset',
  //   '#title' => $this->t('Field'),
  //   '#prefix' => '<div id = field-json-metadata-dictionary-indexes-index-fields-field>',
  //   '#suffix' => '</div>',
  //   '#collapsible' => TRUE,
  //   '#collapsed' => FALSE,
  // ];

  // $element['dictionary_indexes']['index']['type'] = [
  //   '#type' => 'select',
  //   '#title' => $this->t('Type'),
  //   '#prefix' => '<div id = field-json-metadata-dictionary-indexes-index-type>',
  //   '#suffix' => '</div>',
  //   '#options' => [
  //     'index' => $this->t('index'),
  //     'fulltext' => $this->t('fulltext'),
  //   ],
  //   '#default' => 'index',
  //   "#description" => "Index type.",
  //   '#description_display' => 'before',
  //   '#required' => TRUE,
  // ];

  // $element['dictionary_indexes']['index']['description'] = [
  //   '#type' => 'textfield',
  //   '#title' => $this->t('Description'),
  //   '#prefix' => '<div id = field-json-metadata-dictionary-indexes-index-description>',
  //   '#suffix' => '</div>',
  //   '#title' => 'Description',
  //   '#description' => 'Description of index purpose or functionality.',
  //   '#description_display' => 'before',
  // ];

  // $element['dictionary_indexes']['add_index'] = [
  //   '#type' => 'submit',
  //   '#name' => 'Add index',
  //   '#value' => 'Add one',
  //   '#access' => TRUE,
  //   '#op' => 'add_new_index',
  //   '#submit' => [
  //     [$this, 'indexSubformCallback'],
  //   ],
  //   '#ajax' => [
  //     'callback' => [$this, 'indexSubformAjax'],
  //     'wrapper' => 'field-json-metadata-dictionary-index',
  //     'effect' => 'fade',
  //   ],
  //   '#limit_validation_errors' => [],
  // ];

  // $element['dictionary_indexes']['remove_index'] = [
  //   '#type' => 'submit',
  //   '#name' => 'Remove index',
  //   '#title' => $this->t('Remove one'),
  //   '#value' => 'Remove one',
  //   '#access' => TRUE,
  //   '#op' => 'remove_new_index',
  //   '#submit' => [
  //     [$this, 'indexSubformCallback'],
  //   ],
  //   '#ajax' => [
  //     'callback' => [$this, 'indexSubformAjax'],
  //     'wrapper' => 'field-json-metadata-dictionary-index',
  //     'effect' => 'fade',
  //   ],
  //   '#limit_validation_errors' => [],
  // ];

  // $element['dictionary_indexes']['index']['fields']['add_index_field'] = [
  //   '#type' => 'submit',
  //   '#name' => 'Add index field',
  //   '#value' => 'Add one',
  //   '#access' => TRUE,
  //   '#op' => 'add_new_index_field',
  //   '#submit' => [
  //     [$this, 'indexFieldSubformCallback'],
  //   ],
  //   '#ajax' => [
  //     'callback' => [$this, 'indexFieldSubformAjax'],
  //     'wrapper' => 'field-json-metadata-dictionary-index-fields',
  //     'effect' => 'fade',
  //   ],
  //   '#limit_validation_errors' => [],
  // ];

  // $element['dictionary_indexes']['index']['fields']['remove_index_field'] = [
  //   '#type' => 'submit',
  //   '#name' => 'Remove index field',
  //   '#value' => 'Remove one',
  //   '#access' => TRUE,
  //   '#op' => 'remove_new_index_field',
  //   '#submit' => [
  //     [$this, 'indexFieldSubformCallback'],
  //   ],
  //   '#ajax' => [
  //     'callback' => [$this, 'indexFieldSubformAjax'],
  //     'wrapper' => 'field-json-metadata-dictionary-index-fields',
  //     'effect' => 'fade',
  //   ],
  //   '#limit_validation_errors' => [],
  // ];

}