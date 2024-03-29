<?php

namespace Drupal\Tests\metastore\Unit;

use Drupal\Component\DependencyInjection\Container;
use Drupal\metastore\SchemaRetriever;
use Drupal\metastore\ValidMetadataFactory;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use RootedData\Exception\ValidationException;

/**
 *
 */
class ValidMetadataFactoryTest extends TestCase {

  public function testGetNoIdentifierException() {
    $validMetadataFactory = ValidMetadataFactory::create($this->getCommonMockChain()->getMock());
    $this->expectException(ValidationException::class);
    $validMetadataFactory->get(json_encode(['title' => 'blah']), 'dataset');
  }

  /**
   * UUID is added as "identifier" field if not present.
   *
   * Note: The "valid metadata" model in DKAN currently expects *all*
   * metadata types to include an "identifier" property. If a new metadata
   * item is posted without an identifier, it will be added.
   */
  public function testGetNoIdentifier() {
    $validMetadataFactory = ValidMetadataFactory::create($this->getCommonMockChain()->getMock());
    $result = $validMetadataFactory->get(json_encode(['title' => 'blah']), 'dataset', ['method' => 'POST']);
    $this->assertTrue(isset($result->{'$.identifier'}));
  }

  /**
   * Ensure that completely invalid JSON will throw exception.
   */
  public function testInvalidJson() {
    $validMetadataFactory = ValidMetadataFactory::create($this->getCommonMockChain()->getMock());
    $this->expectExceptionMessage("Invalid JSON: Syntax error");
    $validMetadataFactory->get('{"foo": "bar",}');
  }

  /**
   * Private.
   */
  private function getCommonMockChain() {
    $options = (new Options)
      ->add('metastore.schema_retriever', SchemaRetriever::class)
      ->index(0);

    $shortDatasetSchema = [
      'title' => 'Project Open Data Dataset',
      'required' => [
        'title',
        'identifier',
      ],
      'properties' => [
        'title' => [
          'title' => 'Title',
          'description' => 'Human-readable name of the asset. Should be in plain English and include sufficient detail to facilitate search and discovery.',
          'type' => 'string',
          'minLength' => 1,
        ],
        'identifier' => [
          'title' => 'Unique Identifier',
          'description' => 'A unique identifier for the dataset or API as maintained within an Agency catalog or database.',
          'type' => 'string',
          'minLength' => 1,
        ],
      ],
    ];

    return (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(SchemaRetriever::class, "retrieve", json_encode($shortDatasetSchema));
  }

}
