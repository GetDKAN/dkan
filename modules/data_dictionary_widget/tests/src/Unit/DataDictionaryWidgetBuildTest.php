<?php

namespace Drupal\Tests\data_dictionary_widget\Unit;

use PHPUnit\Framework\TestCase;
use MockChain\Chain;
use Drupal\Core\Form\FormState;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\metastore\SchemaRetriever;
use Drupal\data_dictionary_widget\Controller\Widget\FieldAddCreation;
use Drupal\data_dictionary_widget\Controller\Widget\FieldButtons;
use Drupal\data_dictionary_widget\Controller\Widget\FieldCallbacks;
use Drupal\data_dictionary_widget\Controller\Widget\FieldCreation;
use Drupal\data_dictionary_widget\Controller\Widget\FieldEditCreation;
use Drupal\data_dictionary_widget\Controller\Widget\FieldOperations;
use Drupal\data_dictionary_widget\Controller\Widget\FieldValues;
use MockChain\Options;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormInterface;
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

    // Expect that getTriggeringElement will be called once and return the addTrigger.
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

    // Assert keys and values exist in $user_input
    $this->assertEquals($data['identifier'], $user_input['field_json_metadata']['identifier']);
    $this->assertEquals($data['title'], $user_input['field_json_metadata']['title']);
    $this->assertEquals($data['data']['fields'][0]['name'], $user_input['field_json_metadata']['dictionary_fields']['field_collection']['group']['name']);
    $this->assertEquals($data['data']['fields'][0]['title'], $user_input['field_json_metadata']['dictionary_fields']['field_collection']['group']['title']);
    $this->assertEquals($data['data']['fields'][0]['type'], $user_input['field_json_metadata']['dictionary_fields']['field_collection']['group']['type']);
    $this->assertEquals($data['data']['fields'][0]['format'], $user_input['field_json_metadata']['dictionary_fields']['field_collection']['group']['format']);
    $this->assertEquals($data['data']['fields'][0]['description'], $user_input['field_json_metadata']['dictionary_fields']['field_collection']['group']['description']);
  }
}
