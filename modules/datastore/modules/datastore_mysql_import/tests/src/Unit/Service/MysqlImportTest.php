<?php

namespace Drupal\Tests\dastastore_mysql_import\Unit\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\File\FileSystem;
use Drupal\Core\StreamWrapper\StreamWrapperManager;

use Drupal\common\Resource;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\datastore\Service\Import as Service;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\datastore_mysql_import\Service\MysqlImport;

use Dkan\Datastore\Importer;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
class MysqlImportTest extends TestCase {

  protected const HOST = "http://example.org";

  /**
   *
   */
  public function testMysqlImporter() {
    $options = (new Options())
      ->add('database', Connection::class)
      ->add('event_dispatcher', ContainerAwareEventDispatcher::class)
      ->add('file_system', FileSystem::class)
      ->add('request_stack', RequestStack::class)
      ->add('stream_wrapper_manager', StreamWrapperManager::class)
      ->index(0);

    $filepath = "file://" . __DIR__ . "/../../../../../../tests/data/countries.csv";

    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(FileSystem::class, 'realpath', $filepath)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getHost', self::HOST)
      ->getMock();

    \Drupal::setContainer($container);

    $resource = new Resource(self::HOST . '/text.csv', "text/csv");

    $jobStore = (new Chain($this))
      ->add(JobStore::class, "retrieve", "")
      ->add(Importer::class, "run", Result::class)
      ->add(Importer::class, "getResult", Result::class)
      ->add(JobStore::class, "store", "")
      ->getMock();

    $databaseTableFactory = (new Chain($this))
      ->add(DatabaseTableFactory::class, "getInstance", DatabaseTable::class)
      ->add(DatabaseTable::class, 'count', 4)
      ->getMock();

    $jobStoreFactory = (new Chain($this))
      ->add(JobStoreFactory::class, "getInstance", $jobStore)
      ->getMock();

    $service = new Service($resource, $jobStoreFactory, $databaseTableFactory);
    $service->setImporterClass(MysqlImport::class);
    $service->import();

    $result = $service->getResult();
    $this->assertTrue($result instanceof Result);
  }

}
