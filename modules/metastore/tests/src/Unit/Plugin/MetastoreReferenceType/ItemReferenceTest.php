<?php

namespace Drupal\Tests\metastore\Unit\Plugin\MetastoreReferenceType;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Plugin\MetastoreReferenceType\ItemReference;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Service;
use Drupal\metastore\Service\Uuid5;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;
use MockChain\Chain;
use MockChain\Options;
use MockChain\ReturnNull;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class ItemReferenceTest extends TestCase {

  private string $new_value;
  private string $new_identifier;
  private string $existing_value;
  private string $existing_identifier;
  private string $bad_value;
  private string $bad_identifier;
  private \stdClass $distro_value;
  private string $distro_identifier;

  protected function setUp(): void {
    parent::setUp();
    $this->new_value = 'new keyword';
    $this->new_identifier = self::genId($this->new_value);

    $this->existing_value = 'existing keyword';
    $this->existing_identifier = self::genId($this->existing_value);

    $this->bad_value = 'bad keyword';
    $this->bad_identifier = self::genId($this->bad_value);

    $this->distro_value = (object) ['downloadUrl' => 'http://whatever'];
    $this->distro_identifier = self::genId($this->distro_value, 'distribution');

  }

  /**
   * Setup a container with a reactive metastore storage.
   */
  private function getContainer() {

    $retrieveByHash = (new Options())
      // No hash for new reference; it doesn't exist yet in storage.
      ->add(Service::metadataHash($this->new_value), new ReturnNull())
      // Simulate the stored hash for an existing reference.
      ->add(Service::metadataHash($this->existing_value), $this->existing_identifier)
      // For our "bad" reference, we don't find it in storage either.
      ->add(Service::metadataHash($this->bad_value), new ReturnNull())
      // Simulate the stored hash for an existing reference.
      ->add(Service::metadataHash($this->distro_value), $this->distro_identifier);

    $retrieve = (new Options())
      // Retrieving an existing metastore item should return a JSON string with
      // the "wrapped" identifier/data object structure.
      ->add([$this->existing_identifier, FALSE], json_encode(self::wrap($this->existing_identifier, $this->existing_value)))
      // Retrieving a non-existing identifier should thow an exception from
      // the storage.
      ->add([$this->bad_identifier, FALSE], new MissingObjectException());

    $store = (new Options())
      // Successfully storing a new keyword will return an identifier.
      ->add(
        [
          json_encode(self::wrap($this->new_identifier, $this->new_value)),
          $this->new_identifier,
        ], $this->new_identifier
      )
      // For some reason, storage failed. Throw exception.
      ->add(
        [
          json_encode(self::wrap($this->bad_identifier, $this->bad_value)),
          $this->bad_identifier,
        ], new EntityStorageException())
      // Store distribution reference.
      ->add(
        [
          json_encode(self::wrap($this->distro_identifier, $this->distro_value)),
          $this->distro_identifier,
        ], $this->distro_identifier
      );

    // Set up returns for the service container.
    $services = (new Options())
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.metastore.storage', DataFactory::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $services)
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      ->add(NodeData::class, 'retrieveByHash', $retrieveByHash)
      ->add(NodeData::class, 'store', $store)
      // For existing reference, test unpublished behavior.
      ->add(NodeData::class, 'isPublished', FALSE)
      ->add(NodeData::class, 'publish', TRUE)
      ->add(NodeData::class, 'retrieve', $retrieve)
      // For our distirbution test, let's pretend it's making a new revision.
      ->add(ResourceMapper::class, 'newRevision', TRUE);

    return $container_chain->getMock();
  }

  /**
   * Shortcut to get a real UUID for a sample value.
   */
  private static function genId($value, $schema_id = 'keyword'): string {
    return (new Uuid5())->generate($schema_id, $value);
  }

  /**
   * Wrap a value and an identifier in the current object structure.
   */
  private static function wrap(string $identifier, $value): object {
    return (object) [
      'identifier' => $identifier,
      'data' => $value,
    ];
  }

  public function testKeywordReference() {
    $definition = [
      'id' => 'item',
      'class' => ItemReference::class,
    ];
    $config = ['schemaId' => 'keyword', 'property' => 'keyword'];

    // Test for new reference.
    $itemReference = ItemReference::create($this->getContainer(), $config, 'item', $definition);
    $this->assertEquals($this->new_identifier, $itemReference->reference($this->new_value));

    // Test for existing reference.
    $this->assertEquals($this->existing_identifier, $itemReference->reference($this->existing_value));

    // Storage failed for some reason.
    $this->expectException(EntityStorageException::class);
    $itemReference->reference($this->bad_value);
  }

  public function testDistroReference() {
    $definition = [
      'id' => 'item',
      'class' => ItemReference::class,
    ];
    $config = ['schemaId' => 'distribution', 'property' => 'distribution'];

    $itemReference = ItemReference::create($this->getContainer(), $config, 'item', $definition);
    $this->assertEquals($this->distro_identifier, $itemReference->reference($this->distro_value));
  }

  public function testKeyWordDereference() {
    $definition = [
      'id' => 'item',
      'class' => ItemReference::class,
    ];
    $config = ['schemaId' => 'keyword', 'property' => 'keyword'];

    $itemReference = ItemReference::create($this->getContainer(), $config, 'item', $definition);

    // Test for normal value.
    $this->assertEquals($this->existing_value, $itemReference->dereference($this->existing_identifier));

    // Test for value w/showId.
    $showIdResult = self::wrap($this->existing_identifier, $this->existing_value);
    $this->assertEquals($showIdResult, $itemReference->dereference($this->existing_identifier, TRUE));

    // Test for bad reference.
    $this->assertNull($itemReference->dereference($this->bad_identifier));
  }

}
