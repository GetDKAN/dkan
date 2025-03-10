<?php

namespace Drupal\metastore\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;
use Drupal\metastore\MetastoreService;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Arg order a little weird because TypedConfigManagerInterface was not
   * required when this was first done.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\metastore\MetastoreService $metastore
   *   The metastore service.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config
   *   The typed config manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    MessengerInterface $messenger,
    MetastoreService $metastore,
    TypedConfigManagerInterface $typed_config,
  ) {
    parent::__construct($config_factory, $typed_config);
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
      $container->get('dkan.metastore.service'),
      $container->get('config.typed')
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
        DataDictionaryDiscoveryInterface::MODE_REFERENCE => $this->t('Distribution reference'),
      ],
      '#default_value' => $config->get('data_dictionary_mode'),
      '#description' => $this->t("Chose how to use data dictionaries in DKAN. Sitewide means you will enter a single
        data dictionary UUID that will be used for all datasets on the site. Distribution reference means
        DKAN will look in the describedBy field of a distribution for data dictionary for that specific
        resource."),
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

    $form['csv_headers_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('CSV Headers Mode'),
      '#options' => [
        'resource_headers' => $this->t('Use the column names from the resource file'),
        'dictionary_titles' => $this->t('Use data dictionary titles'),
        'machine_names' => $this->t('Use the datastore machine names'),
      ],
      '#default_value' => $config->get('csv_headers_mode'),
      '#description' => $this->t("Choose the column header values to be used for datastore query CSV downloads."),
      '#attributes' => [
        'name' => 'csv_headers_mode',
      ],
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
      ->set('csv_headers_mode', $form_state->getValue('csv_headers_mode'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
