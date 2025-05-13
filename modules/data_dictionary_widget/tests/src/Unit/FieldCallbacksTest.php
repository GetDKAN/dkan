<?php

namespace Drupal\Tests\data_dictionary_widget\Unit;

use PHPUnit\Framework\TestCase;
use Drupal\data_dictionary_widget\Fields\FieldCallbacks;
use Drupal\Core\Form\FormStateInterface;

/**
 * Test class for FieldCallback.
 *
 * @group dkan
 * @group data_dictionary_widget
 * @group unit
 */
class FieldCallbacksTest extends TestCase {

  public function testDescriptionsAndFormats() {
    $formState = $this->createMock(FormStateInterface::class);

    $types = ['string', 'date', 'datetime', 'integer', 'number', 'year', 'boolean'];

    $field_json_metadata_string = [
      [
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
          ]
        ]
      ]
    ];

    $field_json_metadata_date = [
      [
        'identifier' => 'test_identifier',
        'title' => 'test_title',
        'dictionary_fields' => [
          'field_collection' => [
            'group' => [
              'name' => 'test_edit',
              'title' => 'test_edit',
              'type' => 'date',
              'format' => 'default',
              'format_other' => '',
              'description' => 'test_edit',
            ]
          ]
        ]
      ]
    ];

    $field_json_metadata_datetime = [
      [
        'identifier' => 'test_identifier',
        'title' => 'test_title',
        'dictionary_fields' => [
          'field_collection' => [
            'group' => [
              'name' => 'test_edit',
              'title' => 'test_edit',
              'type' => 'datetime',
              'format' => 'default',
              'format_other' => '',
              'description' => 'test_edit',
            ]
          ]
        ]
      ]
    ];

    $field_json_metadata_integer = [
      [
        'identifier' => 'test_identifier',
        'title' => 'test_title',
        'dictionary_fields' => [
          'field_collection' => [
            'group' => [
              'name' => 'test_edit',
              'title' => 'test_edit',
              'type' => 'integer',
              'format' => 'default',
              'format_other' => '',
              'description' => 'test_edit',
            ]
          ]
        ]
      ]
    ];

    $field_json_metadata_number = [
      [
        'identifier' => 'test_identifier',
        'title' => 'test_title',
        'dictionary_fields' => [
          'field_collection' => [
            'group' => [
              'name' => 'test_edit',
              'title' => 'test_edit',
              'type' => 'number',
              'format' => 'default',
              'format_other' => '',
              'description' => 'test_edit',
            ]
          ]
        ]
      ]
    ];

    $field_json_metadata_year = [
      [
        'identifier' => 'test_identifier',
        'title' => 'test_title',
        'dictionary_fields' => [
          'field_collection' => [
            'group' => [
              'name' => 'test_edit',
              'title' => 'test_edit',
              'type' => 'year',
              'format' => 'default',
              'format_other' => '',
              'description' => 'test_edit',
            ]
          ]
        ]
      ]
    ];

    $field_json_metadata_boolean = [
      [
        'identifier' => 'test_identifier',
        'title' => 'test_title',
        'dictionary_fields' => [
          'field_collection' => [
            'group' => [
              'name' => 'test_edit',
              'title' => 'test_edit',
              'type' => 'boolean',
              'format' => 'default',
              'format_other' => '',
              'description' => 'test_edit',
            ]
          ]
        ]
      ]
    ];

    $field_json_metadata_variables = [
      'string' => $field_json_metadata_string,
      'date' => $field_json_metadata_date,
      'datetime' => $field_json_metadata_datetime,
      'integer' => $field_json_metadata_integer,
      'number' => $field_json_metadata_number,
      'year' => $field_json_metadata_year,
      'boolean' => $field_json_metadata_boolean,
    ];

    $formState->method('getValue')
      ->with(['field_json_metadata'])
      ->willReturnOnConsecutiveCalls(
        $field_json_metadata_string,
        $field_json_metadata_date,
        $field_json_metadata_datetime,
        $field_json_metadata_integer,
        $field_json_metadata_number,
        $field_json_metadata_year,
        $field_json_metadata_boolean
      );

    $trigger = ['#op' => 'type'];

    $formState->expects($this->any())
      ->method('getTriggeringElement')
      ->willReturn($trigger);

    $form["field_json_metadata"]["widget"][0]['dictionary_fields']["field_collection"]["group"]["format"] = [
      '#description' => '',
      '#options' => [],
    ];

    foreach ($types as $type) {
      $format_field = FieldCallbacks::updateFormatOptions($form, $formState);
      $field_json_metadata_type = $field_json_metadata_variables[$type];
      $field_json_metadata = $field_json_metadata_type[0]['dictionary_fields']['field_collection']['group']['type'];

      $this->assertEquals($type, $field_json_metadata);
      $this->assertNotNull($format_field["#description"]);
      $this->assertNotNull($format_field["#options"]);
    }
  }
}