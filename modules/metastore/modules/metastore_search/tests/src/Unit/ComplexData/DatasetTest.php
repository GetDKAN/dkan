<?php

namespace Drupal\Tests\metastore_search\Unit\ComplexData;

use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\metastore_search\ComplexData\Dataset;
use MockChain\Chain;
use Drupal\metastore\SchemaRetriever;
use PHPUnit\Framework\TestCase;
use MockChain\Options;
use Drupal\Core\DependencyInjection\Container;

/**
 * Class DatasetTest.
 *
 * @package Drupal\Tests\metastore_search\Unit\ComplexData
 * @group metastore_search
 */
class DatasetTest extends TestCase {

  /**
   *
   */
  public function test() {
    $schema = '
    {
      "$id": "https://example.com/person.schema.json",
      "$schema": "http://json-schema.org/draft-07/schema#",
      "title": "Person",
      "type": "object",
      "properties": {
        "firstName": {
          "type": "string",
          "description": "First name."
        },
        "lastName": {
          "type": "string",
          "description": "Last name."
        },
        "occupations" : {
          "type": "array"
        },
        "age": {
          "description": "Age in years which must be equal to or greater than zero.",
          "type": "integer",
          "minimum": 0
        },
        "address": {
          "type": "object",
          "properties": {
            "city": {
              "coords": "array",
              "items": {
                "type": "string"
              }
            },
            "state": {
              "type": "string"
            }
          }
        }
      }
    }
    ';

    $options = (new Options())
      ->add('dkan.metastore.schema_retriever', SchemaRetriever::class)
      ->add('typed_data_manager', TypedDataManagerInterface::class)
      ->index(0);

    $container = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(SchemaRetriever::class, 'retrieve', $schema)
      ->getMock();

    \Drupal::setContainer($container);

    $collection = (object) ['firstName' => 'hello', 'lastName' => 'goodbye', 'age' => 5000, 'occupations' => ['teacher']];
    $json = json_encode($collection);
    $dataset = new Dataset($json);
    $this->assertEquals($json, json_encode($dataset->getValue()));

    $properties = $dataset->getProperties();
    $this->assertEquals(6, count($properties));
  }

}
