<?php

namespace Drupal\Tests\json_form_widget\Unit;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Component\Utility\EmailValidator;
use Drupal\Component\Uuid\Php;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Language\LanguageDefault;
use Drupal\Core\Language\LanguageManager;
use Drupal\json_form_widget\OptionSource\JsonFormOptionSourcePluginManager;
use Drupal\json_form_widget\Plugin\JsonFormOptionSource\TaxonomySource;
use Drupal\Tests\metastore\Unit\MetastoreServiceTest;
use Drupal\json_form_widget\SchemaUiHandler;
use Drupal\json_form_widget\StringHelper;
use Drupal\json_form_widget\WidgetRouter;
use Drupal\metastore\MetastoreService;
use Drupal\taxonomy\TermStorageInterface;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test class for SchemaUiHandlerTest.
 *
 * @group dkan
 * @group json_form_widget
 * @group unit
 */
class SchemaUiHandlerTest extends TestCase {

  /**
   * The ValidMetadataFactory class used for testing.
   *
   * @var \Drupal\metastore\ValidMetadataFactory|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $validMetadataFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->validMetadataFactory = MetastoreServiceTest::getValidMetadataFactory($this);

    // We need a global container with language_manager.
    $language_manager = new LanguageManager(new LanguageDefault(['en']));
    $options = (new Options())
      ->add('language_manager', $language_manager)
      ->index(0);
    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->getMock();
    \Drupal::setContainer($container);

  }

  /**
   * Test.
   */
  public function testSchemaUi() {
    $widget_router = $this->getRouter([]);
    $options = (new Options())
      ->add('json_form.string_helper', StringHelper::class)
      ->add('dkan.json_form.logger_channel', LoggerInterface::class)
      ->add('uuid', Php::class)
      ->add('json_form.widget_router', $widget_router)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options);
    $container = $container_chain->getMock();
    $ui_handler = SchemaUiHandler::create($container);

    $ui_schema = json_decode('{"@test":{"ui:options":{"widget":"hidden"}},"textarea_text":{"ui:options":{"widget":"textarea","rows":4,"cols":45,"title":"Textarea field","description":"Test description"}},"date":{"ui:options":{"widget":"date","placeholder":"YYYY-MM-DD"}},"disabled":{"ui:options":{"disabled":true}}}');
    $ui_handler->setSchemaUi($ui_schema);
    $form = [
      "@test" => [
        "#type" => "textfield",
        "#title" => "Test field",
        "#description" => "",
        "#default_value" => NULL,
        "#required" => FALSE,
      ],
      "textarea_text" => [
        "#type" => "textfield",
        "#title" => "Test field",
        "#default_value" => NULL,
        "#required" => FALSE,
      ],
      "date" => [
        "#type" => "textfield",
        "#title" => "Test field",
        "#default_value" => '2020-05-11T15:06:39.000Z',
        "#required" => FALSE,
      ],
      "disabled" => [
        "#type" => "textfield",
        "#title" => "Test disabled field",
        "#default_value" => NULL,
        "#required" => FALSE,
      ],
    ];
    $expected = [
      "@test" => [
        "#type" => "textfield",
        "#title" => "Test field",
        "#description" => "",
        "#default_value" => NULL,
        "#required" => FALSE,
        "#access" => FALSE,
      ],
      "textarea_text" => [
        "#type" => "textarea",
        "#title" => "Textarea field",
        "#description" => "Test description",
        '#description_display' => 'before',
        "#default_value" => NULL,
        "#required" => FALSE,
        "#rows" => 4,
        "#cols" => 45,
      ],
      "date" => [
        "#type" => "date",
        "#title" => "Test field",
        "#default_value" => '2020-05-11',
        "#required" => FALSE,
        "#attributes" => [
          "placeholder" => "YYYY-MM-DD",
        ],
        '#date_date_format' => 'Y-m-d',
      ],
      "disabled" => [
        "#type" => "textfield",
        "#title" => "Test disabled field",
        "#default_value" => NULL,
        "#required" => FALSE,
        "#disabled" => TRUE,
      ],
    ];
    $this->assertEquals($expected, $ui_handler->applySchemaUi($form));

