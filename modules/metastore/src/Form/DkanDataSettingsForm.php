<?php

namespace Drupal\metastore\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DkanDataSettingsForm.
 *
 * @package Drupal\metastore\Form
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
      'metastore.settings',
    ];
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metastore_settings_form';
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('metastore.settings');
    $options = $this->retrieveSchemaProperties();
    $default_values = $config->get('property_list');
    $form['description'] = [
      '#markup' => $this->t('Select properties on the dataset schema which should reference another object in the metastore even when passed directly with the dataset.'),
    ];
    $form['property_list'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Dataset properties'),
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
    $schemaRetriever = \Drupal::service('dkan.metastore.schema_retriever');
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

    $this->config('metastore.settings')
      ->set('property_list', $form_state->getValue('property_list'))
      ->save();

    // Rebuild routes, without clearing all caches.
    \Drupal::service("router.builder")->rebuild();
  }

}
