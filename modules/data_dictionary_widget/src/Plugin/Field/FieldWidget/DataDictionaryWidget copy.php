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
    if (!empty($items[0]->value)) {
      $field_json_metadata = json_decode($items[0]->value, true);

      foreach ($field_json_metadata["data"]["fields"] as $key => $value) {
        $fields[] = $field_json_metadata["data"]["fields"][$key];
      }
    }

        // Add button to trigger the subform.
        $element['add_subform'] = [
          '#type' => 'button',
          '#value' => $this->t('Add'),
          '#ajax' => [
            'callback' => [$this, 'addSubformCallback'],
            'wrapper' => 'subform-wrapper',
          ],
        ];
    
        // Wrapper for the subform.
        $element['subform_wrapper'] = [
          '#type' => 'container',
          '#attributes' => ['id' => 'subform-wrapper'],
        ];
    
        // Subform elements.
        $element['subform_wrapper']['subform'] = [
          $element['identifier'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Identifier'),
          ],
      
        ];
    
        // Add a Cancel button to close the subform.
        $element['subform_wrapper']['cancel'] = [
          '#type' => 'button',
          '#value' => $this->t('Cancel'),
          '#attributes' => ['class' => ['js-hide']],
          '#ajax' => [
            'callback' => [$this, 'cancelSubformCallback'],
            'wrapper' => 'subform-wrapper',
          ],
        ];

    $element['identifier'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Identifier'),
      '#default_value' => isset($field_json_metadata['identifier']) ? $field_json_metadata['identifier'] : '',
    ];

    $element['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => isset($field_json_metadata['title']) ? $field_json_metadata['title'] : '',
    ];

    $element['data'] = [
      '#type' => 'table',
      '#header' => ['NAME', 'TITLE', 'DETAILS'],
      '#rows' => isset($fields) ? $fields : [],
      '#tree' => TRUE,
    ];

    $element['add_row_button'] = [
      '#type' => 'submit',
      '#value' => 'Add another Field',
      '#access' => TRUE,
      '#submit' => [
        [$this, '_data_dictionary_widget_add_field_row'],
      ],
    ];

    

    $add_field = $element['add_field'] = [
      '#type' => 'submit',
      '#value' => 'Add field',
      '#access' => FALSE,
      '#attributes' => [
        'class' => ['button--primary'],
      ],
      '#submit' => [
        [$this, '_data_dictionary_widget_add_field_row'],
      ],
    ];

    $cancel_field = $element['cancel_field'] = [
      '#type' => 'submit',
      '#value' => 'Cancel',
      '#access' => FALSE,
      '#submit' => [
        [$this, '_data_dictionary_widget_add_field_row'],
      ],
    ];


    // Check if the table exists in form_state, then set it to the element[data]
    $table = $form_state->get('rows');

    if ($table) {
      $add_field['#access'] = TRUE;
      //$element['add_row_button']['#access'] = FALSE;
      foreach ($table as $key => $value) {
        $element['data'][$key] = [
          'name' => $table[$key]['name'],
          'title' => $table[$key]['title'],
          'type' => $table[$key]['type'],
        ];
      }
    }

    // Set the item type to the entity.
    $form_entity = $form_state->getFormObject()->getEntity();
    if ($form_entity instanceof FieldableEntityInterface) {
      $form_entity->set('field_data_type', 'data-dictionary');
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {

    $existing_data = $form["field_json_metadata"]["widget"][0]["data"]["#rows"];

    if (isset($values[0]['data'][0])){
      $values[0]["data"] = array_merge($values[0]["data"], $existing_data);
    } else {
      $values[0]["data"] = $existing_data;
    }

    if (isset($values[0]) && is_array($values[0])) {
      $json_data = [
        'identifier' => $values[0]['identifier'],
        'title' => $values[0]['title'],
        'data' => [
          'fields' => $values[0]['data'],
        ]
      ];
    }

    $all_values = json_encode($json_data);

    return $all_values;
  }

  public function _data_dictionary_widget_add_field_row($form, FormStateInterface $form_state) {
    
    if ($form_state->get('rows')) {
      $form['data'] = $form_state->get('rows');
    }

    $form['data'][] = [
      'name' => [
        '#type' => 'textfield',
        '#title' => 'A name for this field.',
      ],
      'title' => [
        '#type' => 'textfield',
        '#title' => 'A human-readable title.',
      ],
      'type' => [
        '#type' => 'select',
        '#title' => 'Data type:',
        '#options' => [
          'string' => t('String'),
          'date' => t('Date'),
          'integer' => t('Integer'),
          'number' => t('Number'),
        ],
      ],
    ];

    $form_state->set('rows', $form['data']);
  
    $form_state->setRebuild();
  }

  /**
   * Ajax callback for the Add button.
   */
  public function addSubformCallback(array &$form, FormStateInterface $form_state) {
    // You can populate the subform elements and perform any actions here.
    // You may want to use '#prefix' and '#suffix' to wrap the subform elements.

    return $form['subform_wrapper'];
  }

  /**
   * Ajax callback for the Cancel button.
   */
  public function cancelSubformCallback(array &$form, FormStateInterface $form_state) {
    // Hide the subform when the Cancel button is clicked.
    $form['subform_wrapper']['#attributes']['class'][] = 'js-hide';

    return $form['subform_wrapper'];
  }

}