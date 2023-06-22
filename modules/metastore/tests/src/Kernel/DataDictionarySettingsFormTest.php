<?php

namespace Drupal\Tests\metastore\Kernel;

use Drupal\Tests\common\Kernel\ConfigFormTestBase;
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
   * @var string[]
   */
  public static $modules = ['metastore', 'common'];

  /**
   * {@inheritdoc}
   */
  public function provideFormData(): array {
    return [
      [
        [
          'dictionary_mode' => [
            '#value' => DataDictionaryDiscoveryInterface::MODE_SITEWIDE,
            '#config_name' => DataDictionarySettingsForm::SETTINGS,
            '#config_key' => 'data_dictionary_mode',
          ],
          'sitewide_dictionary_id' => [
            '#value' => $this->randomString(),
            '#config_name' => DataDictionarySettingsForm::SETTINGS,
            '#config_key' => 'data_dictionary_sitewide',
          ],
        ],
      ],
      [
        [
          'dictionary_mode' => [
            '#value' => DataDictionaryDiscoveryInterface::MODE_NONE,
            '#config_name' => DataDictionarySettingsForm::SETTINGS,
            '#config_key' => 'data_dictionary_mode',
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->form = new DataDictionarySettingsForm($this->container->get('config.factory'), $this->container->get('messenger'), $this->container->get('dkan.metastore.service'));
  }

}
