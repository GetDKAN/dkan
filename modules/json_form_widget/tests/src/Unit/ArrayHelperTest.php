<?php

namespace Drupal\Tests\json_form_widget\Unit;

use PHPUnit\Framework\TestCase;
use Drupal\json_form_widget\ArrayHelper;
use MockChain\Chain;
use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Form\FormState;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\json_form_widget\FieldTypeRouter;
use Drupal\json_form_widget\IntegerHelper;
use Drupal\json_form_widget\ObjectHelper;
use Drupal\json_form_widget\SchemaUiHandler;
use Drupal\json_form_widget\StringHelper;
use Drupal\metastore\SchemaRetriever;
use MockChain\Options;

/**
 * Test class for ArrayHelper.
 */
class ArrayHelperTest extends TestCase {

  /**
   * Test complex arrays.
   */
  public function testComplex() {
    $object = $this->getExpectedObject();

    $options = (new Options())
      ->add('json_form.object_helper', ObjectHelper::class)
      ->index(0);
    $chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(ObjectHelper::class, 'handleObjectElement', $object)
      ->getMock();

    $array_helper = ArrayHelper::create($chain);
    $options = (new Options())
      ->add('dkan.metastore.schema_retriever', SchemaRetriever::class)
      ->add('json_form.router', FieldTypeRouter::class)
      ->add('json_form.string_helper', StringHelper::class)
      ->add('json_form.object_helper', ObjectHelper::class)
      ->add('json_form.array_helper', $array_helper)
      ->add('json_form.integer_helper', IntegerHelper::class)
      ->add('json_form.schema_ui_handler', SchemaUiHandler::class)
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('string_translation', TranslationManager::class)
      ->index(0);

    $distribution_schema = $this->getSchema();
    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(SchemaRetriever::class, 'retrieve', $distribution_schema)
      ->add(SchemaUiHandler::class, 'setSchemaUi')
      ->add(ObjectHelper::class, 'handleObjectElement', $object)
      ->add(ObjectHelper::class, 'setBuilder');

    $container = $container_chain->getMock();
    \Drupal::setContainer($container);

    $definition = [
      'name' => 'distribution',
      'schema' => json_decode($distribution_schema),
    ];
    $context = [$definition['name']];
    $context_name = ArrayHelper::buildContextName($context);
    $form_state = new FormState();
    $form_state->set(ArrayHelper::buildCountProperty($context_name), 1);
    $router = FieldTypeRouter::create($container);
    $router->setSchema(json_decode($distribution_schema));
    $array_helper->setBuilder($router);

    $result = $array_helper->handleArrayElement($definition, [], $form_state, $context);
    $expected = $this->getExpectedComplexArrayElement();
    unset($result['actions']);
    unset($result['distribution'][0]['distribution']['schema']['schema']['fields']['actions']);
    $this->assertEquals($expected, $result);
  }

  /**
   * Helper function to get schema to test.
   */
  private function getSchema() {
    $schema = '{
      "title": "Distribution",
      "description": "Description.",
      "type": "array",
      "items": {
        "title": "Project Open Data Distribution",
        "type": "object",
        "properties": {
          "@type": {
            "title": "Metadata Context",
            "description": "Test Description.",
            "default": "dcat:Distribution",
            "type": "string",
            "readOnly": true
          },
          "schema": {
            "title": "Schema",
            "description": "Test description.",
            "type": "object",
            "properties": {
              "fields": {
                "type": "array",
                "items": {
                  "title": "Table Schema Field",
                  "type": "object",
                  "properties": {
                    "name": {
                      "title": "Name",
                      "description": "A name for this field.",
                      "type": "string"
                    },
                    "title": {
                      "title": "Title",
                      "description": "A human-readable title.",
                      "type": "string"
                    }
                  }
                }
              }
            }
          }
        }
      }
    }';
    return $schema;
  }

  /**
   * Helper function to get expected object.
   */
  private function getExpectedObject() {
    return [
      "distribution" => [
        "#type" => "details",
        "#open" => TRUE,
        "#title" => "Project Open Data Distribution",
        "@type" => [
          "#type" => "textfield",
          "#title" => "Metadata Context",
          "#description" => "Test Description.",
          "#default_value" => "dcat:Distribution",
          "#required" => FALSE,
        ],
        "schema" => [
          "schema" => [
            "#type" => "details",
            "#open" => TRUE,
            "#title" => "Schema",
            "#description" => "Test description.",
            "fields" => [
              "#type" => "fieldset",
              "#title" => "fields",
              "#prefix" => "",
              "#suffix" => "",
              "#tree" => TRUE,
              "fields" => [
                0 => [
                  "fields" => [
                    "#type" => "details",
                    "#open" => TRUE,
                    "#title" => "Table Schema Field",
                    "name" => [
                      "#type" => "textfield",
                      "#title" => "Name",
                      "#description" => "A name for this field.",
                      "#default_value" => NULL,
                      "#required" => FALSE,
                    ],
                    "title" => [
                      "#type" => "textfield",
                      "#title" => "Title",
                      "#description" => "A human-readable title.",
                      "#default_value" => NULL,
                      "#required" => FALSE,
                    ],
                  ],
                ]
              ],
            ],
          ]
        ],
      ],
      "#required" => FALSE,
    ];
  }

  /**
   * Helper function to get the full expected complex element.
   */
  private function getExpectedComplexArrayElement() {
    return [
      '#type' => 'fieldset',
      '#title' => 'Distribution',
      '#tree' => TRUE,
      '#description' => 'Description.',
      '#description_display' => 'before',
      '#prefix' => '<div id="distribution-fieldset-wrapper">',
      '#suffix' => '</div>',
      'distribution' => [
        0 => $this->getExpectedObject(),
      ],
    ];
  }

}
