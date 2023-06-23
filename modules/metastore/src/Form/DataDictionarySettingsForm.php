<?php

namespace Drupal\metastore\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;

/**
 * Data-Dictionary settings form.
 */
class DataDictionarySettingsForm extends ConfigFormBase {

  /**
   * The metastore service.
   *
   * @var \Drupal\metastore\MetastoreService
   */
  protected $metastore;

  /**
   * The messenger interface.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a \Drupal\Core\Form\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\metastore\MetastoreService $metastore
   *   The metastore service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MessengerInterface $messenger, MetastoreService $metastore) {
    $this->setConfigFactory($config_factory);
    $this->messenger = $messenger;
    $this->metastore = $metastore;
  }

  /**
   * Instantiates a new instance of this class.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Interface implemented by service container classes.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('messenger'),
      $container->get('dkan.metastore.service')
    );
  }

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
      '#default_value' => $config->get('data_dictionary_mode'),
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
      '#default_value' => $config->get('data_dictionary_sitewide'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('dictionary_mode') === 'sitewide') {
      try {
        // Search for existing data-dictionary id.
        if (!$this->metastore->get('data-dictionary', $form_state->getValue('sitewide_dictionary_id'))) {
          throw new \Exception('Data not found.');
        }
      }
      catch (\Exception $e) {
        $form_state->setErrorByName('sitewide_dictionary_id', $e->getMessage());
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->config(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('data_dictionary_mode', $form_state->getValue('dictionary_mode'))
      ->set('data_dictionary_sitewide', $form_state->getValue('sitewide_dictionary_id'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
