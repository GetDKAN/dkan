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
    $trigger= ['#op' => 'add'];

    // Expect that getTriggeringElement will be called once and return the add.
    $formState->expects($this->any())
      ->method('getTriggeringElement')
      ->willReturn($trigger);

    $formState->expects($this->exactly(4))
      ->method('set')
      ->willReturnOnConsecutiveCalls (
        '',
        $this->equalTo($user_input),
        TRUE,
        FALSE
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

    $formState->expects($this->exactly(5))
      ->method('get')
      ->willReturnOnConsecutiveCalls([], $current_fields, NULL, FALSE, NULL);

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

    $current_fields = [
      [
        'name' => 'test',
        'title' => 'test',
        'type' => 'string',
        'format' => 'default',
        'description' => 'test'
      ]
    ];

    $updated_current_fields = [
      [
        'name' => 'test_edit',
        'title' => 'test_edit',
        'type' => 'string',
        'format' => 'default',
        'description' => 'test_Edit'
      ]
    ];

    $fields_being_modified = [
      [
        'name' => 'test',
        'title' => 'test',
        'type' => 'string',
        'format' => 'default',
        'description' => 'test'
      ]
    ];

    $op = "edit_0";

    $user_input = [
      'field_json_metadata' => [
        0 => [
          'identifier' => 'test_identifier',
          'title' => 'test_title',
          'dictionary_fields' => [
            'data' => [
              0 => [
                'field_collection' => [
                    'name' => 'test_edit',
                    'title' => 'test_edit',
                    'type' => 'string',
                    'format' => 'default',
                    'format_other' => '',
                    'description' => 'test_edit',
                ]
              ]
            ]
          ]
        ]
      ]
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

    $formState->expects($this->exactly(12))
      ->method('get')
      ->willReturnOnConsecutiveCalls([], $current_fields, $fields_being_modified, FALSE, NULL, $fields_being_modified, $fields_being_modified, [], $updated_current_fields, $fields_being_modified, FALSE, NULL);

    $formState->expects($this->exactly(11))
      ->method('getTriggeringElement')
      ->willReturnOnConsecutiveCalls(['#op' => $op], ['#op' => $op], ['#op' => $op], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => $op], ['#op' => $op], ['#op' => $op], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0'], ['#op' => 'update_0']);

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

    $element = $dataDictionaryWidget->formElement(
      $fieldItemList,
      0,
      [],
      $form,
      $formState
    );

    // Assert that the subform to collect the edits for the data dictionary field exists.
    $this->assertNotNull($element["dictionary_fields"]["edit_fields"][0]);
    $this->assertArrayHasKey('name', $element["dictionary_fields"]["edit_fields"][0]);
    $this->assertArrayHasKey('title', $element["dictionary_fields"]["edit_fields"][0]);
    $this->assertArrayHasKey('type', $element["dictionary_fields"]["edit_fields"][0]);
    $this->assertArrayHasKey('format', $element["dictionary_fields"]["edit_fields"][0]);
    $this->assertArrayHasKey('format_other', $element["dictionary_fields"]["edit_fields"][0]);
    $this->assertArrayHasKey('description', $element["dictionary_fields"]["edit_fields"][0]);
    $this->assertArrayHasKey('update_field', $element["dictionary_fields"]["edit_fields"][0]);

    $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"][0] = [
      'name' => 'test_edit',
      'title' => 'test_edit',
      'type' => 'string',
      'format' => 'default',
      'format_other' => '',
      'description' => 'test_edit',
    ];

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

    $this->assertNotNull($element);
    $this->assertEquals($element["dictionary_fields"]["data"]["#rows"][0]["name"], $updated_current_fields[0]["name"]);
    $this->assertEquals($element["dictionary_fields"]["data"]["#rows"][0]["title"], $updated_current_fields[0]["title"]);
    $this->assertEquals($element["dictionary_fields"]["data"]["#rows"][0]["type"], $updated_current_fields[0]["type"]);
    $this->assertEquals($element["dictionary_fields"]["data"]["#rows"][0]["format"], $updated_current_fields[0]["format"]);
    $this->assertEquals($element["dictionary_fields"]["data"]["#rows"][0]["description"], $updated_current_fields[0]["description"]);
  }
}
