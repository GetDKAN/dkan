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
    $default_processing = $config->get('orphan_processing') ? $config->get('orphan_processing') : 0;
    $form['property_list'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('List of dataset properties with referencing and API endpoint'),
      '#description_display' => 'before',
      '#description' => $this->t("Break out specific sub-elements of the dataset schema so that these elements can be worked with as individual objects. Each element is assigned a unique identifier in addition to it's original schema values."),
      '#options' => $options,
      '#default_value' => $default_values,
    ];
    $form['orphan_processing'] = [
      '#type' => 'radios',
      '#title' => $this->t('Orphaned content processing'),
      '#description_display' => 'before',
      '#description' => $this->t("Define how to process data content that is no longer assocciated with a dataset."),
      '#default_value' => $default_processing,
      '#options' => [
        0 => $this->t('Delete'),
        1 => $this->t('Unpublish'),
      ],
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
    $schemaRetriever = \Drupal::service('metastore.schema_retriever');
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
      ->set('orphan_processing', $form_state->getValue('orphan_processing'))
      ->save();

    // Rebuild routes, without clearing all caches.
    \Drupal::service("router.builder")->rebuild();
  }

}
