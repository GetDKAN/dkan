<?php

namespace Drupal\data_dictionary_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

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
class DataDictionaryWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {    
    $field_values = $form_state->getValue(["field_json_metadata"]);
    $current_fields = $form_state->get('current_fields');
    $field_json_metadata = !empty($items[0]->value) ? json_decode($items[0]->value, true) : [];
    $data_results = $field_json_metadata ? $field_json_metadata["data"]["fields"] : [];
    $op = $form_state->getTriggeringElement()['#op'] ?? null;

    // build the data_results array to display the rows in the data table.
    $data_results = $this->processDataResults($data_results, $current_fields, $field_values, $op);

    $element['identifier'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Identifier'),
      '#default_value' => isset($field_json_metadata['identifier']) ? $field_json_metadata['identifier'] : '',
    ];

    $element['title'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Title'),
      '#default_value' => isset($field_json_metadata['title']) ? $field_json_metadata['title'] : '',
    ];

    $element['dictionary_fields'] = [
      '#type' => 'fieldset',
      '#title' => t('Data Dictionary Fields'),
      '#prefix' => '<div id = field-json-metadata-dictionary-fields>',
      '#suffix' => '</div>',
      '#markup' => t('<div class="claro-details__description">A data dictionary for this resource, compliant with the <a href="https://specs.frictionlessdata.io/table-schema/" target="_blank">Table Schema</a> specification.</div>'),
    ];
    
    $element['dictionary_fields']['data'] = [
      '#access' => ((bool) $current_fields || (bool) $data_results),
      '#type' => 'table',
      '#header' => ['NAME', 'TITLE', 'DETAILS'],
      '#rows' => $form_state->get('cancel') ? $current_fields : ($data_results ?? []),
      '#tree' => TRUE,
      '#theme' => 'custom_table',
    ];
    
