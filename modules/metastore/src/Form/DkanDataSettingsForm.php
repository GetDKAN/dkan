<?php

namespace Drupal\metastore\Form;

use Drupal\common\SchemaPropertiesTrait;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DkanDataSettingsForm.
 *
 * @package Drupal\metastore\Form
 * @codeCoverageIgnore
 */
class DkanDataSettingsForm extends ConfigFormBase {
  use SchemaPropertiesTrait;

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
      '#markup' => $this->t(
        'Select properties from the dataset schema to be available as individual objects.
        Each property will be assigned a unique identifier in addition to its original schema value.'
      ),
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
