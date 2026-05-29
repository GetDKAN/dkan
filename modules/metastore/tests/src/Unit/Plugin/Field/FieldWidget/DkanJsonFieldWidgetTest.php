<?php

namespace Drupal\Tests\metastore\Unit\Field\FieldWidget;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Component\Uuid\Php;
use Drupal\json_form_widget\OptionSource\JsonFormOptionSourcePluginManager;
use Drupal\json_form_widget\SchemaUiHandler;
use Drupal\json_form_widget\StringHelper;
use PHPUnit\Framework\TestCase;
use Drupal\json_form_widget\WidgetRouter;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\Plugin\JsonFormOptionSource\MetastoreSchema;
use MockChain\Chain;
use MockChain\Options;
use Psr\Log\LoggerInterface;

/**
 * Test class for ValueHandlerTest.
 *
 * @group metastore
 * @coversDefaultClass \Drupal\metastore\WidgetRouter
 */
class DkanJsonFieldWidgetTest extends TestCase {

  /**
   * Some tests we inhereted from the original WidgetRouterTest.
   * @dataProvider dataProvider
   */
  public function testHandleListElement($spec, $element, $handledElement) {
    $router = WidgetRouter::create($this->getContainerChain()->getMock());

    $new_element = $router->getConfiguredWidget($spec, $element);

    $this->assertEquals($handledElement, $new_element);
  }

