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
    $form['property_list'] = [
      '#type' => 'textarea',
      '#title' => $this->t('List of dataset properties to be referenced'),
      '#description' => $this->t('Separate properties by a new line.'),
      '#default_value' => $config->get('property_list'),
    ];
    return parent::buildForm($form, $form_state);
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
