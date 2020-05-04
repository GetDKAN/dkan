<?php

namespace Drupal\data_content_type\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DkanDataSettingsForm.
 *
 * @package Drupal\data_content_type\Form
 * @codeCoverageIgnore
 */
class DkanDataSettingsForm extends ConfigFormBase {

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'data_content_type.settings',
    ];
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'data_content_type_settings_form';
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('data_content_type.settings');
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
   * Retrieve schema properties.
   *
   * @return array
   *   List of schema properties' title and description.
   */
  public function retrieveSchemaProperties() : array {
    // Create a json object from our schema.
    $schemaRetriever = \Drupal::service('schema.schema_retriever');
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
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('data_content_type.settings')
      ->set('property_list', $form_state->getValue('property_list'))
      ->save();

    // Rebuild routes, without clearing all caches.
    \Drupal::service("router.builder")->rebuild();
  }

}