    // Test flexible datetime without default value.
    $ui_schema = json_decode('{"modified":{"ui:options":{"widget":"flexible_datetime","timeRequired": true}}}');
    $ui_handler->setSchemaUi($ui_schema);
    $form = [
      "modified" => [
        "#type" => "textfield",
        "#title" => "Flexible datetime field",
        "#default_value" => NULL,
        "#required" => FALSE,
      ],
    ];
    $expected = [
      "modified" => [
        "#type" => "flexible_datetime",
        "#title" => "Flexible datetime field",
        "#default_value" => NULL,
        "#required" => FALSE,
        "#date_time_required" => TRUE,
      ],
    ];
    $this->assertEquals($expected, $ui_handler->applySchemaUi($form));

    // Test flexible datetime with date format 2020-05-11T15:06:39.000Z.
    $ui_schema = json_decode('{"modified":{"ui:options":{"widget":"flexible_datetime","timeRequired": false}}}');
    $ui_handler->setSchemaUi($ui_schema);
    $form = [
      "modified" => [
        "#type" => "textfield",
        "#title" => "Flexible datetime field",
        "#default_value" => '2020-05-11T15:06:39.000Z',
        "#required" => FALSE,
      ],
    ];
    $date = new DrupalDateTime('2020-05-11T15:06:39.000Z');
    $expected = [
      "modified" => [
        "#type" => "flexible_datetime",
        "#title" => "Flexible datetime field",
        "#default_value" => $date,
        "#required" => FALSE,
        "#date_time_required" => FALSE,
      ],
    ];
    $this->assertEquals($expected, $ui_handler->applySchemaUi($form));

    // Test flexible datetime with date format 2020-05-11 15:06:39.000.
    $form['modified']['#default_value'] = '2020-05-11 15:06:39.000';
    $date = new DrupalDateTime('2020-05-11 15:06:39.000');
    $expected['modified']['#default_value'] = $date;
    $this->assertEquals($expected, $ui_handler->applySchemaUi($form));

    // Test flexible datetime with date format 2020-05-09.
    $form['modified']['#default_value'] = '2020-05-09';
    $date = new DrupalDateTime('2020-05-09');
    $expected['modified']['#default_value'] = $date;
    $this->assertEquals($expected, $ui_handler->applySchemaUi($form));

    // Test date_range.
    $ui_schema = json_decode('{"temporal":{"ui:options":{"widget":"date_range"}}}');
    $ui_handler->setSchemaUi($ui_schema);
    $form = [
      "temporal" => [
        "#type" => "textfield",
        "#title" => "Temporal Date Range",
        "#default_value" => '2020-05-11T15:06:39.000Z/2020-05-15T15:00:00.000Z',
        "#required" => FALSE,
      ],
    ];
    new DrupalDateTime('2020-05-11T15:06:39.000Z');
    $expected = [
      "temporal" => [
        "#type" => "date_range",
        "#title" => "Temporal Date Range",
        "#default_value" => '2020-05-11T15:06:39.000Z/2020-05-15T15:00:00.000Z',
        "#required" => FALSE,
      ],
    ];
    $this->assertEquals($expected, $ui_handler->applySchemaUi($form));

    // Test date range without default value.
    $form['temporal']['#default_value'] = NULL;
    $expected['temporal']['#default_value'] = '';
    $this->assertEquals($expected, $ui_handler->applySchemaUi($form));

    // Test dkan_uuid field with already existing value.
    $ui_schema = json_decode('{"identifier":{"ui:options":{"widget":"dkan_uuid"}}}');
    $ui_handler->setSchemaUi($ui_schema);
    $form = [
      'identifier' => [
        '#type' => 'textfield',
        '#title' => 'Identifier',
        '#description' => 'Some description',
        '#required' => TRUE,
        '#default_value' => 'cedcd327-4e5d-43f9-8eb1-c11850fa7c55',
      ],
    ];

    $expected = [
      'identifier' => [
        '#type' => 'textfield',
        '#title' => 'Identifier',
        '#description' => 'Some description',
        '#required' => TRUE,
        '#default_value' => 'cedcd327-4e5d-43f9-8eb1-c11850fa7c55',
        '#access' => FALSE,
      ],
    ];
    $this->assertEquals($expected, $ui_handler->applySchemaUi($form));

