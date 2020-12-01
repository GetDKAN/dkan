<?php

namespace Drupal\Tests\json_form_widget\Unit;

use PHPUnit\Framework\TestCase;
use Drupal\json_form_widget\FormBuilder;
use Drupal\json_form_widget\ArrayHelper;
use MockChain\Chain;
use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\json_form_widget\ObjectHelper;
use Drupal\json_form_widget\SchemaUiHandler;
use Drupal\json_form_widget\StringHelper;
use Drupal\metastore\SchemaRetriever;
use MockChain\Options;

/**
 * Test class for JsonFormWidget.
 */
class JsonFormBuilderTest extends TestCase {

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
      ->add('logger.factory', LoggerChannelFactory::class)
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
   * Test.
   */
  public function testSchema() {
    $options = (new Options())
      ->add('dkan.metastore.schema_retriever', SchemaRetriever::class)
      ->add('json_form.string_helper', StringHelper::class)
      ->add('json_form.object_helper', ObjectHelper::class)
      ->add('json_form.array_helper', ArrayHelper::class)
      ->add('json_form.schema_ui_handler', SchemaUiHandler::class)
      ->add('logger.factory', LoggerChannelFactory::class)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(SchemaRetriever::class, 'retrieve', '{"properties":{"test":{"type":"string","title":"Test field"}},"type":"object"}')
      ->add(SchemaUiHandler::class, 'setSchemaUi');

    $container = $container_chain->getMock();

    \Drupal::setContainer($container);

    $form_builder = FormBuilder::create($container);

    $form_builder->setSchema('dataset');
    $expected = [
      "test" => [
        "#type" => "textfield",
        "#title" => "Test field",
        "#description" => "",
        "#default_value" => NULL,
        "#required" => FALSE,
      ]
    ];
    $this->assertEquals($form_builder->getJsonForm([]), $expected);
  }

}
