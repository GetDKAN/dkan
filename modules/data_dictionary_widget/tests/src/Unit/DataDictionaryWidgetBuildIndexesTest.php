<?php

namespace Drupal\Tests\data_dictionary_widget\Unit;

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
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

    $dataDictionaryWidget = new DataDictionaryWidget(
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
   * Test creating a new dictionary with indexes and adding it to the dictionary table.
   */
  public function testAddNewIndexesDictionaryWidget() {
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

    $new_index = [
      'field_json_metadata' => [
        [
          'indexes' => [
            'field_collection' => [
              'group' => [
                'index' => [
                  'description' => 'test',
                  'type' => 'index',
                ]
              ]
            ]
          ]
        ]
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
      ->willReturnOnConsecutiveCalls(
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        $new_index,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
      );

    // Set up a triggering element with '#op' set to 'add_index'.
    $trigger = ['#op' => 'add_index'];
    // Expect that getTriggeringElement will be called once and return the add.
    $formState->expects($this->any())
      ->method('getTriggeringElement')
      ->willReturn($trigger);

    $dataDictionaryWidget = new DataDictionaryWidget(
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

    $this->assertEquals('test', $element['indexes']['data']['#rows'][0]['description']);
    $this->assertEquals('index', $element['indexes']['data']['#rows'][0]['type']);
  }

  /**
   * Test creating a new dictionary with indexes with index fields and adding it to the dictionary table.
   */
  public function testAddNewIndexesWithFieldsDictionaryWidget() {
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

    $new_index = [
      'field_json_metadata' => [
        [
          'indexes' => [
            'field_collection' => [
              'group' => [
                'index' => [
                  'description' => 'test',
                  'type' => 'index',
                ]
              ]
            ]
          ]
        ]
      ]
    ];

    $new_index_fields = [
      'field_json_metadata' => [
        [
          'indexes' => [
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
            ]
          ]
        ]
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

    $formState->expects($this->exactly(15))
      ->method('get')
      ->willReturnOnConsecutiveCalls(
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        $new_index_fields,
        NULL,
        NULL,
        NULL,
        NULL,
      );

    // Set up a triggering element with '#op' set to 'add_index'.
    $trigger = ['#op' => 'add_index_field'];
    // Expect that getTriggeringElement will be called once and return the add.
    $formState->expects($this->any())
      ->method('getTriggeringElement')
      ->willReturn($trigger);

    $dataDictionaryWidget = new DataDictionaryWidget(
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

    $this->assertEquals('test', $element['indexes']['fields']['data']['#rows'][0]['name']);
    $this->assertEquals(20, $element['indexes']['fields']['data']['#rows'][0]['length']);
  }

  /**
   * Test the creation of the edit buttons for a data dictionary indexes field.
   */
  public function testEditIndexesButtonsCreationDictionaryWidget() {
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
        'description' => 'test',
        'type' => 'index',
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
      ->willReturnOnConsecutiveCalls(
        NULL,
        NULL,
        NULL,
        NULL,
        $current_fields,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL
      );

    $dataDictionaryWidget = new DataDictionaryWidget(
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

    $this->assertNotNull($element["indexes"]["edit_index_buttons"]);
    $this->assertArrayHasKey('#name', $element["indexes"]["edit_index_buttons"]['index_key_0']["edit_index_button"]);
    $this->assertArrayHasKey('#id', $element["indexes"]["edit_index_buttons"]['index_key_0']["edit_index_button"]);
    $this->assertArrayHasKey('#op', $element["indexes"]["edit_index_buttons"]['index_key_0']["edit_index_button"]);
  }

  /**
   * Test edit a data dictionary index field and save it.
   */
  public function testEditDataDictionaryIndexDictionaryWidget() {

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

    $current_index = [
      [
        'description' => 'test',
        'type' => 'index',
      ]
    ];

    $updated_index = [
      [
        'description' => 'test_edit',
        'type' => 'fulltext',
      ]
    ];

    $op = "edit_index_key_0";

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
            0 => [],
          ],
        ],
      ],
      'indexes' => [
        'data' => [
          "#rows" => [
            [
              'description' => 'test',
              'type' => 'index',
            ],
          ],
        ],
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
        [],
        $current_index,
        $current_index,
        [],
        [],
        [],
        [],
        [],
        $current_index,
        [],
        [],
        [],
        [],
        [],
        [],
        [],
        [],
        TRUE,
        [],
        $current_index,
        $current_index,
        [],
        [],
        [],
        [],
        [],
        $updated_index,
        [],
        [],
        [],
        [],
        []
      );

    $formState->expects($this->any())
      ->method('getTriggeringElement')
      ->willReturnOnConsecutiveCalls(
        ['#op' => $op], ['#op' => $op], ['#op' => $op], ['#op' => $op], ['#op' => $op], ['#op' => $op],
        ['#op' => 'update'], ['#op' => 'update'], ['#op' => 'update'], ['#op' => 'update'], ['#op' => 'update'],
      );

    $formState->expects($this->any())
      ->method('getUserInput')
      ->willReturn($user_input);

    $dataDictionaryWidget = new DataDictionaryWidget(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $third_party_settings
    );

    // Trigger callback function to edit fields.
    IndexFieldCallbacks::indexEditCallback($form, $formState);

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
    $this->assertEquals($element["indexes"]["data"]["#rows"][0]["description"], $current_index[0]["description"]);
    $this->assertEquals($element["indexes"]["data"]["#rows"][0]["type"], $current_index[0]["type"]);

    // Trigger callback function to save the edited fields.
    IndexFieldCallbacks::indexEditCallback($form, $formState);

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
    $this->assertEquals($element["indexes"]["data"]["#rows"][0]["description"], $updated_index[0]["description"]);
    $this->assertEquals($element["indexes"]["data"]["#rows"][0]["type"], $updated_index[0]["type"]);
  }

}
