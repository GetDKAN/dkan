<?php

namespace Drupal\metastore\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;

/**
 * Data-Dictionary settings form.
 */
class DataDictionarySettingsForm extends ConfigFormBase {

  /**
   * Config ID.
   *
   * @var string
   */
  const SETTINGS = 'metastore.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'data_dictionary_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['dictionary_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Dictionary Mode'),
      '#options' => [
        DataDictionaryDiscoveryInterface::MODE_NONE => $this->t('Disabled'),
        DataDictionaryDiscoveryInterface::MODE_SITEWIDE => $this->t('Sitewide'),
      ],
      '#default_value' => $config->get('dictionary_mode'),
      '#attributes' => [
        'name' => 'dictionary_mode',
      ],
    ];

    $form['sitewide_dictionary_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sitewide Dictionary ID'),
      '#states' => [
        'visible' => [
          ':input[name="dictionary_mode"]' => ['value' => DataDictionaryDiscoveryInterface::MODE_SITEWIDE],
        ],
      ],
      '#default_value' => $config->get('sitewide_dictionary_id'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->config(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('dictionary_mode', $form_state->getValue('dictionary_mode'))
      ->set('sitewide_dictionary_id', $form_state->getValue('sitewide_dictionary_id'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
