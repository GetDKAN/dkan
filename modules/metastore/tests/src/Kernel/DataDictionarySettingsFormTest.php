<?php

namespace Drupal\Tests\metastore\Kernel;

use Drupal\KernelTests\ConfigFormTestBase;
use Drupal\metastore\Form\DataDictionarySettingsForm;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;

/**
 * Data Dictionary Settings Form class test.
 *
 * @group Form
 */
class DataDictionarySettingsFormTest extends ConfigFormTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['metastore', 'common'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->form = new DataDictionarySettingsForm($this->container->get('config.factory'));
    $this->values = [
      'dictionary_mode' => [
        '#value' => DataDictionaryDiscoveryInterface::MODE_SITEWIDE,
        '#config_name' => DataDictionarySettingsForm::SETTINGS,
        '#config_key' => 'dictionary_mode',
      ],
      'sitewide_dictionary_id' => [
        '#value' => $this->randomString(),
        '#config_name' => DataDictionarySettingsForm::SETTINGS,
        '#config_key' => 'sitewide_dictionary_id',
      ],
    ];
  }

}