    $element['dictionary_fields']['add_row_button'] = [
      '#type' => 'submit',
      '#value' => 'Add field',
      '#access' => TRUE,
      '#op' => 'add_new_field',
      '#submit' => [
        [$this, 'addSubformCallback'],
      ],
      '#ajax' => [
        'callback' => [$this, 'addSubformAjax'],
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
      //$element['dictionary_fields']['field_collection']['#limit_validation_errors'] = [['identifier']];
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
        ]
      ];
      $updated = array_merge($current_fields ?? [], $data_results);
    } else {
      $updated = $current_fields ?? [];
    }

    $json_data = [
      'identifier' => $values[0]['identifier'] ?? '',
      'title' => $values[0]['title'] ?? '',
      'data' => [
        'fields' => $updated,
      ]
    ];

    $all_values = json_encode($json_data);

    return $all_values;
  }

  private function processDataResults($data_results, $current_fields, $field_values, $op) {
    if (isset($current_fields)) {
      $data_results = $current_fields;
    }
  
    if (isset($field_values[0]['dictionary_fields']["field_collection"])) {
      $field_group = $field_values[0]['dictionary_fields']["field_collection"]["group"];
      $field_format = $field_group["format"] == 'other' ? $field_group["format_other"] : $field_group["format"];

      $data_pre = [
        [
          "name" => $field_group["name"],
          "title" => $field_group["title"],
          "type" => $field_group["type"],
          "format" => $field_format,
          "description" => $field_group["description"],
        ]
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
    $field = $form_state->getValue(["field_json_metadata"]);
    $data_type = $field[0]['dictionary_fields']["field_collection"]["group"]["type"];
    $options = [];

    if ($data_type == 'string') {
      $form["field_json_metadata"]["widget"][0]['dictionary_fields']["field_collection"]["group"]["format"]['#description'] = $this->generateFormatDescription($data_type);
      $options = [
        'default' => 'default',
        'email' => 'email',
        'uri' => 'uri',
        'binary' => 'binary',
        'uuid' => 'uuid'
      ];
    }

    if ($data_type == 'date') {
      $form["field_json_metadata"]["widget"][0]['dictionary_fields']["field_collection"]["group"]["format"]['#description'] = $this->generateFormatDescription($data_type);
      $options = [
        'default' => 'default',
        'any' => 'any',
        'other' => 'other'
      ];
    }

    if ($data_type == 'integer') {
      $form["field_json_metadata"]["widget"][0]['dictionary_fields']["field_collection"]["group"]["format"]['#description'] = $this->generateFormatDescription($data_type);
      $options = [
        'default' => 'default',
      ];
    }

    if ($data_type == 'number') {
      $form["field_json_metadata"]["widget"][0]['dictionary_fields']["field_collection"]["group"]["format"]['#description'] = $this->generateFormatDescription($data_type);
      $options = [
        'default' => 'default',
      ];
    }

    $form["field_json_metadata"]["widget"][0]['dictionary_fields']["field_collection"]["group"]["format"]["#options"] = $options;

    return $form["field_json_metadata"]["widget"][0]['dictionary_fields']["field_collection"]["group"]["format"];
  }

  /**
   * Function to generate the description for the "Format" field.
   *
   * @param string $dataType
   *
   * @return string
   */
  function generateFormatDescription($dataType) {
    $description = "<p>Supported formats depend on the specified field type:</p>";
    
    if ($dataType === 'string') {
      $description .= "
        <ul>
          <li><b>default</b>: Any valid string.
          <li><b>email</b>: A valid email address.
          <li><b>uri</b>: A valid URI.
          <li><b>binary</b>: A base64 encoded string representing binary data.
          <li><b>uuid</b>: A string that is a UUID.
        </ul>";
    }

    if ($dataType === 'date') {
      $description .= "
        <ul>
          <li><b>default</b>: An ISO8601 format string of YYYY-MM-DD.
          <li><b>any</b>: Any parsable representation of a date. The implementing library can attept to parse the datetime via a range of strategies.
          <li><b>other</b>: The value can be parsed according to {PATTERN}, which MUST follow the date formatting syntax of C / Python strftime.
        </ul>";
    }

    if ($dataType === 'integer') {
      $description .= "
        <ul>
          <li><b>default</b>: Any valid string.
        </ul>";
    }

    if ($dataType === 'number') {
      $description .= "
        <ul>
          <li><b>default</b>: Any valid string.
        </ul>";
    }

    return $description;
  }

  /**
   * Ajax callback for the Add button.
   */
  public function addSubformCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $form_state->set('add_new_field', '');
    $op = $trigger['#op'];

    $current_fields = $form["field_json_metadata"]["widget"][0]['dictionary_fields']["data"]["#rows"];
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
          '#title' => t('Add new field'),
          '#collapsible' => TRUE,
          '#collapsed' => FALSE,
          'name' => [
            '#type' => 'textfield',
            '#required' => TRUE,
            '#title' => 'Name',
            '#description' => 'A name for this field.',
          ],
          'title' => [
            '#type' => 'textfield',
            '#required' => TRUE,
            '#title' => 'Title',
            '#description' => 'A human-readable title.',
          ],
          'type' => [
            '#type' => 'select',
            '#required' => TRUE,
            '#title' => 'Data type',
            '#default_value' => 'string',
            '#op' => 'type',
            '#options' => [
              'string' => t('String'),
              'date' => t('Date'),
              'integer' => t('Integer'),
              'number' => t('Number'),
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
            '#description' => $this->generateFormatDescription("string"),
            '#default_value' => 'default',
            '#prefix' => '<div id = field-json-metadata-format>',
            '#suffix' => '</div>',
            '#validated' => TRUE,
            '#options' => [
              'default' => 'default',
              'email' => 'email',
              'uri' => 'uri',
              'binary' => 'binary',
              'uuid' => 'uuid'
            ],
          ],
          'format_other' => [
            '#type' => 'textfield',
            '#title' => $this->t('Other format'),
            //'#required' => TRUE,
            '#description' => 'A supported format',
            '#states' => [
              'visible' => [
                ':input[name="field_json_metadata[0][dictionary_fields][field_collection][group][format]"]' => ['value' => 'other'],
              ],
            ],
            //'#element_validate' => [[$this, 'customValidationCallback']],
          ],
          'description' => [
            '#type' => 'textfield',
            '#required' => TRUE,
            '#title' => 'Description',
            '#description' => 'A human-readable title.',
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
                'callback' => [$this, 'addSubformAjax'],
                'wrapper' => 'field-json-metadata-dictionary-fields',
                'effect' => 'fade',
              ],
              //'#limit_validation_errors' => [$form["field_json_metadata"]["widget"][0]["identifier"]],
            ],
            'cancel_settings' => [
              '#type' => 'submit',
              '#value' => $this->t('Cancel'),
              '#op' => 'cancel',
              '#submit' => [
                [$this, 'addSubformCallback'],
              ],
              '#ajax' => [
                'callback' => [$this, 'addSubformAjax'],
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

  public function addSubformAjax(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $form_state->set('add_new_field', '');
    $op = $trigger['#op'];

    $current_fields = $form["field_json_metadata"]["widget"][0]['dictionary_fields']["data"]["#rows"];
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
          '#title' => t('Add new field'),
          '#collapsible' => TRUE,
          '#collapsed' => FALSE,
          'name' => [
            '#type' => 'textfield',
            '#required' => TRUE,
            '#title' => 'Name',
            '#description' => 'A name for this field.',
          ],
          'title' => [
            '#type' => 'textfield',
            '#required' => TRUE,
            '#title' => 'Title',
            '#description' => 'A human-readable title.',
          ],
          'type' => [
            '#type' => 'select',
            '#required' => TRUE,
            '#title' => 'Data type',
            '#default_value' => 'string',
            '#op' => 'type',
            '#options' => [
              'string' => t('String'),
              'date' => t('Date'),
              'integer' => t('Integer'),
              'number' => t('Number'),
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
            '#description' => $this->generateFormatDescription("string"),
            '#default_value' => 'default',
            '#prefix' => '<div id = field-json-metadata-format>',
            '#suffix' => '</div>',
            '#validated' => TRUE,
            '#options' => [
              'default' => 'default',
              'email' => 'email',
              'uri' => 'uri',
              'binary' => 'binary',
              'uuid' => 'uuid'
            ],
          ],
          'format_other' => [
            '#type' => 'textfield',
            //'#required' => TRUE,
            '#title' => $this->t('Other format'),
            '#description' => 'A supported format',
            '#states' => [
              'visible' => [
                ':input[name="field_json_metadata[0][dictionary_fields][field_collection][group][format]"]' => ['value' => 'other'],
              ],
            ],
            //'#element_validate' => [[$this, 'customValidationCallback']],
          ],
          'description' => [
            '#type' => 'textfield',
            '#required' => TRUE,
            '#title' => 'Description',
            '#description' => 'A human-readable title.',
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
                'callback' => [$this, 'addSubformAjax'],
                'wrapper' => 'field-json-metadata-dictionary-fields',
                'effect' => 'fade',
              ],
              //'#limit_validation_errors' => [$form["field_json_metadata"]["widget"][0]["identifier"]],
            ],
            'cancel_settings' => [
              '#type' => 'submit',
              '#value' => $this->t('Cancel'),
              '#op' => 'cancel',
              '#submit' => [
                [$this, 'addSubformCallback'],
              ],
              '#ajax' => [
                'callback' => [$this, 'addSubformAjax'],
                'wrapper' => 'field-json-metadata-dictionary-fields',
                'effect' => 'fade',
              ],
              '#limit_validation_errors' => [['identifier', 'title']],
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

    return $form["field_json_metadata"]["widget"][0]["dictionary_fields"];
    
  }

  public function customValidationCallback($element, &$form_state) {
    $format_field = $form_state->getUserInput()['field_json_metadata'][0]['dictionary_fields']['field_collection']['group']['format'];
    $other_format_value = $element['#value'];

    // Check if the 'format' field is 'other' and the 'format_other' field is empty.
    if ($format_field == 'other' && empty($other_format_value)) {
      // Add a validation error.
      $form_state->setError($element, $this->t('Other format is required when "Other" is selected as the format.'));
    }
  }

}