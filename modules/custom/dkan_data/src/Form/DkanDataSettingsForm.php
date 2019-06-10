<?php

namespace Drupal\dkan_data\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DkanDataSettingsForm.
 *
 * @package Drupal\dkan_data\Form
 * @codeCoverageIgnore
 */
class DkanDataSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'dkan_data.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dkan_data_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('dkan_data.settings');
    $options = $this->retrieveSchemaProperties();
    $default_values = $config->get('property_list');
    $form['property_list'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('List of dataset properties with referencing and API endpoint'),
      '#options' => $options,
      '#default_value' => $default_values,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * @return array
   *   List of schema properties' title and description.
   */
  public function retrieveSchemaProperties() : array {
    // Create a json object from our schema.
    $schemaRetriever = \Drupal::service('dkan_schema.schema_retriever');
    $schema = $schemaRetriever->retrieve('dataset');
    $schema_object = json_decode($schema);

    // Build a list of the schema properties' title and description.
    $property_list = [];
    foreach ($schema_object->properties as $property_id => $property_object) {
      if (isset($property_object->title)) {
        $property_list[$property_id] = "{$property_object->title} ({$property_id})";
      }
      else {
        $property_list[$property_id] = ucfirst($property_id);
      }
    }

    return $property_list;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('dkan_data.settings')
      ->set('property_list', $form_state->getValue('property_list'))
      ->save();

    // Rebuild routes, without clearing all caches.
    \Drupal::service("router.builder")->rebuild();
  }

}