    // Test dkan_uuid field, adding new value.
    $ui_schema = json_decode('{"identifier":{"ui:options":{"widget":"dkan_uuid"}}}');
    $ui_handler->setSchemaUi($ui_schema);
    $form = [
      'identifier' => [
        '#type' => 'textfield',
        '#title' => 'Identifier',
        '#description' => 'Some description',
        '#required' => TRUE,
        '#default_value' => '',
      ],
    ];
    $form = $ui_handler->applySchemaUi($form);

    $this->assertNotEmpty($form['identifier']['#default_value']);

    // Test array field.
    $ui_schema = json_decode('{"references":{"ui:options":{"title":"Related documents","description":"Improved description"},"items":{"ui:options":{"title":"References","placeholder":"http://"}}}}');
    $ui_handler->setSchemaUi($ui_schema);
    $form = [
      'references' => [
        '#type' => 'fieldset',
        '#title' => 'References',
        '#description' => 'Some description',
        '#prefix' => '<div id="references-fieldset-wrapper">',
        '#suffix' => '</div>',
        '#tree' => TRUE,
        'references' => [
          0 => [
            '#type' => 'textfield',
            '#title' => 'Ref',
            '#default_value' => 'Transportation',
          ],
          1 => [
            '#type' => 'textfield',
            '#title' => 'Ref',
            '#default_value' => NULL,
          ],
        ],
        'array_actions' => [],
      ],
    ];

    $expected = [
      'references' => [
        '#type' => 'fieldset',
        '#title' => 'Related documents',
        '#description' => 'Improved description',
        '#description_display' => 'before',
        '#prefix' => '<div id="references-fieldset-wrapper">',
        '#suffix' => '</div>',
        '#tree' => TRUE,
        'references' => [
          0 => [
            '#type' => 'textfield',
            '#title' => 'References',
            '#default_value' => 'Transportation',
            '#attributes' => [
              'placeholder' => 'http://',
            ],
          ],
          1 => [
            '#type' => 'textfield',
            '#title' => 'References',
            '#default_value' => NULL,
            '#attributes' => [
              'placeholder' => 'http://',
            ],
          ],
        ],
        'array_actions' => [],
      ],
    ];
    $this->assertEquals($expected, $ui_handler->applySchemaUi($form));

    // Test object field.
    $ui_schema = json_decode('{"publisher":{"properties":{"@type":{"ui:options":{"widget":"hidden"}},"name":{"ui:options":{"description":"Better description"}}}}}');
    $ui_handler->setSchemaUi($ui_schema);
    $form = [
      'publisher' => [
        'publisher' => [
          '#type' => 'details',
          "#open" => TRUE,
          '#title' => 'Object title',
          '@type' => [
            '#type' => 'textfield',
            '#title' => 'Metadata context',
            '#description' => 'Some description',
            '#default_value' => 'org:Organization',
            '#required' => FALSE,
          ],
          'name' => [
            '#type' => 'textfield',
            '#title' => 'Publisher name',
            '#description' => 'Some description',
            '#default_value' => 'org:Organization',
            '#required' => TRUE,
          ],
        ],
      ]
    ];

    $expected = [
      'publisher' => [
        'publisher' => [
          '#type' => 'details',
          "#open" => TRUE,
          '#title' => 'Object title',
          '@type' => [
            '#type' => 'textfield',
            '#title' => 'Metadata context',
            '#description' => 'Some description',
            '#default_value' => 'org:Organization',
            '#required' => FALSE,
            '#access' => FALSE,
          ],
          'name' => [
            '#type' => 'textfield',
            '#title' => 'Publisher name',
            '#description' => 'Better description',
            '#description_display' => 'before',
            '#default_value' => 'org:Organization',
            '#required' => TRUE,
          ],
        ],
      ],
    ];
    $this->assertEquals($expected, $ui_handler->applySchemaUi($form));

