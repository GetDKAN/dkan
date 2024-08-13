<?php

namespace Drupal\Tests\data_dictionary_widget\Unit;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\data_dictionary_widget\Indexes\IndexFieldAddCreation;
use Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks;
use Drupal\data_dictionary_widget\Indexes\IndexFieldOperations;
use Drupal\data_dictionary_widget\Plugin\Field\FieldWidget\DataDictionaryWidget;
use PHPUnit\Framework\TestCase;

/**
 * Test class for DataDictionaryWidget focus on Indexes.
 *
 * @group dkan
 * @group data_dictionary_widget
 * @group unit
 */
class DataDictionaryWidgetBuildIndexesTest extends TestCase {

  /**
   * Test collecting indexes field information.
   */
  public function testIndexesFieldCollectionDictionaryWidget() {

    // Create mock objects.
    $formState = $this->createMock(FormStateInterface::class);
    $fieldItemList = $this->createMock(FieldItemListInterface::class);
    $field_definition = $this->createMock(FieldDefinitionInterface::class);
    $settings = [];
    $third_party_settings = [];
    $form = [];
    $plugin_id = '';
    $plugin_definition = [];

    $dataDictionaryWidget = new DataDictionaryWidget (
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $third_party_settings
    );

    // Call the method under test.
    $element = $dataDictionaryWidget->formElement(
      $fieldItemList,
      0,
      [],
      $form,
      $formState
    );

    $add_fields = IndexFieldAddCreation::addIndex();

    $element = IndexFieldOperations::setAddIndexFormState($add_fields, $element);

    $add_index_fields = IndexFieldAddCreation::addIndexFields(null);

    $element = IndexFieldOperations::setAddIndexFieldFormState($add_index_fields, $element);

    $this->assertNotNull($element);
    $this->assertNotNull($element["indexes"]["field_collection"]);
    $this->assertArrayHasKey('group', $element["indexes"]["field_collection"], 'Indexes Group Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('description', $element["indexes"]["field_collection"]["group"]["index"], 'Indexes Name Field Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('type', $element["indexes"]["field_collection"]["group"]["index"], 'Indexes Type Field Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('fields', $element["indexes"]["field_collection"]["group"]["index"], 'Indexes Fields Does Not Exist On The Data Dictionary Form');

    $this->assertNotNull($element["indexes"]["fields"]["field_collection"]);
    $this->assertArrayHasKey('group', $element["indexes"]["fields"]["field_collection"], 'Indexes Fields Group Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('name', $element["indexes"]["fields"]["field_collection"]["group"]["index"]["fields"], 'Indexes Fields Name Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('length', $element["indexes"]["fields"]["field_collection"]["group"]["index"]["fields"], 'Indexes Fields Length Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('save_index_settings', $element["indexes"]["fields"]["field_collection"]["group"]["index"]["fields"]["actions"], 'Add button Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('cancel_index_settings', $element["indexes"]["fields"]["field_collection"]["group"]["index"]["fields"]["actions"], 'Cancel button Does Not Exist On The Data Dictionary Form');

    $this->assertArrayHasKey('save_index', $element["indexes"]["field_collection"]["group"]["index"], 'Submit Index button Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('cancel_index', $element["indexes"]["field_collection"]["group"]["index"], 'Cancel Index button Does Not Exist On The Data Dictionary Form');
  }

  /**
   * Test creating a new dictionary field with indexes and adding it to the dictionary table.
   */
  public function testAddNewFieldWithIndexDictionaryWidget() {

    // Create mock objects.
    $formState = $this->createMock(FormStateInterface::class);
    $field_definition = $this->createMock(FieldDefinitionInterface::class);
    $settings = [];
    $third_party_settings = [];
    $plugin_id = '';
    $plugin_definition = [];

    $form = [
      'field_json_metadata' => [
        'widget' => [
          0 => [
            'dictionary_fields' => [
              'data' => [
                '#rows' => null
              ]
            ],
            'indexes' => [
              'data' => [
                '#rows' => null
              ]
            ]
          ]
        ]
      ]
    ];

    $user_input = [
      'field_json_metadata' => [
        'identifier' => 'test',
        'title' => 'test',
        'indexes' => [
          'field_collection' => [
            'group' => [
              'index' => [
                'description' => 'test',
                'type' => 'index',
                'fields' => [
                  'field_collection' => [
                    'group' => [
                      'index' => [
                        'fields' => [
                          'name' => 'test',
                          'length' => 20,
                        ]
                      ]
                    ]
                  ]
                ],
                'data' => null,
                'edit_buttons' => [],
              ]
            ]
          ]
        ]
      ]
    ];

    $values = [
      [
        'identifier' => 'test',
        'title' => 'test',
        'indexes' => [
          'data' => '',
          'add_row_button' => 'Add index',
          'field_collection' => [
            'group' => [
              'index' => [
                'description' => 'test',
                'type' => 'index',
              ]
            ]
          ],
          'fields' => [
            'field_collection' => [
              'group' => [
                'indexes' => [
                  'fields' => [
                    'name' => 'test',
                    'length' => 20,
                  ]
                ]
              ]
            ]
          ]
        ]
      ]
    ];

    $dataDictionaryWidget = new DataDictionaryWidget (
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $third_party_settings
    );

    // Set up a triggering element with '#op' set to 'add_index'.
    $trigger = ['#op' => 'add_index'];

    // Expect that getTriggeringElement will be called once and return the add.
    $formState->expects($this->any())
      ->method('getTriggeringElement')
      ->willReturn($trigger);

    $formState->expects($this->exactly(12))
      ->method('set')
      ->willReturnOnConsecutiveCalls (
        '',
        $this->equalTo($user_input),
        TRUE,
        FALSE,
        '',
        ''
      );

    IndexFieldCallbacks::indexAddCallback($form, $formState);

    // Call the method under test.
    $json_data = $dataDictionaryWidget->massageFormValues(
      $values,
      $form,
      $formState
    );

    // Convert JSON to array
    $data = json_decode($json_data, true);

    $this->assertEquals($data['identifier'], $user_input['field_json_metadata']['identifier']);
    $this->assertEquals($data['data']['title'], $user_input['field_json_metadata']['title']);
    $this->assertEquals($data['data']['indexes'][0]['description'], $user_input['field_json_metadata']['indexes']['field_collection']['group']['index']['description']);
    $this->assertEquals($data['data']['indexes'][0]['type'], $user_input['field_json_metadata']['indexes']['field_collection']['group']['index']['type']);
    $this->assertEquals($data['data']['indexes'][0]['fields'][0]['name'], $user_input['field_json_metadata']['indexes']['field_collection']['group']['index']['fields']['field_collection']['group']['index']['fields']['name']);
    $this->assertEquals($data['data']['indexes'][0]['fields'][0]['length'], $user_input['field_json_metadata']['indexes']['field_collection']['group']['index']['fields']['field_collection']['group']['index']['fields']['length']);
  }

}
