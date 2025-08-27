<?php

namespace Drupal\Tests\metastore\Functional\Storage;

use ColinODell\PsrTestLogger\TestLogger;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Tests\BrowserTestBase;
use Drupal\metastore\Storage\Data;
use Psr\Log\LoggerInterface;
use org\bovigo\vfs\vfsStream;

/**
 * Test the Data class.
 *
 * Because of the tight coupling and unification of concerns, this is a
 * BrowserTestBase test. A Kernel test would be too complex and fragile.
 *
 * @covers \Drupal\metastore\Storage\Data
 * @coversDefaultClass \Drupal\metastore\Storage\Data
 *
 * @group dkan
 * @group metastore
 * @group functional
 * @group btb
 * @group functional2
 */
class DataTest extends BrowserTestBase {

  protected $defaultTheme = 'stark';

  protected static $modules = [
    'metastore',
    'node',
  ];

  /**
   * Test the logging of htmlPurifier().
   *
   * @covers ::htmlPurifier
   */
  public function testHtmlPurifierLogging() {
    // Set up a read-only temp directory.
    $temp = vfsStream::setup('temp');
    $temp_dir = vfsStream::newDirectory('mytemp', 0000)
      ->at($temp);

    // Tell the file system service to use this temp directory, which will
    // prevent the HTML purifier from writing its cache directory, and it
    // should log this fact.
    $fs = $this->getMockBuilder(FileSystem::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getTempDirectory'])
      ->getMock();
    $fs->expects($this->any())
      ->method('getTempDirectory')
      ->willReturn($temp_dir->url());

    // Let's keep the old filesystem object so we can set it back.
    $old_fs = $this->container->get('file_system');
    $this->container->set('file_system', $fs);

    // Create a Data object with a testable logger.
    $logger = new TestLogger();
    $data = new StubData(
      'dataset',
      $this->container->get('entity_type.manager'),
      $this->container->get('config.factory'),
      $logger
    );

    $uuid = '05aea36e-9e24-452e-9cf9-9727ab90c198';

    // Since filterHtml() and htmlPurifier() are private, we call in from
    // store() with crafted data.
    $identifier = $data->store(json_encode((object) [
      'identifier' => $uuid,
      'title' => 'title',
      'data' => (object) [
        'description' => 'purify me',
      ],
    ]));

    // We stored a node.
    $this->assertSame($uuid, $identifier);
    // We logged that the cache directory was not created.
    $this->assertTrue(
      $logger->hasErrorThatContains('Failed to create cache directory for HTML purifier')
    );

    // Test can't clean up if we don't set back the old file system service.
    $this->container->set('file_system', $old_fs);
  }

}

/**
 * Stub out the abstract Data class with just enough to make it run.
 *
 * Values are copied from NodeData.
 */
class StubData extends Data {

  public function __construct(string $schemaId, EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $config_factory, LoggerInterface $loggerChannel) {
    $this->entityType = 'node';
    $this->bundle = 'data';
    $this->bundleKey = 'type';
    $this->labelKey = 'title';
    $this->schemaIdField = 'field_data_type';
    $this->metadataField = 'field_json_metadata';
    parent::__construct($schemaId, $entityTypeManager, $config_factory, $loggerChannel);
  }

  public function retrieveContains(string $string, bool $caseSensitive): array {
    return [];
  }

  public function retrieveByHash($hash, $schemaId) {
  }

}