    // Test array field with object.
    $ui_schema = json_decode('{"distribution":{"items":{"@type":{"ui:options":{"widget":"hidden"}}}}}');
    $ui_handler->setSchemaUi($ui_schema);
    $form = [
      'distribution' => [
        '#type' => 'fieldset',
        '#title' => 'Distribution',
        '#description' => 'Some description',
        '#prefix' => '<div id="references-fieldset-wrapper">',
        '#suffix' => '</div>',
        '#tree' => TRUE,
        'distribution' => [
          0 => [
            'distribution' => [
              '#type' => 'details',
              '#open' => TRUE,
              '#title' => 'Item',
              '@type' => [
                '#type' => 'textfield',
                '#title' => 'Type',
                '#default_value' => 'dcat:Distribution',
                '#required' => FALSE,
              ],
              'name' => [
                '#type' => 'textfield',
                '#title' => 'Name',
                '#required' => TRUE,
              ],
            ],
          ],
          1 => [
            'distribution' => [
              '#type' => 'details',
              '#open' => TRUE,
              '#title' => 'Item',
              '@type' => [
                '#type' => 'textfield',
                '#title' => 'Type',
                '#default_value' => 'dcat:Distribution',
                '#required' => FALSE,
              ],
              'name' => [
                '#type' => 'textfield',
                '#title' => 'Name',
                '#required' => TRUE,
              ]
            ],
          ],
        ],
        'actions' => [],
      ],
    ];

    $expected = [
      'distribution' => [
        '#type' => 'fieldset',
        '#title' => 'Distribution',
        '#description' => 'Some description',
        '#prefix' => '<div id="references-fieldset-wrapper">',
        '#suffix' => '</div>',
        '#tree' => TRUE,
        'distribution' => [
          0 => [
            'distribution' => [
              '#type' => 'details',
              '#open' => TRUE,
              '#title' => 'Item',
              '@type' => [
                '#type' => 'textfield',
                '#title' => 'Type',
                '#default_value' => 'dcat:Distribution',
                '#required' => FALSE,
                '#access' => FALSE,
              ],
              'name' => [
                '#type' => 'textfield',
                '#title' => 'Name',
                '#required' => TRUE,
              ]
            ],
          ],
          1 => [
            'distribution' => [
              '#type' => 'details',
              '#open' => TRUE,
              '#title' => 'Item',
              '@type' => [
                '#type' => 'textfield',
                '#title' => 'Type',
                '#default_value' => 'dcat:Distribution',
                '#required' => FALSE,
                '#access' => FALSE,
              ],
              'name' => [
                '#type' => 'textfield',
                '#title' => 'Name',
                '#required' => TRUE,
              ]
            ],
          ],
        ],
        'actions' => [],
      ],
    ];

    $this->assertEquals($expected, $ui_handler->applySchemaUi($form));

    // Test upload_or_link widget.
    $ui_schema = json_decode('{"downloadURL":{"ui:options":{"widget":"upload_or_link", "extensions": "jpg pdf png csv", "progress_indicator": "bar"}}}');
    $ui_handler->setSchemaUi($ui_schema);
    $form = [
      'downloadURL' => [
        '#type' => 'string',
        '#title' => 'Download URL',
        '#description' => 'Some description',
        '#required' => FALSE,
        '#default_value' => 'https://url.to.api.or.file',
      ],
    ];
    $expected = [
      'downloadURL' => [
        '#type' => 'upload_or_link',
        '#title' => 'Download URL',
        '#description' => 'Some description',
        '#required' => FALSE,
        '#uri' => 'https://url.to.api.or.file',
        '#upload_location' => 'public://uploaded_resources',
        '#upload_validators' => [
          'FileExtension' => ['extensions' => 'jpg pdf png csv'],
        ],
        '#progress_indicator' => 'bar',
      ],
    ];
    $form = $ui_handler->applySchemaUi($form);

    $this->assertEquals($form, $expected);

