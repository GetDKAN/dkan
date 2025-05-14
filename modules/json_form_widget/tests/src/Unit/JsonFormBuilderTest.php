<?php

namespace Drupal\Tests\json_form_widget\Unit;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Component\Utility\EmailValidator;
use Drupal\Core\Form\FormState;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\json_form_widget\ArrayHelper;
use Drupal\json_form_widget\FieldTypeRouter;
use Drupal\json_form_widget\FormBuilder;
use Drupal\json_form_widget\IntegerHelper;
use Drupal\json_form_widget\ObjectHelper;
use Drupal\json_form_widget\SchemaUiHandler;
use Drupal\json_form_widget\StringHelper;
use Drupal\metastore\SchemaRetriever;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test class for JsonFormWidget.
 *
 * @group dkan
 * @group json_form_widget
 * @group unit
 */
class JsonFormBuilderTest extends TestCase {

  protected FieldTypeRouter $router;

  public function setUp(): void {
    parent::setUp();
    $this->router = $this->getRouter();
  }

  /**
   * Test.
   */
  public function testNoSchema() {
    $container = $this->getDetaultContainerChain()->getMock();
    $form_builder = FormBuilder::create($container);

    $form_builder->setSchema((object) []);
    $this->assertEquals($form_builder->getJsonForm([]), []);
  }

  /**
   * Basic schema test.
   *
   * @param object $schema
   *   JSON schema.
   * @param object $ui_schema
   *   UI schema.
   * @param object $default_data
   *   Default data for form.
   * @param array $expected
   *   Expected result from form builder.
   * @param array $count
   *   Count property and value for ArrayHelper. Should be associative array
   *   with property name as key and count value as value. (e.g.
   *   ['keyword' => 1]).
   *
   * @dataProvider schemaProvider
   */
  public function testSchema(
    object $schema,
    ?object $ui_schema,
    ?object $default_data,
    array $expected,
    array $count = [],
  ) {
    $container = $this->getDetaultContainerChain()->getMock();
    $form_builder = FormBuilder::create($container);
    $form_builder->setSchema($schema, $ui_schema);

    $form_state = new FormState();
    foreach ($count as $property => $value) {
      $form_state->set(
        ArrayHelper::buildStateProperty(ArrayHelper::STATE_PROP_COUNT, $property), 
        $value
      );
    }

    $this->assertIsObject($form_builder->getSchema());
    $result = $form_builder->getJsonForm($default_data, $form_state);

    // Eliminate validation and actions elements to make comparison easier.
    foreach ($result as $field_key => $field_value) {
      unset(
        $result[$field_key]['#element_validate'],
        $result[$field_key][$field_key][0]['actions'],
        $result[$field_key][$field_key][0][$field_key]['actions'],
        $result[$field_key][$field_key][0]['#attributes'],
        $result[$field_key]['array_actions']
      );
    }

    $this->assertEquals($expected, $result);
  }

  /**
   * Return FieldTypeRouter object.
   */
  private function getRouter() {
    $email_validator = new EmailValidator();
    $string_helper = new StringHelper($email_validator);
    $object_helper = new ObjectHelper();
    $array_helper = new ArrayHelper($object_helper, $string_helper);
    $integer_helper = new IntegerHelper();

    $options = (new Options())
      ->add('json_form.string_helper', $string_helper)
      ->add('json_form.object_helper', $object_helper)
      ->add('json_form.array_helper', $array_helper)
      ->add('json_form.integer_helper', $integer_helper)
      ->add('string_translation', TranslationManager::class)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options);

