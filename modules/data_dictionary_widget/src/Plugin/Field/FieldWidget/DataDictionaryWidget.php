<?php

namespace Drupal\data_dictionary_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use phpDocumentor\Reflection\PseudoTypes\True_;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\data_dictionary_widget\Controller\DataDictionary;

/**
 * A data-dictionary widget.
 *
 * @FieldWidget(
 *   id = "data_dictionary_widget",
 *   label = @Translation("Data-Dictionary Widget"),
 *   field_types = {
 *     "string_long"
 *   }
 * )
 */
class DataDictionaryWidget extends WidgetBase implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_values = $form_state->getValue(["field_json_metadata"]);
    $current_fields = $form_state->get('current_fields');
    $fields_being_modified = $form_state->get("fields_being_modified");
    $op = $form_state->getTriggeringElement()['#op'] ?? null;
    $field_json_metadata = !empty($items[0]->value) ? json_decode($items[0]->value, true) : [];
    $op_index = $form_state->getTriggeringElement()['#op'] ? explode("_", $form_state->getTriggeringElement()['#op']) : null;

    $data_results = $field_json_metadata ? $field_json_metadata["data"]["fields"] : [];

    // Build the data_results array to display the rows in the data table.
    $data_results = $this->processDataResults($data_results, $current_fields, $field_values, $op);

    $element['identifier'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Identifier'),
      '#default_value' => $field_json_metadata['identifier'] ?? '',
    ];

    $element['title'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Title'),
      '#default_value' => $field_json_metadata['title'] ?? '',
    ];

    $element['dictionary_fields'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Data Dictionary Fields'),
      '#prefix' => '<div id = field-json-metadata-dictionary-fields>',
      '#suffix' => '</div>',
      '#markup' => t('<div class="claro-details__description">A data dictionary for this resource, compliant with the <a href="https://specs.frictionlessdata.io/table-schema/" target="_blank">Table Schema</a> specification.</div>'),
      '#pre_render' => [
        [$this, 'preRenderForm'],
      ],
    ];

    $element['dictionary_fields']['data'] = [
      '#access' => ((bool) $current_fields || (bool) $data_results),
      '#type' => 'table',
      '#header' => ['NAME', 'TITLE', 'DETAILS'],
      '#rows' => $form_state->get('cancel') ? $current_fields : ($data_results ?? []),
      '#tree' => TRUE,
      '#theme' => 'custom_table',
    ];
   $action_list = [
    'format',
    'edit',
    'update',
    'abort',
    'delete'
   ];

    //Creating ajax buttons/fields to be placed in correct location later.
    foreach ($data_results as $key => $data) {
      if (in_array($op_index[0],$action_list) && array_key_exists($key,  $fields_being_modified)){
          $element['dictionary_fields']['edit_fields'][$key]['name'] = [
            '#name' => 'field_json_metadata[0][dictionary_fields][data][' . $key .'][field_collection][name]',
            '#type' => 'textfield',
            '#value' => $this->t($current_fields[$key]['name']),
            '#required' => TRUE,
            '#title' => 'Name',
            '#description' => 'A name for this field.',
          ];
          $element['dictionary_fields']['edit_fields'][$key]['title'] = [
            '#name' => 'field_json_metadata[0][dictionary_fields][data][' . $key .'][field_collection][title]',
            '#type' => 'textfield',
            '#value' =>  $this->t($current_fields[$key]['title']),
            '#required' => TRUE,
            '#title' => 'Title',
            '#description' => 'A human-readable title.',
          ];
          $element['dictionary_fields']['edit_fields'][$key]['type'] = [
            '#name' => 'field_json_metadata[0][dictionary_fields][data][' . $key .'][field_collection][type]',
            '#type' => 'select',
            '#required' => TRUE,
            '#title' => 'Data type',
            '#value' =>  $current_fields[$key]['type'],
            '#op' => 'format_' . $key,
            '#options' => [
              'string' => t('String'),
              'date' => t('Date'),
              'integer' => t('Integer'),
              'number' => t('Number'),
            ],
            '#ajax' => [
              'callback' => [$this, 'updateFormatOptions'],
              'method' => 'replace',
              'wrapper' => 'field-json-metadata-' . $key .'-format',
            ],
          ];
          $element['dictionary_fields']['edit_fields'][$key]['format'] = [
            '#name' => 'field_json_metadata[0][dictionary_fields][data][' . $key .'][field_collection][format]',
            '#type' => 'select',
            '#required' => TRUE,
            '#title' => 'Format',
            '#description' => DataDictionary::generateFormatDescription($current_fields[$key]['type']),
            '#value' =>  $current_fields[$key]['format'],
            '#prefix' => '<div id = field-json-metadata-' . $key .'-format>',
            '#suffix' => '</div>',
            '#validated' => TRUE,
            '#options' => DataDictionary::setFormatOptions($current_fields[$key]['type']),
          ];
          $element['dictionary_fields']['edit_fields'][$key]['format_other'] = [
            '#name' => 'field_json_metadata[0][dictionary_fields][data][' . $key .'][field_collection][format_other]',
            '#type' => 'textfield',
            '#title' => $this->t('Other format'),
            //'#required' => TRUE,
            '#value' =>  $current_fields[$key]['format_other'],
            '#description' => 'A supported format',
            '#states' => [
              'visible' => [
                ':input[name="field_json_metadata[0][dictionary_fields][data][' . $key .'][field_collection][format]"]' => ['value' => 'other'],
              ],
            ],
          ];
          $element['dictionary_fields']['edit_fields'][$key]['description'] = [
            '#name' => 'field_json_metadata[0][dictionary_fields][data][' . $key .'][field_collection][description]',
            '#type' => 'textfield',
            '#value' =>  $current_fields[$key]['description'],
            '#required' => TRUE,
            '#title' => 'Description',
            '#description' => 'Information about the field data.',
          ];

          $element['dictionary_fields']['edit_fields'][$key]['update_field']['actions' ]= [
            '#type' => 'actions',
            'save_update' => [
              '#type' => 'submit',
              '#name' => 'update_' . $key,
              '#value' => $this->t('Save'),
              '#op' => 'update_' . $key,
              '#submit' => [
                [$this, 'editSubformCallback'],
              ],
              '#ajax' => [
                'callback' => [$this, 'subformAjax'],
                'wrapper' => 'field-json-metadata-dictionary-fields',
                'effect' => 'fade',
              ],
              '#limit_validation_errors' => [],
            ],
            'cancel_updates' => [
              '#type' => 'submit',
              '#name' => 'cancel_update_' . $key,
              '#value' => $this->t('Cancel'),
              '#op' => 'abort_' . $key,
              '#submit' => [
                [$this, 'editSubformCallback'],
              ],
              '#ajax' => [
                'callback' => [$this, 'subformAjax'],
                'wrapper' => 'field-json-metadata-dictionary-fields',
                'effect' => 'fade',
              ],
              '#limit_validation_errors' => [],
              ],
              'delete_field' => [
                '#type' => 'submit',
                '#name' => 'delete_' . $key,
                '#value' => $this->t('Delete'),
                '#op' => 'delete_' . $key,
                '#submit' => [
                  [$this, 'editSubformCallback'],
                ],
                '#ajax' => [
                  'callback' => [$this, 'subformAjax'],
                  'wrapper' => 'field-json-metadata-dictionary-fields',
                  'effect' => 'fade',
                ],
                '#limit_validation_errors' => [],
                ],
            ];


        }else{
        $element['dictionary_fields']['edit_buttons'][$key]['edit_button'] = [
          //'#type' => 'image_button',
          '#type' => 'submit',
          '#name' => 'edit_' . $key,
          //'#id' => 'edit_' . $key,
          '#value' => 'Edit',
          '#access' => TRUE,
          '#op' => 'edit_' . $key,
          '#src' => 'core/misc/icons/787878/cog.svg',
          '#attributes' => ['class' => ['field-plugin-settings-edit'], 'alt' => $this->t('Edit')],
          '#submit' => [
            [$this, 'editSubformCallback'],
          ],
          '#ajax' => [
            'callback' => [$this, 'subformAjax'],
            'wrapper' => 'field-json-metadata-dictionary-fields',
            'effect' => 'fade',
          ],
          '#limit_validation_errors' => [],
        ];
      }
    }

    $element['dictionary_fields']['add_row_button'] = [
      '#type' => 'submit',
      '#value' => 'Add field',
      '#access' => TRUE,
      '#op' => 'add_new_field',
      '#submit' => [
      [$this, 'addSubformCallback'],
      ],
      '#ajax' => [
        'callback' => [$this, 'subformAjax'],
        'wrapper' => 'field-json-metadata-dictionary-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];

    $form_entity = $form_state->getFormObject()->getEntity();
    if ($form_entity instanceof FieldableEntityInterface) {
      $form_entity->set('field_data_type', 'data-dictionary');
    }

    if ($form_state->get('add_new_field')) {

      $element['dictionary_fields']['field_collection'] = $form_state->get('add_new_field');
      $element['dictionary_fields']['field_collection']['#access'] = TRUE;
      $element['dictionary_fields']['add_row_button']['#access'] = FALSE;
      $element['identifier']['#required'] = FALSE;
      $element['title']['#required'] = FALSE;
    }


    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $current_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    $field_collection = $values[0]['dictionary_fields']["field_collection"]["group"] ?? [];

    if (!empty($field_collection)) {
      $data_results = [
        [
          "name" => $field_collection["name"],
          "title" => $field_collection["title"],
          "type" => $field_collection["type"],
          "format" => $field_collection["format"],
          "description" => $field_collection["description"],
        ],
      ];
      $updated = array_merge($current_fields ?? [], $data_results);
    }
    else {
      $updated = $current_fields ?? [];
    }

    $json_data = [
      'identifier' => $values[0]['identifier'] ?? '',
      'title' => $values[0]['title'] ?? '',
      'data' => [
        'fields' => $updated,
      ],
    ];

    $all_values = json_encode($json_data);

    return $all_values;
  }

  /**
   * Cleaning the data up.
   */
  private function processDataResults($data_results, $current_fields, $field_values, $op) {
    if (isset($current_fields)) {
      $data_results = $current_fields;
    }

    if (isset($field_values[0]['dictionary_fields']["field_collection"])) {
      $field_group = $field_values[0]['dictionary_fields']['field_collection']['group'];
      $field_format = $field_group["format"] == 'other' ? $field_group["format_other"] : $field_group["format"];

      $data_pre = [
        [
          "name" => $field_group["name"],
          "title" => $field_group["title"],
          "type" => $field_group["type"],
          "format" => $field_format,
          "description" => $field_group["description"],
        ],
      ];

      if (isset($data_pre) && $op === "add") {
        $data_results = isset($current_fields) ? array_merge($current_fields, $data_pre) : $data_pre;
      }
    }

    if (!isset($data_pre) && isset($data_results) && $current_fields) {
      $data_results = $current_fields;
    }

    return $data_results;
  }

  /**
   * AJAX callback to update the options of the "Format" field.
   */
  public function updateFormatOptions(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    $op_index = explode("_", $trigger['#op']);
    $field = $form_state->getValue(["field_json_metadata"]);
    $format_field = $form["field_json_metadata"]["widget"][0]['dictionary_fields']["field_collection"]["group"]["format"];
    $data_type = $field[0]['dictionary_fields']["field_collection"]["group"]["type"];
    $field_index = $op_index[1];

    //The update format field is located in a diferent location.
    if(str_contains($op, 'format')){
      $format_field = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["edit_fields"][$field_index]["format"];
      $data_type = $field[0]["dictionary_fields"]["data"][$field_index]["field_collection"]["type"];
    }


    $format_field['#description'] = DataDictionary::generateFormatDescription($data_type);
    $options = DataDictionary::setFormatOptions($data_type);

    $format_field["#options"] = $options;
    return $format_field;

  }


   /**
   * Ajax callback for the Add button.
   */
  public function editSubformCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    $op_index = explode("_", $trigger['#op']);
    $currently_modifying = $form_state->get('fields_being_modified') != null ? $form_state->get('fields_being_modified') : [];
    $current_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    $field_index =  $op_index[1];

    if (str_contains($op, 'abort')) {
      unset($currently_modifying[$field_index] );
      $form_state->set('fields_being_modified', $currently_modifying);
    }

    if (str_contains($op, 'delete')) {
      unset($currently_modifying[$field_index] );
      unset($current_fields[$field_index] );
      $form_state->set('fields_being_modified', $currently_modifying);
    }

    if (str_contains($op, 'update')) {
      $update_values = $form_state->getUserInput();
      unset($currently_modifying[$field_index]);
      $form_state->set('fields_being_modified', $currently_modifying);
      unset($current_fields[$field_index]);
      $current_fields[$field_index] =  [
        'name' => $update_values['field_json_metadata'][0]['dictionary_fields']['data'][$field_index]['field_collection']['name'],
        'title' => $update_values['field_json_metadata'][0]['dictionary_fields']['data'][$field_index]['field_collection']['title'],
        'type' => $update_values['field_json_metadata'][0]['dictionary_fields']['data'][$field_index]['field_collection']['type'],
        'format' => $update_values['field_json_metadata'][0]['dictionary_fields']['data'][$field_index]['field_collection']['format'],
        'format_other' => $$update_values['field_json_metadata'][0]['dictionary_fields']['data'][$field_index]['field_collection']['format_other'],
        'description' => $update_values['field_json_metadata'][0]['dictionary_fields']['data'][$field_index]['field_collection']['description'],
      ];
      ksort($current_fields);
    }

    if (str_contains($op, 'edit')) {
      $currently_modifying[$field_index] = $current_fields[$field_index];
      $form_state->set('fields_being_modified', $currently_modifying);
    }

    $form_state->set('current_fields', $current_fields);
    $form_state->setRebuild();

  }

  /**
   * Ajax callback for the Add button.
   */
  public function addSubformCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    $form_state->set('add_new_field', '');
    $fields_being_added = $form_state->set('fields_being_added', '');

    $current_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    if ($current_fields) {
      $form_state->set('current_fields', $current_fields);
    }

    if ($op === 'cancel') {
      $form_state->set('cancel', TRUE);
    }

    if ($op === 'add_new_field') {
      $add_fields = [
        '#access' => FALSE,
        'group' => [
          '#type' => 'fieldset',
          '#title' => $this->t('Add new field'),
          '#collapsible' => TRUE,
          '#collapsed' => FALSE,
          'name' => [
            '#type' => 'textfield',
            '#required' => TRUE,
            '#title' => 'Name',
            '#description' => $this->t('A name for this field.'),
          ],
          'title' => [
            '#type' => 'textfield',
            '#required' => TRUE,
            '#title' => 'Title',
            '#description' => $this->t('A human-readable title.'),
          ],
          'type' => [
            '#type' => 'select',
            '#required' => TRUE,
            '#title' => 'Data type',
            '#default_value' => 'string',
            '#op' => 'type',
            '#options' => [
              'string' => $this->t('String'),
              'date' => $this->t('Date'),
              'integer' => $this->t('Integer'),
              'number' => $this->t('Number'),
            ],
            '#ajax' => [
              'callback' => [$this, 'updateFormatOptions'],
              'method' => 'replace',
              'wrapper' => 'field-json-metadata-format',
            ],
          ],
          'format' => [
            '#type' => 'select',
            '#required' => TRUE,
            '#title' => 'Format',
            '#description' => DataDictionary::generateFormatDescription("string"),
            '#default_value' => 'default',
            '#prefix' => '<div id = field-json-metadata-format>',
            '#suffix' => '</div>',
            '#validated' => TRUE,
            '#options' => [
              'default' => 'default',
              'email' => 'email',
              'uri' => 'uri',
              'binary' => 'binary',
              'uuid' => 'uuid',
            ],
          ],
          'format_other' => [
            '#type' => 'textfield',
            '#title' => $this->t('Other format'),
          // '#required' => TRUE,
            '#description' => $this->t('A supported format'),
            '#states' => [
              'visible' => [
                ':input[name="field_json_metadata[0][dictionary_fields][field_collection][group][format]"]' => ['value' => 'other'],
              ],
            ],
            // '#element_validate' => [[$this, 'customValidationCallback']],
          ],
          'description' => [
            '#type' => 'textfield',
            '#required' => TRUE,
            '#title' => 'Description',
            '#description' => 'Information about the field data.',
          ],
          'actions' => [
            '#type' => 'actions',
            'save_settings' => [
              '#type' => 'submit',
              '#button_type' => 'primary',
              '#value' => $this->t('Add'),
              '#op' => 'add',
              '#submit' => [
            [$this, 'addSubformCallback'],
              ],
              '#ajax' => [
                'callback' => [$this, 'subformAjax'],
                'wrapper' => 'field-json-metadata-dictionary-fields',
                'effect' => 'fade',
              ],
            ],
            'cancel_settings' => [
              '#type' => 'submit',
              '#value' => $this->t('Cancel'),
              '#op' => 'cancel',
              '#submit' => [
            [$this, 'addSubformCallback'],
              ],
              '#ajax' => [
                'callback' => [$this, 'subformAjax'],
                'wrapper' => 'field-json-metadata-dictionary-fields',
                'effect' => 'fade',
              ],
              '#limit_validation_errors' => [],
            ],
          ],
        ],
      ];

      $form_state->set('add_new_field', $add_fields);
    }

    if ($op === 'add') {
      $form_state->set('add', TRUE);
      $form_state->set('cancel', FALSE);
    }

    $form_state->setRebuild();
  }

  /**
   * Ajax callback.
   */
  public function subformAjax(array &$form, FormStateInterface $form_state) {
    return $form["field_json_metadata"]["widget"][0]["dictionary_fields"];
  }

  /**
   * Prerender callback for the form.
   *
   * Moves the buttons into the table.
   */
  public function preRenderForm(array $dictionaryFields) {
    return DataDictionary::setAjaxElements($dictionaryFields);
  }

  /**
   * Widget validation callback.
   */
  public function customValidationCallback($element, &$form_state) {
    $format_field = $form_state->getUserInput()['field_json_metadata'][0]['dictionary_fields']['field_collection']['group']['format'];
    $other_format_value = $element['#value'];

    // Check if the 'format' field is 'other' and 'format_other' field is empty.
    if ($format_field == 'other' && empty($other_format_value)) {
      // Add a validation error.
      $form_state->setError($element, $this->t('Other format is required when "Other" is selected as the format.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRenderForm'];
  }

}
