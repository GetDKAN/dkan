<?php

namespace Drupal\data_dictionary_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Annotation\FieldWidget;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\data_dictionary_widget\Indexes\IndexFieldButtons;
use Drupal\data_dictionary_widget\Indexes\IndexFieldEditCreation;

/**
 * A data-dictionary widget.
 *
 * @FieldWidget(
 *   id = "data_index_widget",
 *   label = @Translation("Data-Dictionary Index Widget"),
 *   field_types = {
 *     "string_long"
 *   }
 * )
 */
class DataIndexWidget extends AbstractMetadataWidget implements TrustedCallbackInterface {

  /**
   * The delta of the index in indexex for this data dictionary.
   */
  protected $indexNumber;

  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings) {
    $this->indexNumber =  \Drupal::request()->query->get('index');
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
  }


  protected function getDelta() {
    return \Drupal::request()->query->get('index');
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $current_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    $field_collection = $values[0]['dictionary_fields']["field_collection"]["group"] ?? [];
    $dd_fields = isset($values[0]["dd_fields"]) ? json_decode($values[0]["dd_fields"]) : [];
    $indexes = isset($values[0]["indexes"]) ? json_decode($values[0]["indexes"]) : [];

    $field_results = !empty($field_collection) ? [
      [
        "name" => $field_collection["name"] ?? '',
        "length" => isset($field_collection["length"]) ? (int) $field_collection["length"] : 0,
      ],
    ] : [];

    $updated_fields = $current_fields ?? [];
    if (!empty($field_results)) {
      $updated_fields = reset($field_results);
    }

    $current_index = [
      $this->indexNumber => [
        'description' => $values[0]['description'],
        'type' => $values[0]['type'],
        'fields' => $updated_fields,
      ]
    ];

    $updated_indexes = $indexes;
    $updated_indexes[array_key_first($current_index)] = reset($current_index);


    $json_data = [
      'identifier' => $values[0]['identifier'] ?? '',
      'data' => [
        'title' => $values[0]['title'] ?? '',
        'fields' => $dd_fields,
        'indexes' => $updated_indexes,
      ],
    ];

    return json_encode($json_data);
  }

  /**
   * @inheritDoc
   */
  protected function processDataResults($data_results, $current_fields, $field_values, $op) {
    $index_data_results = $data_results['indexes'][$this->indexNumber]['fields'] ?? [];

    if (isset($current_fields)) {
      $index_data_results = $current_fields;
    }

    if (isset($field_values["field_json_metadata"][0]["dictionary_fields"]["field_collection"])) {
      $index_field_group = $field_values["field_json_metadata"][0]["indexes"]["fields"]["field_collection"]["group"];

      $data_index_fields_pre = [
        [
          "name" => $index_field_group["name"],
          "length" => (int) $index_field_group["length"],
        ],
      ];
    }

    if (isset($data_index_fields_pre) && $op === "add_field") {
      $index_data_results = isset($current_fields) ? array_merge($current_fields, $data_index_fields_pre) : $data_index_fields_pre;
    }

    return $index_data_results;
  }

  protected function createGeneralFields($element, $field_json_metadata, $current_fields, $form_state) {
    $element['identifier'] = $this->createField('identifier', $field_json_metadata, $form_state);
    $element['title'] = $this->createField('title', $field_json_metadata, $form_state);
    $element['description'] = $this->createField('description', $field_json_metadata, $form_state);
    $element['type'] = $this->createField('type', $field_json_metadata, $form_state);
    $element['indexes'] = $this->createField('indexes', $field_json_metadata, $form_state);
    $element['foob'] = [
      '#name' => 'field_json_metadata[0][foob]',
      '#type' => 'textfield',
      '#required' => FALSE,
      '#title' => t('Data Dictionary Foobar'),
      '#default_value' => $field_json_metadata['foob'] ?? ($field_json_metadata['data']['description'] ?? ''),
    ];

    $element['dictionary_fields'] = [
      '#type' => 'fieldset',
      '#title' => t('Fields'),
      '#prefix' => '<div id = field-json-metadata-index-fields>',
      '#suffix' => '</div>',
      '#markup' => t('<div class="claro-details__description">One or more fields included in index. Must be keys from the fields object.</div>'),
      '#required' => TRUE,
    ];

    $element['dictionary_fields']['current_dictionary_fields'] = $current_fields;
    $element['dd_fields'] = $this->createField('dd_fields', $field_json_metadata, $form_state);


    return $element;
  }

    /**
   * @inheritDoc
   */
  protected function createFieldOptions($op_index, $data_results, $fields_being_modified, $element) {
    // Creating ajax buttons/fields to be placed in correct location later.
    foreach ($data_results as $key => $data) {
      if (self::checkEditingField($key, $op_index, $fields_being_modified)) {
        $element['edit_fields'][$key] = IndexFieldEditCreation::editIndexFields($key, $fields_being_modified);
      }
      else {
        $element['edit_buttons'][$key]['edit_button'] = IndexFieldButtons::editIndexButtons($key);
      }
    }
    $element['add_row_button'] = IndexFieldButtons::addIndexFieldButton();

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected static function createDataRows($current_dictionary_fields, $data_results, $form_state) {
    if ($data_results) {
      return [
        '#access' => ((bool) $current_dictionary_fields || (bool) $data_results),
        '#type' => 'table',
        '#header' => ['NAME', 'LENGTH'],
        '#rows' => $form_state->get('cancel_dictionary_field') ? $current_dictionary_fields : ($data_results ?? []),
        '#tree' => TRUE,
        '#theme' => 'custom_index_fields_table',
      ];
    }
    else {
      return [];
    }
  }

  protected function createField(string $field, array $field_json_metadata, FormStateInterface &$form_state) {
    $field_object = parent::createField($field, $field_json_metadata, $form_state);

    if ($field === 'title') {
      $field_object['#attributes'] = ['readonly' => 'readonly'];
    }

    if (empty($field_object)) {
      $fieldMappings = [
        'description' => [
          '#name' => 'field_json_metadata[0]["description"]',
          '#type' => 'textfield',
          '#required' => TRUE,
          '#title' => t('Index Title'),
          '#default_value' => $field_json_metadata['data']['indexes'][$this->indexNumber]['description'] ?? '',
        ],
        'type' => [
          '#name' => 'field_json_metadata[0]["type"]',
          '#type' => 'select',
          '#description' => t('Index type.'),
          '#title' => 'Index Type',
          '#default_value' => $field_json_metadata['data']['indexes'][$this->indexNumber]['type'] ?? 'index',
          '#op' => 'index_type',
          '#required' => TRUE,
          '#options' => [
            'index' => t('index'),
            'fulltext' => t('fulltext'),
          ],
        ],
        'dd_fields' => [
          '#type' => 'textarea',
          '#access' => FALSE,
          '#required' => TRUE,
          '#title' => t('DD Fields'),
          '#default_value' => isset($field_json_metadata['data']['fields']) ? json_encode($field_json_metadata['data']['fields']) : '',
        ],
      ];
      $field_object = $fieldMappings[$field] ?? [];
    }

    return $field_object;
  }


}
