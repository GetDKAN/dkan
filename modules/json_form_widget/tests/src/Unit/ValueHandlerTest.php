<?php

namespace Drupal\Tests\json_form_widget\Unit;

use PHPUnit\Framework\TestCase;
use Drupal\json_form_widget\ValueHandler;

/**
 * Test class for ValueHandlerTest.
 */
class ValueHandlerTest extends TestCase {

  /**
   * Test.
   */
  public function test() {
    $value_handler = new ValueHandler();
    // Test object.
    $values = [
      "distribution" => [
        "distribution" => [
          0 => [
            "distribution" => [
              "@type" => "dcat:Distribution",
              "schema" => [
                "schema" => [
                  "fields" => [
                    "fields" => [
                      0 => [
                        "fields" => [
                          "name" => "Field name",
                          "title" => "Field title",
                        ],
                      ],
                    ],
                  ],
                ],
              ],
            ],
          ],
        ],
      ],
    ];
    $property = "distribution";
    $schema = json_decode($this->getComplexObjectSchema());
    $expected = [
      0 => [
        "@type" => "dcat:Distribution",
        "schema" => [
          "fields" => [
            0 => [
              "name" => "Field name",
              "title" => "Field title",
            ],
          ],
        ],
      ],
    ];
    // Test complex array.
    $result = $value_handler->flattenValues($values, $property, $schema);
    $this->assertEquals($result, $expected);

    // Test array without values.
    $values = [
      "references" => [
        "references" => [
          0 => "http://google.com",
        ],
      ],
    ];
    $property = "references";
    $schema = json_decode('{"title":"Related documents","type":"array","items":{"type":"string","format":"uri"}}');
    $expected = [
      "http://google.com",
    ];
    $result = $value_handler->flattenValues($values, $property, $schema);
    $this->assertEquals($result, $expected);

    // Test strings without values.
    $schema = json_decode('{"type":"string","format":"uri"}');
    $result = $value_handler->flattenValues([], "url", $schema);
    $this->assertEquals($result, FALSE);

    // Test object without values.
    $schema = json_decode($this->getObjectSchema());
    $result = $value_handler->handleObjectValues(NULL, "publisher", $schema);
    $this->assertEquals($result, FALSE);

  }

  /**
   * Helper function to get complex object schema.
   */
  private function getComplexObjectSchema() {
    $schema = '{
      "title": "Distribution",
      "description": "Description.",
      "type": "array",
      "items": {
        "title": "Project Open Data Distribution",
        "type": "object",
        "properties": {
          "@type": {
            "title": "Metadata Context",
            "description": "Test Description.",
            "default": "dcat:Distribution",
            "type": "string",
            "readOnly": true
          },
          "schema": {
            "title": "Schema",
            "description": "Test description.",
            "type": "object",
            "properties": {
              "fields": {
                "type": "array",
                "items": {
                  "title": "Table Schema Field",
                  "type": "object",
                  "properties": {
                    "name": {
                      "title": "Name",
                      "description": "A name for this field.",
                      "type": "string"
                    },
                    "title": {
                      "title": "Title",
                      "description": "A human-readable title.",
                      "type": "string"
                    }
                  }
                }
              }
            }
          }
        }
      }
    }';
    return $schema;
  }

  /**
   * Helper function to get object schema.
   */
  private function getObjectSchema() {
    return '{
      "title": "Organization",
      "description": "A Dataset Publisher Organization.",
      "type": "object",
      "required": [
        "name"
      ],
      "properties": {
        "@type": {
          "title": "Metadata Context",
          "description": "IRI for the JSON-LD data type. This should be org:Organization for each publisher",
          "type": "string",
          "default": "org:Organization"
        },
        "name": {
          "title": "Publisher Name",
          "description": "",
          "type": "string",
          "minLength": 1
        },
        "subOrganizationOf": {
          "title": "Parent Organization",
          "type": "string"
        }
      }
    }';
  }

}
