<?php

use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\dkan_search\ComplexData\Dataset;
use MockChain\Chain;
use Drupal\dkan_schema\SchemaRetriever;
use PHPUnit\Framework\TestCase;
use MockChain\Options;
use Drupal\Core\DependencyInjection\Container;

/**
 *
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
          "description": "The person\'s first name."
        },
        "lastName": {
          "type": "string",
          "description": "The person\'s last name."
        },
        "occupations" : {
          "type": "array"
        },
        "age": {
          "description": "Age in years which must be equal to or greater than zero.",
          "type": "integer",
          "minimum": 0
        }
      }
    }
    ';

    $options = (new Options())
      ->add('dkan_schema.schema_retriever', SchemaRetriever::class)
      ->add('typed_data_manager', TypedDataManagerInterface::class);

    $container = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(SchemaRetriever::class, 'retrieve', $schema)
      ->getMock();

    \Drupal::setContainer($container);

    $thing = (object) ['firstName' => 'hello', 'lastName' => 'goodbye', 'age' => 5000, 'occupations' => ['teacher']];
    $json = json_encode($thing);
    $dataset = new Dataset($json);
    $this->assertEquals($json, json_encode($dataset->getValue()));

    $properties = $dataset->getProperties();
    $this->assertEquals(4, count($properties));
  }

}
