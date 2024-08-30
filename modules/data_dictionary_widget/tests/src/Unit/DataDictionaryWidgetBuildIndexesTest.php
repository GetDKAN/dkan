<?php

namespace Drupal\Tests\data_dictionary_widget\Unit;

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\data_dictionary_widget\Indexes\IndexFieldAddCreation;
use Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks;
use Drupal\data_dictionary_widget\Indexes\IndexFieldEditCreation;
use Drupal\data_dictionary_widget\Indexes\IndexFieldOperations;
use Drupal\data_dictionary_widget\Indexes\IndexValidation;
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

    $element = $dataDictionaryWidget->formElement(
      $fieldItemList,
      0,
      [],
      $form,
      $formState
    );

    // After creating the widget and building it call the index functions to update $element with the index fields.
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

    // The gets are happening in formElement and function calls in it that use the formState variable.
    // Set the value for $form_state->get("new_index").
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

    // The gets are happening in formElement and function calls in it that use the formState variable.
    // Set the value for $form_state->get("new_index_fields").
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

    $formState->expects($this->once())
      ->method('getFormObject')
      ->willReturn($formObject);

    $formObject->expects($this->once())
      ->method('getEntity')
      ->willReturn($entity);

    $entity->expects($this->once())
      ->method('set')
      ->with('field_data_type', 'data-dictionary');

    // The gets are happening in formElement and function calls in it that use the formState variable.
    // Set the value for $form_state->get("current_index").
    $formState->expects($this->exactly(14))
      ->method('get')
      ->willReturnOnConsecutiveCalls(
        NULL,
        NULL,
        NULL,
        NULL,
        $current_index,
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
   * Test edit a data dictionary index and save it.
   */
  public function testEditDataDictionaryIndexDictionaryWidget() {
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

    $user_input = [
      'field_json_metadata' => [
        0 => [
          'identifier' => 'test_identifier',
          'title' => 'test_title',
          'indexes' => [
            'edit_index' => [
              'index_key_0' => [
                'description' => 'test_edit',
                'type' => 'fulltext',
              ]
            ]
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
              'fields' => [],
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

    // The gets are happening in indexEditCallback and formElement and function calls in it that use the formState variable.
    // Set the value for various $form_state calls that use the current index and then are updated when we pass the updated index value.
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
        ['#op' => 'edit_index_key_0'], ['#op' => 'edit_index_key_0'],
        ['#op' => 'update_index_key_0'], ['#op' => 'update_index_key_0'],
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
    $this->assertEquals($current_index[0]["description"], $element["indexes"]["data"]["#rows"][0]["description"]);
    $this->assertEquals($current_index[0]["type"], $element["indexes"]["data"]["#rows"][0]["type"]);

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
    $this->assertEquals($updated_index[0]["description"], $element["indexes"]["data"]["#rows"][0]["description"]);
    $this->assertEquals($updated_index[0]["type"], $element["indexes"]["data"]["#rows"][0]["type"]);
  }

  /**
   * Test edit index for indexes.
   */
  public function testEditDataDictionaryIndexEdit() {
    // Arrange
    $indexKey = 'index_key_0';
    $current_index = [
      [
        'description' => 'test',
        'type' => 'index',
        'fields' => [
          [
            'name' => 'test',
            'length' => 20
          ]
        ]
      ]
    ];
    $index_being_modified = $current_index;
    $formState = $this->createMock(FormStateInterface::class);
    $formState->expects($this->any())
      ->method('get')
      ->willReturnOnConsecutiveCalls(FALSE);

    // Act
    $edit_index = IndexFieldEditCreation::editIndex($indexKey, $current_index, $index_being_modified, $formState);

    // Assert
    $this->assertNotNull($edit_index);
    $this->assertEquals('field_json_metadata[0][indexes][edit_index][index_key_0][description]', $edit_index['description']['#name']);
    $this->assertEquals($current_index[0]['description'], $edit_index['description']['#value']);
    $this->assertEquals('field_json_metadata[0][indexes][edit_index][index_key_0][type]', $edit_index['type']['#name']);
    $this->assertEquals($current_index[0]['type'], $edit_index['type']['#value']);
    $this->assertEquals($current_index[0]['fields'][0]['name'], $edit_index['group']['fields']['data']['#rows'][0]['name']);
    $this->assertEquals($current_index[0]['fields'][0]['length'], $edit_index['group']['fields']['data']['#rows'][0]['length']);
  }

  /**
   * Test edit index fields for indexes.
   */
  public function testEditDataDictionaryIndexEditFields() {
    // Arrange
    $indexKey = 'index_field_key_0';
    $current_index_fields = [
      [
        'name' => 'test',
        'length' => 20
      ]
    ];

    // Act
    $edit_index_fields = IndexFieldEditCreation::editIndexFields($indexKey, $current_index_fields, null);

    // Assert
    $this->assertNotNull($edit_index_fields);
    $this->assertEquals('field_json_metadata[0][indexes][fields][edit_index_fields][0][name]', $edit_index_fields['name']['#name']);
    $this->assertEquals($current_index_fields[0]['name'], $edit_index_fields['name']['#value']);
    $this->assertEquals('field_json_metadata[0][indexes][fields][edit_index_fields][0][length]', $edit_index_fields['length']['#name']);
    $this->assertEquals($current_index_fields[0]['length'], $edit_index_fields['length']['#value']);
  }

  /**
   * Test editing data dictionary index fields and save it.
   */
  public function testEditDataDictionaryIndexFieldsDictionaryWidget() {
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

    $current_index_field = [
      [
        'name' => 'test',
        'length' => 20,
      ]
    ];

    $updated_index_field = [
      [
        'name' => 'test_update',
        'length' => 25,
      ]
    ];

    $user_input = [
      'field_json_metadata' => [
        0 => [
          'identifier' => 'test_identifier',
          'title' => 'test_title',
          'indexes' => [
            'edit_index' => [
              'index_key_0' => [
                'description' => 'test_edit',
                'type' => 'fulltext',
              ]
            ],
            'fields' => [
              'edit_index_fields' => [
                [
                  'name' => 'test',
                  'length' => 20,
                ]
              ]
            ]
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
              'fields' => [
                'name' => 'test',
                'length' => 20,
              ],
            ],
          ],
        ],
        'fields' => [
          'data' => [
            "#rows" => [
              0 => [
                'name' => 'test',
                'length' => 20,
              ],
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

    // The gets are happening in indexEditCallback and formElement and function calls in it that use the formState variable.
    // Set the value for various $form_state calls that use the current index and then are updated when we pass the updated index value.
    $formState->expects($this->any())
      ->method('get')
      ->willReturnOnConsecutiveCalls(
        $current_index_field,
        $current_index_field,
        [],
        [],
        $current_index_field,
        [],
        [],
        [],
        [],
        [],
        [],
        $current_index_field,
        $current_index_field,
        [],
        $current_index_field,
        [],
        [],
        TRUE,
        $current_index_field,
        $current_index_field,
        [],
        [],
        [],
        [],
        [],
        [],
        [],
        [],
        [],
        $updated_index_field,
        $updated_index_field,
        [],
        $updated_index_field,
      );

    $formState->expects($this->any())
      ->method('getTriggeringElement')
      ->willReturnOnConsecutiveCalls(
        ['#op' => 'edit_index_field_key_0'], ['#op' => 'edit_index_field_key_0'],
        ['#op' => 'update_index_field_key_0'], ['#op' => 'update_index_field_key_0'],
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
    IndexFieldCallbacks::indexEditSubformCallback($form, $formState);

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
    $this->assertEquals($current_index_field[0]["name"], $element["indexes"]["fields"]["data"]["#rows"][0]["name"]);
    $this->assertEquals($current_index_field[0]["length"], $element["indexes"]["fields"]["data"]["#rows"][0]["length"]);

    // Trigger callback function to save the edited fields.
    IndexFieldCallbacks::indexEditSubformCallback($form, $formState);

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
    $this->assertEquals($updated_index_field[0]["name"], $element["indexes"]["fields"]["data"]["#rows"][0]["name"]);
    $this->assertEquals($updated_index_field[0]["length"], $element["indexes"]["fields"]["data"]["#rows"][0]["length"]);
  }

}
