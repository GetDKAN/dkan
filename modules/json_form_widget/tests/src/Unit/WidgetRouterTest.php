<?php

namespace Drupal\Tests\json_form_widget\Unit;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Component\Uuid\Php;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\json_form_widget\OptionSource\JsonFormOptionSourcePluginManager;
use Drupal\json_form_widget\Plugin\JsonFormOptionSource\TaxonomySource;
use Drupal\json_form_widget\StringHelper;
use PHPUnit\Framework\TestCase;
use Drupal\json_form_widget\WidgetRouter;
use Drupal\taxonomy\TermStorageInterface;
use MockChain\Chain;
use MockChain\Options;

/**
 * Test class for ValueHandlerTest.
 *
 * @group json_form_widget
 * @coversDefaultClass \Drupal\json_form_widget\WidgetRouter
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
      ->add('plugin.manager.json_form_option_source', JsonFormOptionSourcePluginManager::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $containerGetOptions)
      ->add(JsonFormOptionSourcePluginManager::class, 'createInstance', TaxonomySource::class)
      ->add(TaxonomySource::class, 'getEntityTypeManager', EntityTypeManager::class)
      ->add(EntityTypeManager::class, 'getStorage', (new Options())
        ->add('taxonomy_term', TermStorageInterface::class)
        ->index(0))
      ->add(TermStorageInterface::class, 'loadTree', static::terms());
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
      // Ensure regular textfield with maxlength comes through.
      'textField' => [
        (object) [
          'widget' => 'textfield',
        ],
        [
          '#type' => 'textfield',
          '#title' => 'textField',
          '#maxlength' => 256,
        ],
        [
          '#type' => 'textfield',
          '#title' => 'textField',
          '#maxlength' => 256,
        ],
      ],
      // Textarea should not have maxlength after being handled.
      'textArea' => [
        (object) [
          'widget' => 'textarea',
        ],
        [
          '#type' => 'textfield',
          '#title' => 'textArea',
          '#maxlength' => 256,
        ],
        [
          '#type' => 'textarea',
          '#title' => 'textArea',
        ],
      ],
      // A textformat property converts it to a text_format element.
      'textFormat' => [
        (object) [
          'widget' => 'textarea',
          'textFormat' => 'html',
        ],
        [
          '#type' => 'textfield',
          '#title' => 'textFormat',
        ],
        [
          '#type' => 'text_format',
          '#title' => 'textFormat',
          '#format' => 'html',
          '#allowed_formats' => ['html'],
        ],
      ],
      // Number field includes constraints and a "step" for up/down controlls.
      'numberField' => [
        (object) [
          'widget' => 'number',
          'step' => '10',
          'min' => '10',
          'max' => '100',
        ],
        [
          '#type' => 'textfield',
          '#title' => 'number',
        ],
        [
          '#type' => 'number',
          '#title' => 'number',
          '#step' => '10',
          '#min' => '10',
          '#max' => '100',
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
      'taxonomyOptionsField' => [
        (object) [
          "widget" => "list",
          "type" => "autocomplete",
          "allowCreate" => FALSE,
          "source" => (object) [
            "plugin" => "taxonomy",
            "config" => (object) [
              "vocabulary" => "test_vocabulary",
            ],
          ],
        ],
        [
          '#type' => 'textfield',
          '#title' => 'Taxonomy Options',
        ],
        [
          '#type' => 'select2',
          '#title' => 'Taxonomy Options',
          '#options' => [
            'Term 1' => 'Term 1',
            'Term 2' => 'Term 2',
            'Term 3' => 'Term 3',
          ],
          '#other_option' => FALSE,
          '#multiple' => FALSE,
          '#autocreate' => FALSE,
          '#target_type' => 'taxonomy_term',
        ],
      ],
      'listWithNoOptions' => [
        (object) [
          'widget' => 'list',
          'type' => 'autocomplete',
          'source' => (object) [],
        ],
        [
          '#type' => 'textfield',
          '#title' => 'List With No Options',
        ],
        [
          '#type' => 'select2',
          '#title' => 'List With No Options',
          '#options' => [],
          '#multiple' => FALSE,
          '#autocreate' => FALSE,
          '#target_type' => NULL,
          '#other_option' => FALSE,
        ],
      ],
    ];
  }

  public static function terms(): array {
    return [
      (object) [
        'tid' => 1,
        'name' => 'Term 1',
        'vid' => 'test_vocabulary',
      ],
      (object) [
        'tid' => 2,
        'name' => 'Term 2',
        'vid' => 'test_vocabulary',
      ],
      (object) [
        'tid' => 3,
        'name' => 'Term 3',
        'vid' => 'test_vocabulary',
      ],
    ];
  }

  /**
   * Test the getDropdownOptions method.
   *
   * @dataProvider fixOptionSourceDataProvider
   * @covers ::fixOptionSource
   */
  public function testFixOptionSource($spec, $expected) {
    $router = WidgetRouter::create($this->getContainerChain()->getMock());

    // Use reflection to access the protected method.
    $reflection = new \ReflectionClass($router);
    $method = $reflection->getMethod('fixOptionSource');
    $method->setAccessible(TRUE);

    $fixed = $method->invokeArgs($router, [$spec]);
    $this->assertEquals($expected, $fixed);
  }

  /**
   * Data provider for testFixOptionSource.
   *
   * @return array
   *   Array of test data with spec and expected values.
   */
  public static function fixOptionSourceDataProvider(): array {
    return [
      'metastoreSchema and titleProperty' => [
        (object) [
          'titleProperty' => 'name',
          'source' => (object) [
            'metastoreSchema' => 'publisher',
          ],
        ],
        (object) [
          'source' => (object) [
            'plugin' => 'metastoreSchema',
            'config' => (object) [
              'titleProperty' => 'name',
              'schema' => 'publisher',
            ],
          ],
        ],
      ],
      'no titleProperty' => [
        (object) [
          'source' => (object) [
            'metastoreSchema' => 'publisher',
          ],
        ],
        (object) [
          'source' => (object) [
            'plugin' => 'metastoreSchema',
            'config' => (object) [
              'schema' => 'publisher',
            ],
          ],
        ],
      ],
      'has returnValue' => [
        (object) [
          'source' => (object) [
            'returnValue' => 'url',
            'metastoreSchema' => 'data-dictionary',
          ],
        ],
        (object) [
          'source' => (object) [
            'plugin' => 'metastoreSchema',
            'config' => (object) [
              'schema' => 'data-dictionary',
              'returnValue' => 'url',
            ],
          ],
        ],
      ],
      'already correct' => [
        (object) [
          'source' => (object) [
            'plugin' => 'metastoreSchema',
            'config' => (object) [
              'titleProperty' => 'name',
              'schema' => 'publisher',
            ],
          ],
        ],
        (object) [
          'source' => (object) [
            'plugin' => 'metastoreSchema',
            'config' => (object) [
              'titleProperty' => 'name',
              'schema' => 'publisher',
            ],
          ],
        ],
      ],
      'just enum' => [
        (object) [
          'source' => (object) [
            'enum' => ['option1', 'option2'],
          ],
        ],
        (object) [
          'source' => (object) [
            'enum' => ['option1', 'option2'],
          ],
        ],
      ],
    ];
  }

}