    // Test list with select widget.
    $ui_schema = json_decode('{"format": {
        "ui:options": {
          "widget": "list",
          "type": "select",
          "source": {
            "enum": ["arcgis","csv"]
          }
        }
      }}');
    $ui_handler->setSchemaUi($ui_schema);
    $form = [
      'format' => [
        '#type' => 'string',
        '#title' => 'Format',
        '#description' => 'Some description',
        '#required' => FALSE,
        '#default_value' => 'csv',
      ],
    ];
    $expected = [
      'format' => [
        '#type' => 'select',
        '#title' => 'Format',
        '#description' => 'Some description',
        '#required' => FALSE,
        '#options' => [
          'arcgis' => 'arcgis',
          'csv' => 'csv',
        ],
        '#other_option' => '',
        '#default_value' => 'csv',
      ],
    ];
    $form = $ui_handler->applySchemaUi($form);

    $this->assertEquals($form, $expected);

    // Test list with select other widget.
    $ui_schema = json_decode('{"format": {
        "ui:options": {
          "widget": "list",
          "type": "select_other",
          "other_type": "textfield",
          "source": {
            "enum": ["arcgis","csv"]
          }
        }
      }}');
    $ui_handler->setSchemaUi($ui_schema);
    $form = [
      'format' => [
        '#type' => 'string',
        '#title' => 'Format',
        '#description' => 'Some description',
        '#required' => FALSE,
        '#default_value' => 'https://url.to.api.or.file',
      ],
    ];
    $expected = [
      'format' => [
        '#type' => 'select_or_other_select',
        '#title' => 'Format',
        '#description' => 'Some description',
        '#required' => FALSE,
        '#options' => [
          'arcgis' => 'arcgis',
          'csv' => 'csv',
          'https://url.to.api.or.file' => 'https://url.to.api.or.file',
        ],
        '#default_value' => 'https://url.to.api.or.file',
        '#input_type' => 'textfield',
        '#other_option' => '',
      ],
    ];
    $form = $ui_handler->applySchemaUi($form);

    $this->assertEquals($expected, $form);
  }

  /**
   * Test autocomplete in simple elements.
   */
  public function testAutocompleteOnSimple() {
    // Test options with autocomplete widget and options from metastore.
    $widget_router = $this->getRouter();
    $options = (new Options())
      ->add('json_form.string_helper', StringHelper::class)
      ->add('dkan.json_form.logger_channel', LoggerInterface::class)
      ->add('uuid', Php::class)
      ->add('json_form.widget_router', $widget_router)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options);
    $container = $container_chain->getMock();
    $ui_handler = SchemaUiHandler::create($container);

    $ui_schema = json_decode('{"tags": {
        "ui:options": {
          "widget": "list",
          "type": "autocomplete",
          "allowCreate": "true",
          "multiple": "true",
          "source": {
            "plugin": "taxonomy",
            "config": {
              "vocabulary": "test_vocabulary"
            }
          }
        }
      }}');
    $ui_handler->setSchemaUi($ui_schema);
    $form = [
      'tags' => [
        '#type' => 'string',
        '#title' => 'Tags',
        '#description' => 'Some description',
        '#required' => FALSE,
      ],
    ];
    $expected = [
      'tags' => [
        '#type' => 'select2',
        '#title' => 'Tags',
        '#description' => 'Some description',
        '#required' => FALSE,
        '#options' => [
          'Term 1' => 'Term 1',
          'Term 2' => 'Term 2',
          'Term 3' => 'Term 3',
        ],
        '#other_option' => FALSE,
        '#multiple' => TRUE,
        '#autocreate' => TRUE,
        '#target_type' => 'taxonomy_term',
      ],
    ];
    $form = $ui_handler->applySchemaUi($form);

    $this->assertEquals($expected, $form);
  }

  /**
   * Test actions are hidden.
   */
  public function testAutocompleteHideActions() {
    // Test options with autocomplete widget and options from metastore.
    $widget_router = $this->getRouter();
    $options = (new Options())
      ->add('json_form.string_helper', StringHelper::class)
      ->add('dkan.json_form.logger_channel', LoggerInterface::class)
      ->add('uuid', Php::class)
      ->add('json_form.widget_router', $widget_router)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options);
    $container = $container_chain->getMock();
    $ui_handler = SchemaUiHandler::create($container);

    $ui_schema = json_decode('{"theme": {
        "ui:options": {
          "hideActions": "true",
          "child": "theme"
        },
        "items": {
          "ui:options": {
            "widget": "list",
            "type": "autocomplete",
            "allowCreate": "true",
            "multiple": "true",
            "source": {
              "plugin": "taxonomy",
              "config": {
                "vocabulary": "test_vocabulary"
              }
            }
          }
        }
      }}');
    $ui_handler->setSchemaUi($ui_schema);
    $form = [
      'theme' => [
        '#type' => 'fieldset',
        '#title' => 'Topic',
        '#prefix' => '<div id="theme-fieldset-wrapper">',
        '#suffix' => '</div>',
        "#tree" => TRUE,
        '#description' => 'Some description',
        'theme' => [
          0 => [
            '#type' => 'textfield',
            '#title' => 'Topic',
            '#default_value' => 'Test',
          ],
          1 => [
            '#type' => 'textfield',
            '#title' => 'Topic',
            '#default_value' => 'Test 2',
          ],
        ],
        'array_actions' => [
          '#type' => 'actions',
          'actions' => ['add' => []],
        ],
      ],
    ];
    $expected = [
      'theme' => [
        '#type' => 'fieldset',
        '#title' => 'Topic',
        '#prefix' => '<div id="theme-fieldset-wrapper">',
        '#suffix' => '</div>',
        '#tree' => TRUE,
        '#description' => 'Some description',
        'theme' => [
          0 => [
            '#type' => 'select2',
            '#title' => 'Topic',
            '#options' => [
              'Term 1' => 'Term 1',
              'Term 2' => 'Term 2',
              'Term 3' => 'Term 3',
            ],
            '#other_option' => FALSE,
            '#multiple' => TRUE,
            '#autocreate' => TRUE,
            '#target_type' => 'taxonomy_term',
            '#default_value' => [
              'Test' => 'Test',
              'Test 2' => 'Test 2',
            ],
          ],
        ],
      ],
    ];
    $form = $ui_handler->applySchemaUi($form);

    $this->assertEquals($expected, $form);

    // Test with no default value.
    $form = [
      'theme' => [
        '#type' => 'fieldset',
        '#title' => 'Topic',
        '#prefix' => '<div id="theme-fieldset-wrapper">',
        '#suffix' => '</div>',
        "#tree" => TRUE,
        '#description' => 'Some description',
        'theme' => [
          0 => [
            '#type' => 'textfield',
            '#title' => 'Topic',
            '#default_value' => NULL,
          ],
        ],
        'array_actions' => [
          '#type' => 'actions',
          'actions' => ['add' => []],
        ],
      ],
    ];
    $expected = [
      'theme' => [
        '#type' => 'fieldset',
        '#title' => 'Topic',
        '#prefix' => '<div id="theme-fieldset-wrapper">',
        '#suffix' => '</div>',
        '#tree' => TRUE,
        '#description' => 'Some description',
        'theme' => [
          0 => [
            '#type' => 'select2',
            '#title' => 'Topic',
            '#options' => [
              'Term 1' => 'Term 1',
              'Term 2' => 'Term 2',
              'Term 3' => 'Term 3',
            ],
            '#other_option' => FALSE,
            '#multiple' => TRUE,
            '#autocreate' => TRUE,
            '#target_type' => 'taxonomy_term',
            '#default_value' => [],
          ],
        ],
      ],
    ];
    $form = $ui_handler->applySchemaUi($form);

    $this->assertEquals($form, $expected);
  }

  /**
   * Return WidgetRouter object.
   */
  private function getRouter() {
    $email_validator = new EmailValidator();
    $string_helper = new StringHelper($email_validator);

    $options = (new Options())
      ->add('json_form.string_helper', $string_helper)
      ->add('uuid', Php::class)
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('plugin.manager.json_form_option_source', JsonFormOptionSourcePluginManager::class)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(JsonFormOptionSourcePluginManager::class, 'createInstance', TaxonomySource::class)
      ->add(TaxonomySource::class, 'getEntityTypeManager', EntityTypeManager::class)
      ->add(EntityTypeManager::class, 'getStorage', (new Options())
        ->add('taxonomy_term', TermStorageInterface::class)
        ->index(0))
      ->add(TermStorageInterface::class, 'loadTree', WidgetRouterTest::terms());

    $container = $container_chain->getMock();
    return WidgetRouter::create($container);
  }

}
