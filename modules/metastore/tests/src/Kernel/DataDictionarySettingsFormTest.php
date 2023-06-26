<?php

namespace Drupal\Tests\metastore\Kernel;

use Drupal\Tests\common\Kernel\ConfigFormTestBase;
use Drupal\metastore\Form\DataDictionarySettingsForm;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;
use Drupal\Core\Form\FormState;
use Drupal\Tests\metastore\Unit\MetastoreServiceTest;
use Drupal\Component\Render\FormattableMarkup;

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
  public static $modules = ['system', 'node', 'user', 'field', 'field_ui', 'filter', 'text', 'metastore', 'common', 'datastore', 'dkan', 'menu_link_content', 'basic_auth', 'content_moderation', 'workflows'];

  /**
   * Metastore service.
   *
   * @var \Drupal\metastore\MetastoreService
   */
  protected $metastore;

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  /**
   * Node data storage.
   *
   * @var \Drupal\metastore\Storage\NodeData
   */
  protected $datasetStorage;

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
            '#value' => 'data-dictionary-true',
            '#config_name' => DataDictionarySettingsForm::SETTINGS,
            '#config_key' => 'data_dictionary_sitewide',
          ],
        ],
      ],
      [
        [
          'dictionary_mode' => [
            '#value' => DataDictionaryDiscoveryInterface::MODE_SITEWIDE,
            '#config_name' => DataDictionarySettingsForm::SETTINGS,
            '#config_key' => 'data_dictionary_mode',
          ],
          'sitewide_dictionary_id' => [
            '#value' => 'data-dictionary-false',
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
          'sitewide_dictionary_id' => [
            '#value' => '',
            '#config_name' => DataDictionarySettingsForm::SETTINGS,
            '#config_key' => 'data_dictionary_sitewide',
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  private function getDataDictionary(array $fields, array $indexes, string $identifier, string $title = 'Test DataDict') {
    return json_encode([
      'identifier' => $identifier,
      'title' => $title,
      'data' => [
        'fields' => $fields,
        'indexes' => $indexes,
      ],
    ], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
  }

  /**
   * Test submitting config form.
   *
   * @param array $form_values
   *   Form values to test.
   *
   * @dataProvider provideFormData
   */
  public function testConfigForm(array $form_values) {
    // Programmatically submit the given values.
    foreach ($form_values as $form_key => $data) {
      $values[$form_key] = $data['#value'];
    }
    $form_state = (new FormState())->setValues($values);
    \Drupal::formBuilder()->submitForm($this->form, $form_state);

    // Check that the form returns an error when expected, and vice versa.
    $errors = $form_state->getErrors();
    $valid_form = empty($errors);
    $args = [
      '%values' => print_r($values, TRUE),
      '%errors' => $valid_form ? t('None') : implode(' ', $errors),
    ];

    // Confirm data-dictionary settings form validates.
    if ($valid_form) {
      $this->assertTrue($valid_form, new FormattableMarkup('Input values: %values<br/>Validation handler errors: %errors', $args));
    }

    // Confirm data-dictionary settings form does not validate.
    if ($errors) {
      $this->assertTrue(!empty($errors), new FormattableMarkup('Input values: %values<br/>Validation handler errors: %errors', $args));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig('system');
    $this->installConfig('datastore');
    $this->installConfig('node');
    $this->installConfig('metastore');
    $this->installConfig('common');
    $this->installConfig('basic_auth');
    $this->installConfig('content_moderation');
    $this->installConfig('workflows');
    $this->installSchema('system', ['sequences']);
    $this->installSchema('node', ['node_access']);
    $this->installSchema('user', ['users_data']);
    $this->installEntitySchema('node');
    $this->installEntitySchema('workflow');
    $this->installEntitySchema('content_moderation_state');
    $this->installConfig('field');
    $this->installConfig('field_ui');
    $this->installEntitySchema('user');
    $this->installConfig('field');

    $this->metastore = \Drupal::service('dkan.metastore.service');
    $this->validMetadataFactory = MetastoreServiceTest::getValidMetadataFactory($this);

    $dict_id = 'data-dictionary-pass';
    $fields = [
      [
        'name' => 'a',
        'type' => 'integer',
        'format' => 'default',
      ],
      [
        'name' => 'b',
        'title' => 'B',
        'type' => 'date',
        'format' => '%m/%d/%Y',
      ],
      [
        'name' => 'c',
        'title' => 'C',
        'type' => 'number',
      ],
      [
        'name' => 'd',
        'title' => 'D',
        'type' => 'string',
      ],
      [
        'name' => 'e',
        'title' => 'E',
        'type' => 'boolean',
      ],
    ];
    $indexes = [
      [
        'name' => 'index_a',
        'fields' => [
          ['name' => 'a'],
          ['name' => 'd', 'length' => 6],
        ],
        'type' => 'index',
      ],
      [
        'name' => 'fulltext_index_a',
        'fields' => [
          ['name' => 'd', 'length' => 3],
        ],
        'type' => 'fulltext',
      ],
    ];
    $data_dict = $this->validMetadataFactory->get($this->getDataDictionary($fields, $indexes, $dict_id), 'data-dictionary');

    // Create data-dictionary.
    $this->metastore->post('data-dictionary', $data_dict);
    $this->metastore->publish('data-dictionary', $dict_id);

    $this->form = new DataDictionarySettingsForm($this->container->get('config.factory'), $this->container->get('messenger'), $this->container->get('dkan.metastore.service'));
  }

}
