<?php

namespace Drupal\Tests\data_dictionary_widget\Unit;

use PHPUnit\Framework\TestCase;
use Drupal\data_dictionary_widget\Fields\FieldAddCreation;
use Drupal\data_dictionary_widget\Fields\FieldCallbacks;
use Drupal\data_dictionary_widget\Fields\FieldCreation;
use Drupal\data_dictionary_widget\Fields\FieldOperations;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\data_dictionary_widget\Plugin\Field\FieldWidget\DataDictionaryWidget;

/**
 * Test class for DataDictionaryWidget.
 *
 * @group dkan
 * @group data_dictionary_widget
 * @group unit
 */
class DataDictionaryWidgetBuildTest extends TestCase {

  /**
   * Test rendering a new Data Dictionary Widget.
   */
  public function testRenderDataDictionaryWidget() {

    // Create mock objects.
    $formState = $this->createMock(FormStateInterface::class);
    $formObject = $this->createMock(EntityFormInterface::class);
    $entity = $this->createMock(FieldableEntityInterface::class);
    $fieldItemList = $this->createMock(FieldItemListInterface::class);
    $field_definition = $this->createMock(FieldDefinitionInterface::class);
    $settings = [];
    $third_party_settings = [];
    $form = [];
    $plugin_id = '';
    $plugin_definition = [];

    // Expectations for getFormObject().
    $formState->expects($this->once())
      ->method('getFormObject')
      ->willReturn($formObject);

    // Expectations for getEntity().
    $formObject->expects($this->once())
      ->method('getEntity')
      ->willReturn($entity);

    // Expectations for set() method if form entity is FieldableEntityInterface.
    $entity->expects($this->once())
      ->method('set')
      ->with('field_data_type', 'data-dictionary');

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

    $this->assertNotNull($element);
    $this->assertArrayHasKey('identifier', $element, 'Identifier Field Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('title', $element, 'Identifier Field Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('dictionary_fields', $element, 'Identifier Field Does Not Exist On The Data Dictionary Form');
  }

  /**
   * Test collecting dictionary field information.
   */
  public function testFieldCollectionDictionaryWidget() {

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

    $add_fields = FieldAddCreation::addFields();

    $element = FieldOperations::setAddFormState($add_fields, $element);

    $this->assertNotNull($element);
    $this->assertNotNull($element["dictionary_fields"]["field_collection"]);
    $this->assertArrayHasKey('group', $element["dictionary_fields"]["field_collection"], 'Group Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('name', $element["dictionary_fields"]["field_collection"]["group"], 'Name Field Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('title', $element["dictionary_fields"]["field_collection"]["group"], 'Title Field Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('type', $element["dictionary_fields"]["field_collection"]["group"], 'Type Field Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('format', $element["dictionary_fields"]["field_collection"]["group"], 'Format Field Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('format_other', $element["dictionary_fields"]["field_collection"]["group"], 'format other Field Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('description', $element["dictionary_fields"]["field_collection"]["group"], 'Description Field Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('title', $element["dictionary_fields"]["field_collection"]["group"], 'Title Field Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('actions', $element["dictionary_fields"]["field_collection"]["group"], 'Actions Field Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('save_settings', $element["dictionary_fields"]["field_collection"]["group"]["actions"], 'Add button Does Not Exist On The Data Dictionary Form');
    $this->assertArrayHasKey('cancel_settings', $element["dictionary_fields"]["field_collection"]["group"]["actions"], 'Cancel button Does Not Exist On The Data Dictionary Form');
  }

  /**
   * Test creating a new dictionary field and adding it to the dictionary table.
   */
  public function testAddNewFieldDictionaryWidget() {

    // Create mock objects.
    $formState = $this->createMock(FormStateInterface::class);
    $fieldItemList = $this->createMock(FieldItemListInterface::class);
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
              ],
              'fields' => [
                'data' => [
                  '#rows' => null
                ]
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
        'dictionary_fields' => [
          'field_collection' => [
            'group' => [
                'name' => 'test',
                'title' => 'test',
                'type' => 'string',
                'format' => 'default',
                'format_other' => '',
                'description' => 'test',
                'data' => null,
                'edit_buttons' => []
            ]
          ]
        ]
      ]
    ];

    $data_results = [
      [
        'name' => 'test',
        'title' => 'test',
        'type' => 'string',
        'format' => 'default',
        'description' => 'test'
      ]
    ];

    $values = [
      [
        'identifier' => 'test',
        'title' => 'test',
        'dictionary_fields' => [
          'data' => '',
          'add_row_button' => 'Add field',
          'field_collection' => [
            'group' => [
                'name' => 'test',
                'title' => 'test',
                'type' => 'string',
                'format' => 'default',
                'format_other' => '',
                'description' => 'test'
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

    // Call the method under test.
    $element = $dataDictionaryWidget->formElement(
      $fieldItemList,
      0,
      [],
      $form,
      $formState
    );

    $add_fields = FieldAddCreation::addFields();

    $element = FieldOperations::setAddFormState($add_fields, $element);

    // Set up a triggering element with '#op' set to 'add'.
    $trigger = ['#op' => 'add'];

    // Expect that getTriggeringElement will be called once and return the add.
    $formState->expects($this->any())
      ->method('getTriggeringElement')
      ->willReturn($trigger);

    $formState->expects($this->exactly(6))
      ->method('set')
      ->willReturnOnConsecutiveCalls (
        '',
        $this->equalTo($user_input),
        TRUE,
        FALSE,
        '',
        ''
      );

    FieldCallbacks::addSubformCallback($form, $formState);
    $element['dictionary_fields']['data'] = FieldCreation::createDictionaryDataRows([], $data_results, $formState);
    
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
    $this->assertEquals($data['data']['fields'][0]['name'], $user_input['field_json_metadata']['dictionary_fields']['field_collection']['group']['name']);
    $this->assertEquals($data['data']['fields'][0]['title'], $user_input['field_json_metadata']['dictionary_fields']['field_collection']['group']['title']);
    $this->assertEquals($data['data']['fields'][0]['type'], $user_input['field_json_metadata']['dictionary_fields']['field_collection']['group']['type']);
    $this->assertEquals($data['data']['fields'][0]['format'], $user_input['field_json_metadata']['dictionary_fields']['field_collection']['group']['format']);
    $this->assertEquals($data['data']['fields'][0]['description'], $user_input['field_json_metadata']['dictionary_fields']['field_collection']['group']['description']);
  }

  /**
   * Test the creation of the edit buttons for a data dictionary field.
   */
  public function testEditButtonsCreation() {

    // Create mock objects.
    $formState = $this->createMock(FormStateInterface::class);
    $formObject = $this->createMock(EntityFormInterface::class);
    $entity = $this->createMock(FieldableEntityInterface::class);
    $fieldItemList = $this->createMock(FieldItemListInterface::class);
    $field_definition = $this->createMock(FieldDefinitionInterface::class);
    $settings = [];
    $third_party_settings = [];
    $form = [];
    $plugin_id = '';
    $plugin_definition = [];

    $current_fields = [
      [
        'name' => 'test',
        'title' => 'test',
        'type' => 'string',
        'format' => 'default',
        'description' => 'test'
      ]
    ];

    $formState->expects($this->once())
      ->method('getFormObject')
      ->willReturn($formObject);

    $formObject->expects($this->once())
      ->method('getEntity')
      ->willReturn($entity);

    $entity->expects($this->once())
      ->method('set')
      ->with('field_data_type', 'data-dictionary');

    $formState->expects($this->exactly(14))
      ->method('get')
      ->willReturnOnConsecutiveCalls(NULL, NULL, NULL, FALSE, NULL, NULL, $current_fields);

    $dataDictionaryWidget = new DataDictionaryWidget (
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $third_party_settings
    );

    $element = $dataDictionaryWidget->formElement(
      $fieldItemList,
      0,
      [],
      $form,
      $formState
    );

    $dataDictionaryWidget->trustedCallbacks();
    $dataDictionaryWidget->preRenderForm($element['dictionary_fields']);

    $this->assertNotNull($element["dictionary_fields"]["edit_buttons"]);
    $this->assertArrayHasKey('#name', $element["dictionary_fields"]["edit_buttons"][0]["edit_button"]);
    $this->assertArrayHasKey('#id', $element["dictionary_fields"]["edit_buttons"][0]["edit_button"]);
    $this->assertArrayHasKey('#op', $element["dictionary_fields"]["edit_buttons"][0]["edit_button"]);
  }

    /**
   * Test edit a data dictionary field and save it.
   */
  public function testEditDataDictionaryField() {

    // Create mock objects.
    $formState = $this->createMock(FormStateInterface::class);
    $formObject = $this->createMock(EntityFormInterface::class);
    $entity = $this->createMock(FieldableEntityInterface::class);
    $fieldItemList = $this->createMock(FieldItemListInterface::class);
    $field_definition = $this->createMock(FieldDefinitionInterface::class);
    $settings = [];
    $third_party_settings = [];
    $form = [];
    $plugin_id = '';
    $plugin_definition = [];

    $current_dictionary_fields = [
      [
        'name' => 'test',
        'title' => 'test',
        'type' => 'string',
        'format' => 'default',
        'description' => 'test'
      ]
    ];

    $updated_dictionary_fields = [
      [
        'name' => 'test_edit',
        'title' => 'test_edit',
        'type' => 'string',
        'format' => 'default',
        'description' => 'test_Edit'
      ]
    ];

    $op = "edit_0";

    $user_input = [
      'field_json_metadata' => [
        0 => [
          'identifier' => 'test_identifier',
          'title' => 'test_title',
          'dictionary_fields' => [
            'field_collection' => [
              'group' => [
                'name' => 'test_edit',
                'title' => 'test_edit',
                'type' => 'string',
                'format' => 'default',
                'format_other' => '',
                'description' => 'test_edit',
              ]
            ],
            'data' => [
              [
                'field_collection' => [
                  'name' => 'test_edit',
                  'title' => 'test_edit',
                  'type' => 'string',
                  'format' => 'default',
                  'format_other' => '',
                  'description' => 'test_edit',
                ],
              ],
            ],
          ],
        ],
      ],
    ];

    $form["field_json_metadata"]["widget"][0] = [
      "dictionary_fields" => [
        "data" => [
          "#rows" => [
            [
              'name' => 'test',
              'title' => 'test',
              'type' => 'string',
              'format' => 'default',
              'format_other' => '',
              'description' => 'test',
            ],
          ],
        ],
          'field_collection' => [
            'group' => [
              'name' => 'test',
              'title' => 'test',
              'type' => 'string',
              'format' => 'default',
              'format_other' => '',
              'description' => 'test',
            ]
          ],
      ],
      'indexes' => [
        'fields' => [
          'data' => [
            "#rows" => [
              0 => [],
            ],
          ],
        ]
      ],
    ];

    $formState->expects($this->exactly(2))
      ->method('getFormObject')
      ->willReturn($formObject);

    $formObject->expects($this->exactly(2))
      ->method('getEntity')
      ->willReturn($entity);

    $entity->expects($this->exactly(2))
      ->method('set')
      ->with('field_data_type', 'data-dictionary');

    $formState->expects($this->any())
      ->method('get')
      ->willReturnOnConsecutiveCalls(
        [], [], $user_input, [], [], [], [], [], $current_dictionary_fields, [], [], $current_dictionary_fields, [],
        [], [], $user_input, [], [], [], [], [], [], [], [], $updated_dictionary_fields, [], [], $current_dictionary_fields, [],
      );

    $formState->expects($this->any())
      ->method('getTriggeringElement')
      ->willReturnOnConsecutiveCalls(
        ['#op' => $op], ['#op' => $op], ['#op' => $op], ['#op' => $op], ['#op' => $op], ['#op' => $op], ['#op' => $op], ['#op' => $op], ['#op' => $op], ['#op' => $op],
        ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0'],
      );

    $formState->expects($this->any())
      ->method('getUserInput')
      ->willReturn($user_input);

    $dataDictionaryWidget = new DataDictionaryWidget (
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $third_party_settings
    );

    // Trigger callback function to edit fields. 
    FieldCallbacks::editSubformCallback($form, $formState);
    
    // First call to re-create data dictionary form with the editable fields.
    $element = $dataDictionaryWidget->formElement(
      $fieldItemList,
      0,
      [],
      $form,
      $formState
    );

    // Assert edit feature loads form with the current field values.
    $this->assertNotNull($element);
    $this->assertEquals($element["dictionary_fields"]["data"]["#rows"][0]["name"], $current_dictionary_fields[0]["name"]);
    $this->assertEquals($element["dictionary_fields"]["data"]["#rows"][0]["title"], $current_dictionary_fields[0]["title"]);
    $this->assertEquals($element["dictionary_fields"]["data"]["#rows"][0]["type"], $current_dictionary_fields[0]["type"]);
    $this->assertEquals($element["dictionary_fields"]["data"]["#rows"][0]["format"], $current_dictionary_fields[0]["format"]);
    $this->assertEquals($element["dictionary_fields"]["data"]["#rows"][0]["description"], $current_dictionary_fields[0]["description"]);


    // Trigger callback function to save the edited fields. 
    FieldCallbacks::editSubformCallback($form, $formState);

    // Second call to re-create data dictionary and apply the edits made to the fields.
    $element = $dataDictionaryWidget->formElement(
      $fieldItemList,
      0,
      [],
      $form,
      $formState
    );

    // Assert update feature loads form with the edited field values.
    $this->assertNotNull($element);
    $this->assertEquals($element["dictionary_fields"]["data"]["#rows"][0]["name"], $updated_dictionary_fields[0]["name"]);
    $this->assertEquals($element["dictionary_fields"]["data"]["#rows"][0]["title"], $updated_dictionary_fields[0]["title"]);
    $this->assertEquals($element["dictionary_fields"]["data"]["#rows"][0]["type"], $updated_dictionary_fields[0]["type"]);
    $this->assertEquals($element["dictionary_fields"]["data"]["#rows"][0]["format"], $updated_dictionary_fields[0]["format"]);
    $this->assertEquals($element["dictionary_fields"]["data"]["#rows"][0]["description"], $updated_dictionary_fields[0]["description"]);
  }
}
