<?php

namespace Drupal\datastore\SqlEndpoint\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for the SQL endpoint.
 *
 * NO LONGER USED; TO BE REMOVED.
 *
 * @package Drupal\sql_endpoint\Form
 * @todo Remove
 *
 * @codeCoverageIgnore
 */
class DkanSqlEndpointSettingsForm extends ConfigFormBase {

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'datastore_settings_form';
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'datastore.settings',
    ];
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('datastore.settings');
    $form['rows_limit'] = [
      '#type' => 'number',
      '#min' => 1,
      '#max' => 20000,
      '#title' => $this->t('Rows limit'),
      '#default_value' => $config->get('rows_limit'),
      '#description' => $this->t('Maximum number of rows the datastore SQL query endpoint can return in a single request.'),
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

    $this->config('datastore.settings')
      ->set('rows_limit', $form_state->getValue('rows_limit'))
      ->save();
  }

}
