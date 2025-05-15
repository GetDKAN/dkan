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
use Drupal\json_form_widget\WidgetRouter;
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
    $options = (new Options())
      ->add('dkan.metastore.schema_retriever', SchemaRetriever::class)
      ->add('json_form.string_helper', StringHelper::class)
      ->add('json_form.object_helper', ObjectHelper::class)
      ->add('json_form.array_helper', ArrayHelper::class)
      ->add('json_form.schema_ui_handler', SchemaUiHandler::class)
      ->add('dkan.json_form.logger_channel', LoggerInterface::class)
      ->add('json_form.router', FieldTypeRouter::class)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(SchemaRetriever::class, 'retrieve', '')
      ->add(SchemaUiHandler::class, 'setSchemaUi');

    $container = $container_chain->getMock();

    \Drupal::setContainer($container);

    $form_builder = FormBuilder::create($container);

    $form_builder->setSchema('dataset');
    $this->assertEquals($form_builder->getJsonForm([]), []);
  }

  /**
   * Basic schema test.
   */
  public function testSchema() {

    $container_chain = $this->getDetaultContainerChain()
      ->add(SchemaRetriever::class, 'retrieve', '
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
      }');

    $container = $container_chain->getMock();
    \Drupal::setContainer($container);

    $form_builder = FormBuilder::create($container);
    $form_builder->setSchema('dataset');
    $this->assertIsObject($form_builder->getSchema());
    $expected = [
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
    ];
    $default_data = new \stdClass();
    $default_data->test = "Some value.";
    $this->assertEquals($expected, $form_builder->getJsonForm($default_data));
  }

  public function testSchemaWithEmail() {
    // Test email.
    $container_chain = $this->getDetaultContainerChain()
      ->add(SchemaRetriever::class, 'retrieve', '
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
      }')
      ->add(SchemaUiHandler::class, 'setSchemaUi');

    $container = $container_chain->getMock();
    \Drupal::setContainer($container);

    $default_data = new \stdClass();
    $default_data->test = "Some value.";

    $form_builder = FormBuilder::create($container);
    $form_builder->setSchema('dataset');
    $result = $form_builder->getJsonForm($default_data);
    $this->assertEquals('email', $result["hasEmail"]['#type']);
    $this->assertArrayHasKey("#element_validate", $result["hasEmail"]);

    // Test object.
    $container_chain->add(SchemaRetriever::class, 'retrieve', '{"properties":{"publisher": {
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
    }},"type":"object"}');
    $container = $container_chain->getMock();
    \Drupal::setContainer($container);

    $form_builder = FormBuilder::create($container);
    $form_builder->setSchema('dataset');
    $expected = [
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
    ];
    $this->assertEquals($expected, $form_builder->getJsonForm([]));
  }

  /**
   * Test array with integer.
   */
  public function testSchemaWithArray() {
    $container_chain = $this->getDetaultContainerChain()
      ->add(SchemaRetriever::class, 'retrieve', '
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
        }'
      );
    $container = $container_chain->getMock();
    \Drupal::setContainer($container);

    $form_builder = FormBuilder::create($container);
    $form_builder->setSchema('dataset');
    $expected = [
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
            "#required" => FALSE,
            '#attributes' => [
              'class' => ['json-form-widget-array-item'],
              'data-parent' => 'keyword',
            ],
            "field" => [
              '#type' => 'textfield',
              '#title' => 'Tag',
            ],
          ],
        ],
        '#required' => FALSE,
      ],
    ];
    $form_state = new FormState();
    $form_state->set(
      ArrayHelper::buildStateProperty(ArrayHelper::STATE_PROP_COUNT, 'keyword'),
      1
    );
    $result = $form_builder->getJsonForm([], $form_state);
    // The actions are too complex to deal with in the $expected array, we just
    // assert the count is correct then remove them.
    $this->assertCount(1, $result['keyword']['keyword']);
    $this->assertCount(1, $result['keyword']['keyword']);
    unset($result['keyword']['array_actions'], $result['keyword']['keyword'][0]['actions']);
    $this->assertEquals($expected, $result);
  }

  /**
   * Test array required.
   */
  public function testArrayRequired() {
    $container_chain = $this->getDetaultContainerChain();
    $container_chain->add(SchemaRetriever::class, 'retrieve', '
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
    }');
    $container = $container_chain->getMock();
    \Drupal::setContainer($container);

    $form_builder = FormBuilder::create($container);
    $form_builder->setSchema('dataset');
    $expected = [
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
              '#type' => 'textfield',
              '#title' => 'Tag',
            ],
            '#attributes' => [
              'class' => ['json-form-widget-array-item'],
              'data-parent' => 'keyword',
            ],
          ],
        ],
        '#required' => TRUE,
      ],
    ];
    $form_state = new FormState();
    $result = $form_builder->getJsonForm([], $form_state);
    $this->assertCount(1, $result['keyword']['keyword']);
    $this->assertCount(1, $result['keyword']['keyword']);
    unset($result['keyword']['array_actions'], $result['keyword']['keyword'][0]['actions']);
    $this->assertEquals($expected, $result);
  }

  /**
   * Test schema with array of objects.
   */
  public function testSchemaWithArrayOfObjects() {
    $container_chain = $this->getDetaultContainerChain()
      ->add(SchemaRetriever::class, 'retrieve', '
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
      }');

    $container = $container_chain->getMock();
    \Drupal::setContainer($container);

    $form_builder = FormBuilder::create($container);
    $form_builder->setSchema('dataset');
    $expected = [
      "contributors" => [
        "#type" => "fieldset",
        "#title" => "Resources",
        "#description" => "List of links.",
        "#tree" => TRUE,
        '#required' => FALSE,
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
      ],
    ];
    $form_state = new FormState();
    $form_state->set(
      ArrayHelper::buildStateProperty(ArrayHelper::STATE_PROP_COUNT, 'contributors'),
      1
    );
    $result = $form_builder->getJsonForm([], $form_state);
    unset($result['contributors']['array_actions'], $result['contributors']['contributors'][0]['contributors']['actions']);
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
      ->add(Container::class, 'get', $options)
      ->add(SchemaUiHandler::class, 'setSchemaUi');
  }

  /**
   * Test schema field ordering by weight.
   */
  public function testSchemaFieldWeightOrdering() {
    $base_schema = '{
      "properties": {
        "first":  { "type": "string" },
        "second": { "type": "string" },
        "third":  { "type": "string" }
      },
      "type": "object"
    }';
    $ui_schema = '{
      "first":  { "ui:options": { "weight": 10 } },
      "second": { "ui:options": { "weight": -10 } },
      "third":  { "ui:options": { "weight": 0 } }
    }';

    $schema_retriever = $this->createMock(SchemaRetriever::class);
    $schema_retriever
      ->method('retrieve')
      ->willReturnCallback(function ($name) use ($base_schema, $ui_schema) {
        return $name === 'dataset' ? $base_schema : $ui_schema;
      });

    $logger = $this->createStub(LoggerInterface::class);
    $schema_ui_handler = new SchemaUiHandler(
      $schema_retriever,
      $logger,
      $this->createStub(WidgetRouter::class)
    );

    $router = $this->getRouter();

    $options = (new Options())
      ->add('dkan.metastore.schema_retriever', $schema_retriever)
      ->add('json_form.router', $router)
      ->add('json_form.schema_ui_handler', $schema_ui_handler)
      ->add('dkan.json_form.logger_channel', $logger)
      ->index(0);

    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(SchemaUiHandler::class, 'setSchemaUi')
      ->getMock();

    \Drupal::setContainer($container);

    $form_builder = FormBuilder::create($container);
    $form_builder->setSchema('dataset');
    $form = $form_builder->getJsonForm([]);

    $ordered_keys = array_keys($form);
    $expected_order = ['second', 'third', 'first'];
    $this->assertEquals($expected_order, $ordered_keys);
  }
}
