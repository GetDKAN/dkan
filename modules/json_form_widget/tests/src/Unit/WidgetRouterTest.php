<?php

namespace Drupal\Tests\json_form_widget\Unit;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Component\Uuid\Php;
use Drupal\json_form_widget\StringHelper;
use PHPUnit\Framework\TestCase;
use Drupal\json_form_widget\WidgetRouter;
use Drupal\metastore\MetastoreService;
use MockChain\Chain;
use MockChain\Options;

/**
 * Test class for ValueHandlerTest.
 */
class WidgetRouterTest extends TestCase {

  /**
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
      ->index(0);

    $metastoreGetAllOptions = (new Options())
      ->add('publisher', self::publishers())
      ->add('data-dictionary', self::dataDictionaries())
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $containerGetOptions)
      ->add(MetastoreService::class, 'getAll', $metastoreGetAllOptions);
  }

  public static function dataProvider(): array {
    return [
      // Tag field is a free-tagging autocomplete that populates from metastore.
      'tagField' => [
        (object) [
          'widget' => 'list',
          'type' => 'autocomplete',
          'allowComplete' => TRUE,
          'multiple' => TRUE,
          'source' => [
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
          '#options' => [],
          '#other_option' => FALSE,
          '#multiple' => TRUE,
          '#autocreate' => FALSE,
          '#target_type' => 'node',
        ],
      ],
      // Format is a simple select field with values defined in UI schema.
      'formatField' => [
        (object) [
          "title" => "File Format",
          "widget" => "list",
          "type" => "select_other",
          "other_type" => "textfield",
          "source" => (object) [
            "enum" => [
              "csv",
              "json",
            ],
          ],
        ],
        [
          '#type' => 'textfield',
          '#title' => 'File Format',
        ],
        [
          '#type' => 'select_or_other_select',
          '#title' => 'File Format',
          '#options' => [
            'csv' => 'csv',
            'json' => 'json',
          ],
          '#other_option' => FALSE,
          '#input_type' => 'textfield',
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


}
