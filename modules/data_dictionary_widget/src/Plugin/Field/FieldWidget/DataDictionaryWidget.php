<?php

namespace Drupal\data_dictionary_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * A widget bar.
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

    if (isset($current_fields)) {
      $data_results = $current_fields;
    }

    // if collecting new field information
    if (isset($field_values[0]["field_collection"])) {
      $field_group = $field_values[0]["field_collection"]["group"];
      $data_pre = [
        [
          "name"  => $field_group["name"],
          "title" => $field_group["title"],
          "type"  => $field_group["type"],
          "format"  => $field_group["format"]
        ]
      ];
    }
    
    if (isset($data_pre) && $op === "add") {
      if (isset($current_fields)) {
          $data_results = array_merge($current_fields, $data_pre);
      } else {
          $data_results = $data_pre;
      }
  }

    if (!isset($data_pre) && isset($data_results) && $current_fields) {
      $data_results = $current_fields;
    }


    
    if ($form_state->get('cancel')) {
      // If 'cancel' is TRUE, use existing_field.
      $data_results = $form_state->get('current_fields');
    }

    $element['identifier'] = [
      '#type' => 'textfield',
      //'#required' => TRUE,
      '#title' => $this->t('Identifier'),
      '#default_value' => isset($field_json_metadata['identifier']) ? $field_json_metadata['identifier'] : '',
    ];

    $element['title'] = [
      '#type' => 'textfield',
      //'#required' => TRUE,
      '#title' => $this->t('Title'),
      '#default_value' => isset($field_json_metadata['title']) ? $field_json_metadata['title'] : '',
    ];

    //dump($data_results);

    $element['data'] = [
      '#access' => FALSE,
      '#type' => 'table',
      '#header' => ['NAME', 'TITLE', 'DETAILS'],
      '#rows' => isset($data_results) ? $data_results : [],
      '#tree' => TRUE,
      '#theme' => 'custom_table',
    ];

    $element['add_row_button'] = [
      '#type' => 'submit',
      '#value' => 'Add new field',
      '#access' => TRUE,
      '#op' => 'add_new_field',
      '#submit' => [
        [$this, 'addSubformCallback'],
      ],
      //'#limit_validation_errors' => [],
    ];

    // Set the item type to the entity.
    $form_entity = $form_state->getFormObject()->getEntity();
    if ($form_entity instanceof FieldableEntityInterface) {
      $form_entity->set('field_data_type', 'data-dictionary');
    }

    if ($form_state->get('add_new_field')) {
      $element['field_collection'] = $form_state->get('add_new_field');
      $element['field_collection']['#access'] = TRUE;
      $element['add_row_button']['#access'] = FALSE;
    }

    if ($current_fields || $data_results) {
      $element['data']['#access'] = TRUE;
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    //dump($values);
    $existing_row = $form["field_json_metadata"]["widget"][0]["data"]["#rows"];

    if (isset($values[0]["field_collection"]["group"])) {
      $data_results[] = ([
        "name" => $values[0]["field_collection"]['group']["name"],
        "title" => $values[0]["field_collection"]['group']["title"],
        "type" => $values[0]["field_collection"]['group']["type"],
        "format" => $values[0]["field_collection"]['group']["format"]
      ]);

      $updated = array_merge($existing_row, $data_results);
    }

    if (isset($values[0]) && is_array($values[0])) {
      $json_data = [
        'identifier' => $values[0]['identifier'],
        'title' => $values[0]['title'],
        'data' => [
          'fields' => isset($updated) ? $updated : $existing_row,
        ]
      ];
    }

    $all_values = json_encode($json_data);

    return $all_values;
  }

  /**
   * AJAX callback to update the options of the "Format" field.
   */
  public function updateFormatOptions(array &$form, FormStateInterface $form_state) {
    $field = $form_state->getValue(["field_json_metadata"]);
    $data_type = $field[0]["field_collection"]["group"]["type"];
    $options = [];

    if ($data_type == 'string') {
      $form["field_json_metadata"]["widget"][0]["field_collection"]["group"]["format"]['#description'] = $this->generateFormatDescription($data_type);
      $options = [
        'default' => 'default',
        'email' => 'email',
        'uri' => 'uri',
        'binary' => 'binary',
        'uuid' => 'uuid'
      ];
    }

    if ($data_type == 'date') {
      $form["field_json_metadata"]["widget"][0]["field_collection"]["group"]["format"]['#description'] = $this->generateFormatDescription($data_type);
      $options = [
        'default' => 'default',
        'any' => 'any',
        'pattern' => '{PATTERN}'
      ];
    }

    if ($data_type == 'integer') {
      $form["field_json_metadata"]["widget"][0]["field_collection"]["group"]["format"]['#description'] = $this->generateFormatDescription($data_type);
      $options = [
        'default' => 'default',
      ];
    }

    if ($data_type == 'number') {
      $form["field_json_metadata"]["widget"][0]["field_collection"]["group"]["format"]['#description'] = $this->generateFormatDescription($data_type);
      $options = [
        'default' => 'default',
      ];
    }

    $form["field_json_metadata"]["widget"][0]["field_collection"]["group"]["format"]["#options"] = $options;

    return $form["field_json_metadata"]["widget"][0]["field_collection"]["group"]["format"];
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
          <li><b>{PATTERN}</b>: The value can be parsed according to {PATTERN}, which MUST follow the date formatting syntax of C / Pythin strftime.
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
    // You can populate the subform elements and perform any actions here.
    // You may want to use '#prefix' and '#suffix' to wrap the subform elements.
    $trigger = $form_state->getTriggeringElement();
    $form_state->set('add_new_field', '');
    $op = $trigger['#op'];

    $current_fields = $form["field_json_metadata"]["widget"][0]["data"]["#rows"];
    if ($current_fields) {
      $form_state->set('current_fields', $current_fields);
    }

    if ($op === 'cancel') {
      $form_state->set('cancel', TRUE);
    }

    if ($op === 'add_new_field') {
      $form['my_fieldset'] = array(
        '#type' => 'fieldset',
        '#title' => t('My Fieldset'),
        '#collapsible' => TRUE, // Optional: Make the fieldset collapsible.
        '#collapsed' => FALSE, // Optional: Start the fieldset in the open state.
      );

      $add_fields = [
        '#access' => FALSE,
        'group' => [
          '#type' => 'fieldset',
          '#title' => t('Add new field'),
          '#collapsible' => TRUE, // Optional: Make the fieldset collapsible.
          '#collapsed' => FALSE, // Optional: Start the fieldset in the open state.
          'name' => [
            '#type' => 'textfield',
            '#title' => 'Name',
            '#description' => 'A name for this field.',
          ],
          'title' => [
            '#type' => 'textfield',
            '#title' => 'Title',
            '#description' => 'A human-readable title.',
          ],
          'type' => [
            '#type' => 'select',
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
              'wrapper' => 'field-json-metadata-format', // The HTML element to replace via AJAX.

            ],
          ],
          'format' => [
            '#type' => 'select',
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
            ],
            'cancel_settings' => [
              '#type' => 'submit',
              '#value' => $this->t('Cancel'),
              '#op' => 'cancel',
              '#submit' => [
                [$this, 'addSubformCallback'],
              ],
              
            ],
          ],
        ],

      ];

      $form_state->set('add_new_field', $add_fields);
    }

    if ($op === 'add') {
      $form_state->set('add', TRUE);
    }

    $form_state->setRebuild();
  }
}