    $container = $container_chain->getMock();
    \Drupal::setContainer($container);
    return FieldTypeRouter::create($container);
  }

  /**
   * Get the default container chain.
   */
  protected function getDetaultContainerChain(): Chain {
    $options = (new Options())
      ->add('dkan.metastore.schema_retriever', SchemaRetriever::class)
      ->add('json_form.router', $this->getRouter())
      ->add('json_form.schema_ui_handler', SchemaUiHandler::class)
      ->add('dkan.json_form.logger_channel', LoggerInterface::class)
      ->add('string_translation', TranslationManager::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $options);
  }

  /**
   * Data provider for testSchema.
   *
   * (Probably want to use folding to make sense of this!)
   */
  public static function schemaProvider() {
    return [
      'basic' => [
        'schema' => json_decode('
          {
            "required": [
              "accessLevel"
            ],
            "properties":{
              "title":{
                "type":"string",
                "title":"Title field"
              },
              "test":{
                "type":"string",
                "title":"Test field",
                "maxLength":400
              },
              "downloadURL":{
                "title":"Download URL",
                "description":"This is an URL field.",
                "type":"string",
                "format":"uri"
              },
              "accessLevel": {
                "description": "Description.",
                "title": "Public Access Level",
                "type": "string",
                "enum": [
                  "public",
                  "restricted public",
                  "non-public"
                ],
                "default": "public"
              },
              "accrualPeriodicity": {
                "title": "Frequency",
                "description": "Description.",
                "type": "string",
                "enum": [
                  "R/P10Y",
                  "R/P4Y"
                ],
                "enumNames": [
                  "Decennial",
                  "Quadrennial"
                ]
              }
            },
            "type":"object"
          }'
        ),
        'ui_schema' => NULL,
        'default_data' => (object) [
          'test' => "Some value.",
        ],
        'expected' => [
          "title" => [
            "#type" => "textfield",
            "#title" => "Title field",
            "#description" => "",
            "#default_value" => NULL,
            '#description_display' => 'before',
            "#required" => FALSE,
            "#maxlength" => 256,
          ],
          "test" => [
            "#type" => "textfield",
            "#title" => "Test field",
            "#description" => "",
            "#default_value" => "Some value.",
            '#description_display' => 'before',
            "#required" => FALSE,
            "#maxlength" => 400,
          ],
          "downloadURL" => [
            "#type" => "url",
            "#title" => "Download URL",
            "#description" => "This is an URL field.",
            '#description_display' => 'before',
            "#default_value" => NULL,
            "#required" => FALSE,
          ],
          "accessLevel" => [
            "#type" => "select",
            "#title" => "Public Access Level",
            "#description" => "Description.",
            '#description_display' => 'before',
            "#default_value" => "public",
            "#required" => TRUE,
            "#options" => [
              "public" => "public",
              "restricted public" => "restricted public",
              "non-public" => "non-public",
            ],
          ],
          "accrualPeriodicity" => [
            "#type" => "select",
            "#title" => "Frequency",
            "#description" => "Description.",
            '#description_display' => 'before',
            "#default_value" => NULL,
            "#required" => FALSE,
            "#options" => [
              "R/P10Y" => "Decennial",
              "R/P4Y" => "Quadrennial",
            ],
            "#empty_value" => '',
          ],
        ],
      ],
      'withEmail' => [
        'schema' => json_decode('
          {
            "properties":{
              "hasEmail": {
                "title": "Email",
                "description": "Email address for the contact name.",
                "pattern": "^mailto:",
                "type": "string"
              }
            },
            "type":"object"
          }'
        ),
        'ui_schema' => NULL,
        'default_data' => (object) [
          'hasEmail' => "Some value.",
        ],
        'expected' => [
          "hasEmail" => [
            "#type" => "email",
            "#title" => "Email",
            "#description" => "Email address for the contact name.",
            '#description_display' => 'before',
            "#default_value" => "Some value.",
            "#required" => FALSE,
          ],
        ],
      ],
      'withObject' => [
        'schema' => json_decode('
          {
            "properties": {
              "publisher": {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "id": "https://project-open-data.cio.gov/v1.1/schema/organization.json#",
                "title": "Organization",
                "description": "A Dataset Publisher Organization.",
                "type": "object",
                "required": [
                  "name"
                ],
                "properties": {
                  "@type": {
                    "title": "Metadata Context",
                    "description": "IRI for the JSON-LD data type. This should be org:Organization for each publisher",
                    "type": "string",
                    "default": "org:Organization"
                  },
                  "name": {
                    "title": "Publisher Name",
                    "description": "",
                    "type": "string",
                    "minLength": 1
                  }
                }
              }
            },
            "type": "object"
          }
        '),
        'ui_schema' => NULL,
        'default_data' => NULL,
        'expected' => [
          "publisher" => [
            "publisher" => [
              "#type" => "details",
              "#open" => TRUE,
              "#title" => "Organization",
              "#description" => "A Dataset Publisher Organization.",
              '#description_display' => 'before',
              "@type" => [
                "#type" => "textfield",
                "#title" => "Metadata Context",
                "#description" => "IRI for the JSON-LD data type. This should be org:Organization for each publisher",
                '#description_display' => 'before',
                "#default_value" => "org:Organization",
                "#required" => FALSE,
                '#maxlength' => 256,
              ],
              "name" => [
                "#type" => "textfield",
                "#title" => "Publisher Name",
                "#description" => "",
                '#description_display' => 'before',
                "#default_value" => NULL,
                "#required" => TRUE,
                '#maxlength' => 256,
              ],
            ],
          ],
        ],
      ],
      'withArray' => [
        'schema' => json_decode('
          {
            "properties":{
              "keyword": {
                "title": "Tags",
                "description": "Tags (or keywords).",
                "type": "array",
                "items": {
                  "type": "string",
                  "title": "Tag"
                }
              }
            },
            "type":"object"
          }        
        '),
        'ui_schema' => NULL,
        'default_data' => NULL,
        'expected' => [
          "keyword" => [
            "#type" => "fieldset",
            "#title" => "Tags",
            "#tree" => TRUE,
            "#description" => "Tags (or keywords).",
            "#description_display" => "before",
            "#prefix" => '<div id="keyword-fieldset-wrapper">',
            "#suffix" => '</div>',
            "keyword" => [
              0 => [
                "#type" => "fieldset",
                'field' => [
                  "#type" => "textfield",
                  "#title" => "Tag",
                ],
                '#required' => FALSE,
              ],
            ],
            "#required" => FALSE,
          ],
        ],
        'count' => [
          'keyword' => 1,
        ],
      ],
      'withArrayOfObjects' => [
        'schema' => json_decode('
          {
            "properties": {
              "contributors": {
                "title": "Resources",
                "description": "List of links.",
                "type": "array",
                "items": {
                  "type": "object",
                  "properties": {
                    "name": {
                      "type": "string",
                      "title": "Name"
                    },
                    "url": {
                      "type": "string",
                      "format": "uri",
                      "title": "URL",
                      "default": "http://example.com"
                    }
                  }
                }
              }
            },
            "type": "object"
          }
        '),
        'ui_schema' => NULL,
        'default_data' => NULL,
        'expected' => [
          "contributors" => [
            "#type" => "fieldset",
            "#title" => "Resources",
            "#description" => "List of links.",
            "#tree" => TRUE,
            "#description_display" => "before",
            "#prefix" => '<div id="contributors-fieldset-wrapper">',
            "#suffix" => '</div>',
            "contributors" => [
              0 => [
                'contributors' => [
                  "#type" => "details",
                  "#open" => TRUE,
                  '#description_display' => 'before',
                  "name" => [
                    "#type" => "textfield",
                    "#title" => "Name",
                    "#required" => FALSE,
                    "#description" => "",
                    '#description_display' => 'before',
                    '#default_value' => NULL,
                    '#maxlength' => 256,
                  ],
                  "url" => [
                    "#type" => "url",
                    "#title" => "URL",
                    "#required" => FALSE,
                    "#description" => "",
                    '#description_display' => 'before',
                    '#default_value' => "http://example.com",
                  ],
                ],
                '#required' => FALSE,
              ],
            ],
            "#required" => FALSE,
          ],
        ],
        'count' => [
          'contributors' => 1,
        ],
      ],
      'arrayRequired' => [
        'schema' => json_decode('
          {
            "required": [
              "keyword"
            ],
            "properties":{
              "keyword": {
                "title": "Tags",
                "description": "Tags (or keywords).",
                "type": "array",
                "items": {
                  "type": "string",
                  "title": "Tag"
                },
                "minItems": 1
              }
            },
            "type":"object"
          }'
        ),
        'ui_schema' => NULL,
        'default_data' => NULL,
        'expected' => [
          "keyword" => [
            "#type" => "fieldset",
            "#title" => "Tags",
            "#prefix" => '<div id="keyword-fieldset-wrapper">',
            "#suffix" => "</div>",
            "#tree" => TRUE,
            "#description" => "Tags (or keywords).",
            '#description_display' => 'before',
            "keyword" => [
              0 => [
                "#type" => "fieldset",
                "#required" => TRUE,
                'field' => [
                  "#type" => "textfield",
                  "#title" => "Tag",
                ],
              ],
            ],
            '#required' => TRUE,
          ],
        ],
      ],
      'weights' => [
        'schema' => json_decode('
          {
            "properties": {
              "first":  { "type": "string", "title": "First" },
              "second": { "type": "string", "title": "Second" },
              "third":  { "type": "string", "title": "Third" }
            },
            "type": "object"
          }'
        ),
        'ui_schema' => json_decode('
          {
            "first":  { "ui:options": { "weight": 10 } },
            "second": { "ui:options": { "weight": -10 } },
            "third":  { "ui:options": { "weight": 0 } }
          }'
        ),
        'default_data' => NULL,
        'expected' => [
          'second' => [
            '#type' => 'textfield',
            '#title' => 'Second',
            '#description' => '',
            '#description_display' => 'before',
            '#default_value' => NULL,
            '#required' => FALSE,
            '#maxlength' => 256,
          ],
          'third' => [
            '#type' => 'textfield',
            '#title' => 'Third',
            '#description' => '',
            '#description_display' => 'before',
            '#default_value' => NULL,
            '#required' => FALSE,
            '#maxlength' => 256,
          ],
          'first' => [
            '#type' => 'textfield',
            '#title' => 'First',
            '#description' => '',
            '#description_display' => 'before',
            '#default_value' => NULL,
            '#required' => FALSE,
            '#maxlength' => 256,
          ],
        ],
      ],
    ];
  }

}
