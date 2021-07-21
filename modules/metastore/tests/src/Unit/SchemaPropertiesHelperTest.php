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
   * Test.
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

}