  private function getContainerChain() {
    $containerGetOptions = (new Options())
      ->add('uuid', Php::class)
      ->add('json_form.string_helper', StringHelper::class)
      ->add('dkan.metastore.service', MetastoreService::class)
      ->add('plugin.manager.json_form_option_source', JsonFormOptionSourcePluginManager::class)
      ->add('dkan.json_form.logger_channel', LoggerInterface::class)
      ->index(0);

    $metastoreGetAllOptions = (new Options())
      ->add('publisher', self::publishers())
      ->add('data-dictionary', self::dataDictionaries())
      ->add('theme', self::themes())
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $containerGetOptions)
      ->add(MetastoreService::class, 'getAll', $metastoreGetAllOptions)
      ->add(JsonFormOptionSourcePluginManager::class, 'createInstance', MetastoreSchema::class)
      ->add(MetastoreSchema::class, 'getMetastore', MetastoreService::class)
      ->add(MetastoreService::class, 'getAll', $metastoreGetAllOptions);
  }

  /**
   * Data provider.
   *
   * Each dataset gets is an array with three elements:
   * 1. The spec object.
   * 2. The element array.
   * 3. The expected handled element array.
   */
  public static function dataProvider(): array {
    return [
      'tagField' => [
        (object) [
          'widget' => 'list',
          'type' => 'autocomplete',
          'allowCreate' => TRUE,
          'multiple' => TRUE,
          'source' => (object) [
            'metastoreSchema' => 'theme',
          ],
        ],
        [
          '#type' => 'textfield',
          '#title' => 'tags',
        ],
        [
          '#type' => 'select2',
          '#title' => 'tags',
          '#options' => [
            'Theme 1' => 'Theme 1',
            'Theme 2' => 'Theme 2',
          ],
          '#other_option' => FALSE,
          '#multiple' => TRUE,
          '#autocreate' => TRUE,
          '#target_type' => 'node',
        ],
      ],
      // Publisher popualtes from metastore but returns whole object,
      // is wrapped in a details element.
      'publisherField' => [
        (object) [
          "widget" => "list",
          "type" => "autocomplete",
          "allowCreate" => TRUE,
          "titleProperty" => "name",
          "source" => (object) [
            "metastoreSchema" => "publisher",
          ],
        ],
        [
          '#type' => 'details',
          '#title' => 'Organization',
          'name' => [
            '#type' => 'textfield',
            '#title' => "Publisher Name",
            "#default_value" => NULL,
            "#required" => TRUE,
          ],
        ],
        [
          '#type' => 'details',
          '#title' => 'Organization',
          'name' => [
            '#type' => 'select2',
            '#title' => 'Publisher Name',
            '#default_value' => NULL,
            '#required' => TRUE,
            '#options' => [
              'Publisher 1' => 'Publisher 1',
              'Publisher 2' => 'Publisher 2',
            ],
            '#other_option' => FALSE,
            '#multiple' => FALSE,
            '#autocreate' => TRUE,
            '#target_type' => 'node',
          ],
        ],
      ],
      // Data dict field draws from metastore but just shows URLs.
      'dataDict' => [
        (object) [
          "widget" => "list",
          "type" => "select",
          "titleProperty" => "title",
          "source" => (object) [
            "metastoreSchema" => "data-dictionary",
            "returnValue" => "url",
          ],
        ],
        [
          '#type' => 'url',
          '#title' => 'Data Dictionary',
        ],
        [
          '#type' => 'select',
          '#title' => 'Data Dictionary',
          '#options' => [
            'dkan://metastore/schemas/data-dictionary/items/111' => 'Data dictionary 1',
            'dkan://metastore/schemas/data-dictionary/items/222' => 'Data dictionary 2',
          ],
          '#other_option' => FALSE,
        ],
      ],
    ];
  }

  public static function publishers() {
    return [
      json_encode((object) [
        'identifier' => '111',
        'data' => (object) [
          '@type' => 'org:Organization',
          'name' => 'Publisher 1',
        ],
      ]),
      json_encode((object) [
        'identifier' => '222',
        'data' => (object) [
          '@type' => 'org:Organization',
          'name' => 'Publisher 2',
        ],
      ]),
    ];
  }

  public static function dataDictionaries() {
    return [
      json_encode((object) [
        'identifier' => '111',
        'data' => (object) [
          'title' => 'Data dictionary 1',
        ],
      ]),
      json_encode((object) [
        'identifier' => '222',
        'data' => (object) [
          'title' => 'Data dictionary 2',
        ],
      ]),
    ];
  }

  public static function themes() {
    return [
      json_encode((object) [
        'identifier' => '111',
        'data' => 'Theme 1',
      ]),
      json_encode((object) [
        'identifier' => '222',
        'data' => 'Theme 2',
      ]),
    ];
  }

  /**
   * Test autocomplete on complex publisher elements.
   *
   * Inherited from SchemaUiHandlerTest::testAutocompleteOnComplex
   * before decoupling.
   */
  public function testAutocompleteOnPublisher() {
    // Test options with autocomplete widget, titleProperty and options from metastore.
    // $widget_router = $this->getRouter($results);
    $widget_router = WidgetRouter::create($this->getContainerChain()->getMock());
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

    $ui_schema = json_decode('{"publisher": {
      "ui:options": {
        "widget": "list",
        "type": "autocomplete",
        "titleProperty": "name",
        "allowCreate": "true",
        "multiple": "true",
        "source": {
          "metastoreSchema": "publisher"
        }
      }
    }}');
    $ui_handler->setSchemaUi($ui_schema);
    $form = [
      'publisher' => [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => 'Organization',
        '#description' => 'Some description',
        'name' => [
          '#type' => 'string',
          '#title' => 'Publisher',
          '#description' => 'Some description',
          '#required' => FALSE,
        ],
      ],
    ];
    $expected = [
      'publisher' => [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => 'Organization',
        '#description' => 'Some description',
        'name' => [
          '#type' => 'select2',
          '#title' => 'Publisher',
          '#description' => 'Some description',
          '#required' => FALSE,
          '#options' => [
            'Publisher 1' => 'Publisher 1',
            'Publisher 2' => 'Publisher 2',
          ],
          '#other_option' => '',
          '#multiple' => TRUE,
          '#autocreate' => TRUE,
          '#target_type' => 'node',
        ],
      ],
    ];
    $form = $ui_handler->applySchemaUi($form);

    $this->assertEquals($expected, $form);
  }

}
