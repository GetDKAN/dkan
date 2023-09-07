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
    $field_json_metadata = json_decode($items[0]->value);

    $element['identifier'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Identifier'),
      '#default_value' => isset($field_json_metadata->identifier) ? $field_json_metadata->identifier : '',
    ];

    $element['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => isset($field_json_metadata->title) ? $field_json_metadata->title : '',
    ];

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
      $json_data = [
        'identifier' => $values[0]['identifier'],
        'title' => $values[0]['title'],
      ];
      $values = json_encode($json_data);

    return $values;
  }


  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Create the custom setting 'size', and
      // assign a default value of 60
      'size' => 60,
    ] + parent::defaultSettings();
  }

  /**
  * {@inheritdoc}
  */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['size'] = [
      '#type' => 'number',
      '#title' => $this->t('Size of textfield'),
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
      '#min' => 1,
    ];

    return $element;
  }

}
