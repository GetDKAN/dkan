<?php

namespace Drupal\Tests\metastore\Unit;

use Drupal\Core\DependencyInjection\Container;
use Drupal\metastore\SchemaPropertiesHelper;
use Drupal\metastore\SchemaRetriever;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class SchemaPropertiesHelperTest extends TestCase {

  /**
   * Test to retrieve dataset schema properties.
   */
  public function test() {
    $schema = '{
      "properties":{
        "test":{
          "type":"string",
          "title":"Test field"
        },
        "downloadURL":{
          "type":"string",
          "format":"uri"
        }
      },
      "type":"object"
    }';
    $expected = [
      'test' => 'Test field (test)',
      'downloadURL' => 'DownloadURL',
    ];

    $chain = (new Chain($this))
      ->add(Container::class, 'get', SchemaRetriever::class)
      ->add(SchemaRetriever::class, 'retrieve', $schema);

    $schemaPropertiesHelper = SchemaPropertiesHelper::create($chain->getMock());
    $this->assertEquals($expected, $schemaPropertiesHelper->retrieveSchemaProperties());
  }

  /**
   * Test to retrieve string schema properties.
   */
  public function testRetrieveStringSchemaProperties() {
    $schema = '{
      "type":"object",
      "properties":{
        "@type":{
          "type":"string",
          "title":"Metadata Context"
        },
        "title":{
          "type":"string",
          "title":"Title"
        },
        "test":{
          "type":"string"
        },
        "theme": {
          "type":"array",
          "items": {
            "type": "string",
            "title": "Category"
          }
        },
        "contactPoint":{
          "type":"object",
          "properties": {
            "fn":{
              "type":"string",
              "title":"Contact Name"
            }
          }
        },
        "distribution": {
          "type":"array",
          "items": {
            "type": "object",
            "title": "Data File",
            "properties": {
              "title":{
                "type":"string",
                "title":"Title"
              }
            }
          }
        }
      }
    }';
    $expected = [
      'dataset_title' => 'Dataset: Title (title)',
      'dataset_test' => 'Dataset: Test',
      'contactPoint_fn' => 'ContactPoint: Contact Name (fn)',
      'distribution_title' => 'Distribution: Title (title)'
    ];

    $chain = (new Chain($this))
      ->add(Container::class, 'get', SchemaRetriever::class)
      ->add(SchemaRetriever::class, 'retrieve', $schema);

    $schemaPropertiesHelper = SchemaPropertiesHelper::create($chain->getMock());
    $this->assertEquals($expected, $schemaPropertiesHelper->retrieveStringSchemaProperties());
  }
}
