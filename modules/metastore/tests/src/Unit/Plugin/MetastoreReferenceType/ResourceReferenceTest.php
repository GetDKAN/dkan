<?php

namespace Drupal\Tests\metastore\Unit\Plugin\MetastoreReferenceType;

use Drupal\common\DataResource;
use Drupal\common\Storage\DatabaseTableInterface;
use Drupal\Component\Datetime\Time;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Http\RequestStack;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\metastore\Exception\AlreadyRegistered;
use Drupal\metastore\Plugin\MetastoreReferenceType\ResourceReference;
use Drupal\metastore\ResourceMapper;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

class ResourceReferenceTest extends TestCase {

  /**
   * An arbitrary timestamp so we don't get mismatched versions.
   */
  const TIME = '1679494210';

  /**
   * Plugin definition.
   *
   * @var array
   */
  private array $definition;

  /**
   * Plugin config.
   *
   * @var array
   */
  private array $config;

  private string $new_url;
  private string $new_identifier;
  private string $local_url;
  private string $local_resolved_url;
  private string $local_identifier;
  private string $existing_url;
  private string $existing_identifier;
  private string $tsv_url;
  private string $tsv_identifier;
  private string $bad_url;
  private string $bad_identifier;

  protected function setUp(): void {
    parent::setUp();

    $this->definition = [
      'id' => 'item',
      'class' => ResourceReference::class,
    ];

    $this->config = ['property' => 'downloadURL'];

    // New resource in the system.
    $this->new_url = 'http://sample.com/newfile.csv';
    $this->new_identifier = self::genId($this->new_url);

    // Existing resource in system that doesn't trigger revision.
    $this->existing_url = 'http://sample.com/existing.csv';
    $this->existing_identifier = self::genId($this->existing_url);

    // File already located on webserver.
    $this->local_url = 'http://mysite.com/local.csv';
    $this->local_resolved_url = 'http://h-o.st/local.csv';
    $this->local_identifier = self::genId($this->local_resolved_url);

    // Existing resource in system that doesn't trigger revision.
    $this->tsv_url = 'http://sample.com/data.tsv';
    $this->tsv_identifier = self::genId($this->tsv_url);
    
    $this->bad_url = 'http://sample.com/bad.csv';
    $this->bad_identifier = self::genId($this->bad_url);

    // We still have a static method calling \Drupal::service()
    $this->setContainer();
  }

  private function setContainer() {
    $services = (new Options())
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->add('request_stack', RequestStack::class)
      ->add('datetime.time', Time::class)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $services)
      ->add(StreamWrapperManager::class, 'getViaUri', StreamWrapperInterface::class)
      // Fake stream wrapper to simulate local URL.
      ->add(StreamWrapperInterface::class, 'getExternalUrl', 'http://mysite.com')
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getHost', 'host')
      ->add(Time::class, 'getCurrentTime', self::TIME);

