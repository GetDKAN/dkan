<?php

namespace Drupal\Tests\json_form_widget\Unit;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Language\LanguageDefault;
use Drupal\Core\Language\LanguageManager;
use PHPUnit\Framework\TestCase;
use Drupal\json_form_widget\ValueHandler;
use MockChain\Chain;
use MockChain\Options;

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

    // Test arrays.
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

    // Test arrays in arrays.
    $values = [
      "references" => [
        "references" => [
          0 => [
            "http://google.com" => "http://google.com",
            "http://url.com" => "http://url.com",
          ],
          1 => [
            "http://otherurl.com" => "http://otherurl.com",
            "http://evenanother.com" => "http://evenanother.com",
          ]
        ],
      ],
    ];
    $property = "references";
    $schema = json_decode('{"title":"Related documents","type":"array","items":{"type":"string","format":"uri"}}');
    $expected = [
      "http://google.com",
      "http://url.com",
      "http://otherurl.com",
      "http://evenanother.com",
    ];
    $result = $value_handler->flattenValues($values, $property, $schema);
    $this->assertEquals($result, $expected);

    // Test strings without values.
    $schema = json_decode('{"type":"string","format":"uri"}');
    $result = $value_handler->flattenValues([], "url", $schema);
    $this->assertEquals($result, FALSE);

    // Test select other.
    $schema = json_decode('{"type":"string"}');
    $formValues = [
      'license' => [
        0 => 'option 1',
        'select' => 'option 1',
        'other' => '',
      ]
    ];
    $result = $value_handler->flattenValues($formValues, "license", $schema);
    $expected = 'option 1';
    $this->assertEquals($result, $expected);

    // Test object without values.
    $schema = json_decode($this->getObjectSchema());
    $result = $value_handler->handleObjectValues(NULL, "publisher", $schema);
    $this->assertEquals($result, FALSE);

  }

  /**
   * Test values for datetime elements.
   */
  public function testDatetimeValues() {
    $language_manager = new LanguageManager(new LanguageDefault(['en']));
    $options = (new Options())
      ->add('language_manager', $language_manager)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $options);

    $container = $container_chain->getMock();
    \Drupal::setContainer($container);
    $value_handler = new ValueHandler();

    // Test flexible_datetime.
    $date = new DrupalDateTime('2020-05-11T15:06:39.000Z');
    $formValues = [
      'modified' => $date,
    ];
    $expected = $date->__toString();
    $result = $value_handler->handleStringValues($formValues, 'modified');
    $this->assertEquals($result, $expected);
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