    \Drupal::setContainer($container_chain->getMock());
  }

  /**
   * Setup a container with a reactive metastore storage.
   */
  private function getContainer($new_revision = 0) {

    $filePathExists = (new Options())
      // For the new URL, a filePath does not yet exist.
      ->add($this->new_url, FALSE)
      // We are also making the local URL a new resource.
      ->add($this->local_resolved_url, FALSE)
      // For the existing one, it does, so we'll expect an exception.
      ->add($this->existing_url, new AlreadyRegistered(json_encode([
        (object) [
          "identifier" => $this->existing_identifier,
          "perspective" => 'source',
        ],
      ])))
        // TSV file, let's say its a new one.
      ->add($this->tsv_url, FALSE);

    $latestRevision = (new Options())
      // For an existing URL, we simulate a record in the mapper table.
      ->add($this->existing_identifier, self::mapperTableRow($this->existing_url, $this->existing_identifier))
      ->add(self::extract($this->existing_identifier, "identifier"), self::mapperTableRow($this->existing_url, $this->existing_identifier))
      ->index(0);

    $store = (new Options())
      // Successfully storing a new keyword will return an identifier.
      ->add(
        new DataResource($this->new_url, 'text/csv', DataResource::DEFAULT_SOURCE_PERSPECTIVE),
        $this->new_identifier
      )
      // The local URL is also new and will have to be stored.
      ->add(
        new DataResource($this->local_resolved_url, 'text/csv', DataResource::DEFAULT_SOURCE_PERSPECTIVE),
        $this->local_identifier
      )
      // Existing resource when creating new revision.
      ->add(
        (new DataResource($this->existing_url, 'text/csv', DataResource::DEFAULT_SOURCE_PERSPECTIVE))->createNewVersion(),
        $this->local_identifier
      )
      // TSV file is new.
      ->add(
        (new DataResource($this->tsv_url, 'text/tab-separated-values', DataResource::DEFAULT_SOURCE_PERSPECTIVE)),
        $this->tsv_identifier
      )
      ->index(0);

    $revision = (new Options())
      // Retrieve a db row for the existing resource.
      ->add([
        self::extract($this->existing_identifier, 'identifier'),
        self::extract($this->existing_identifier, 'perspective'),
        self::extract($this->existing_identifier, 'version'),
      ], self::mapperTableRow($this->existing_url, $this->existing_identifier));

    // Set up returns for the service container.
    $services = (new Options())
      ->add('logger.factory', LoggerChannelFactory::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->add('file_system', FileSystemInterface::class)
      ->add('entity_type.manager', EntityTypeManager::class)
      ->index(0);

    $container_chain = (new Chain($this))
      ->add(Container::class, 'get', $services)
      ->add(EntityTypeManager::class, 'getStorage', EntityStorageInterface::class)
      ->add(ResourceMapper::class, 'filePathExists', $filePathExists)
      ->add(ResourceMapper::class, 'getStore', DatabaseTableInterface::class)
      ->add(ResourceMapper::class, 'dispatchEvent', [])
      ->add(ResourceMapper::class, 'getLatestRevision', $latestRevision)
      ->add(ResourceMapper::class, 'getRevision', $revision)
      ->add(ResourceMapper::class, 'newRevision', $new_revision)
      ->add(ResourceMapper::class, 'validateNewVersion', TRUE)
      ->add(DatabaseTableInterface::class, 'store', $store);

    return $container_chain->getMock();
  }

  /**
   * Shortcut to get a full resource identifier/version/perspective.
   */
  private static function genId($url, $perspective = 'source'): string {
    $hash = md5($url);
    return DataResource::buildUniqueIdentifier($hash, self::TIME, $perspective);
  }

  /**
   * Simulate the return of a DB query against file mapper table.
   */
  private static function mapperTableRow(string $filepath, string $identifier): object {
    return (object) [
      'identifier' => self::extract($identifier, 'identifier'),
      'version' => (int) self::extract($identifier, 'version'),
      'filePath' => $filepath,
      'perspective' => substr($identifier, 46),
      'mimeType' => 'text/csv',
      'checksum' => NULL,
    ];
  }

  /**
   * Get a specific substring of a full identifier string.
   *
   * @param string $full_identifier
   *   Full identifier string, e.g. 7e174878bc2140d04334d0cedf1f3931__1679494210__source
   * @param string $part
   *   Which part; can be "identifier, "version" or "perspective".
   *
   * @return string
   *   The substring requested.
   *
   * @throws \OutOfBoundsException
   */
  private static function extract(string $full_identifier, string $part): string {
    switch ($part) {
      case 'identifier':
        return substr($full_identifier, 0, 32);

      case 'version':
        return substr($full_identifier, 34, 10);

      case 'perspective':
        return substr($full_identifier, 46);

      default:
        throw new \OutOfBoundsException("\"$part\" is not a valid part");
    }
  }

  /**
   * Wrap a value and an identifier in the current object structure.
   */
  private static function distribution(string $url, array $properties = ['mediaType' => 'text/csv']): object {
    return (object) (['downloadURL' => $url] + $properties);
  }

  public function testReference() {
    // Test for new reference.
    $resourceReference = ResourceReference::create($this->getContainer(), $this->config, 'resource', $this->definition);

    // This should successfully run the registerWithResourceMapper method.
    $resourceReference->setContext(self::distribution($this->new_url));
    $this->assertEquals($this->new_identifier, $resourceReference->reference($this->new_url));

    // A new resource, except it's from the local domain.
    $resourceReference->setContext(self::distribution($this->local_url));
    $this->assertEquals($this->local_identifier, $resourceReference->reference($this->local_url));

    $resourceReference->setContext(self::distribution($this->existing_url));
    $this->assertEquals($this->existing_identifier, $resourceReference->reference($this->existing_url));    

    // // Storage failed for some reason.
    // $this->expectException(EntityStorageException::class);
    // $itemReference->reference($this->bad_url);
  }

  public function testNewRevisionReference() {
    // We expect an identifier for a new revision of existing.
    $existing_new_revision = implode('__', [
      self::extract($this->existing_identifier, 'identifier'),
      (string ) ((int) self::extract($this->existing_identifier, 'version') + 1),
      DataResource::DEFAULT_SOURCE_PERSPECTIVE,
    ]);
    $resourceReference = ResourceReference::create($this->getContainer(1), $this->config, 'resource', $this->definition);
    $resourceReference->setContext(self::distribution($this->existing_url));
    $this->assertEquals($existing_new_revision, $resourceReference->reference($this->existing_url));    
  }

  public function testReferenceCsvFormat() {
    $resourceReference = ResourceReference::create($this->getContainer(), $this->config, 'resource', $this->definition);
    $resourceReference->setContext(self::distribution($this->existing_url, ['format' => 'csv']));
    $this->assertEquals($this->existing_identifier, $resourceReference->reference($this->existing_url));    
  }

  public function testReferenceTsvFormat() {
    $resourceReference = ResourceReference::create($this->getContainer(), $this->config, 'resource', $this->definition);
    $resourceReference->setContext(self::distribution($this->tsv_url, ['format' => 'tsv']));
    $this->assertEquals($this->tsv_identifier, $resourceReference->reference($this->tsv_url));

    // New let's try conflicting formats.
    $resourceReference->setContext(self::distribution($this->tsv_url, [
      'format' => 'csv',
      'mediaType' => 'text/tab-separated-values',
    ]));
    // (If mimetype is parsed wrong, this would fail to match the option in the
    // DatabaseTable::store() mock.)
    $this->assertEquals($this->tsv_identifier, $resourceReference->reference($this->tsv_url));
  }

  public function testDereference() {
    $definition = [
      'id' => 'item',
      'class' => ResourceReference::class,
    ];
    $config = ['property' => 'downloadURL'];

    $itemReference = ResourceReference::create($this->getContainer(), $config, 'resource', $definition);
    // Make sure an existing remote URL comes back correctly.
    $this->assertEquals($this->existing_url, $itemReference->dereference($this->existing_identifier));

    // Test with showID.
    $showIdResult[] = (object) [
      'identifier' => $this->existing_identifier,
      'data' => new DataResource($this->existing_url, 'text/csv', 'source'),
    ];
    $this->assertEquals($showIdResult, $itemReference->dereference($this->existing_identifier, TRUE));

    // Test URL stored instead of identifier
    $this->assertEquals($this->existing_url, $itemReference->dereference($this->existing_url));
    
    // Test for value w/showId.
    // $showIdResult = self::wrap($this->existing_identifier, $this->existing_value);
    // $this->assertEquals($showIdResult, $itemReference->dereference($this->existing_identifier, TRUE));

    // // Test for bad reference.
    // $this->assertNull($itemReference->dereference($this->bad_identifier));
  }


}
