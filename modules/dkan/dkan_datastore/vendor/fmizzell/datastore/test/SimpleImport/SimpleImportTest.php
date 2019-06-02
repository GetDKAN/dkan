<?php

use Dkan\Datastore\Manager\SimpleImport\SimpleImport;
use Dkan\Datastore\Resource;

class SimpleImportTest extends \PHPUnit\Framework\TestCase {

  private $database;

  /**
   * This method is called before each test.
   */
  protected function setUp()
  {
    $this->database = new \Dkan\Datastore\Storage\Database\Memory();
  }

  private function getDatastore(Resource $resource) {

    $provider = new \Dkan\Datastore\Manager\InfoProvider();
    $provider->addInfo(new \Dkan\Datastore\Manager\Info(SimpleImport::class, "simple_import", "SimpleImport"));

    $bin_storage = new \Dkan\Datastore\LockableBinStorage("dkan_datastore", new \Dkan\Datastore\Locker("dkan_datastore"), new \Dkan\Datastore\Storage\KeyValue\Memory());

    $factory = new \Dkan\Datastore\Manager\Factory($resource, $provider, $bin_storage, $this->database);

    return $factory->get();
  }

  public function testBasics() {
    $resource = new Resource(1, __DIR__ . "/data/countries.csv");

    /* @var $datastore \Dkan\Datastore\Manager\SimpleImport\SimpleImport */
    $datastore = $this->getDatastore($resource);

    $status = $datastore->getStatus();
    $this->assertEquals(SimpleImport::STORAGE_UNINITIALIZED, $status['storage']);
    $this->assertEquals(SimpleImport::DATA_IMPORT_UNINITIALIZED, $status['data_import']);

    $datastore->import();

    $status = $datastore->getStatus();
    $this->assertEquals(SimpleImport::STORAGE_INITIALIZED, $status['storage']);
    $this->assertEquals(SimpleImport::DATA_IMPORT_DONE, $status['data_import']);

    $this->assertEquals(4, $datastore->numberOfRecordsImported());

    $status = $datastore->getStatus();
    $this->assertEquals(SimpleImport::STORAGE_INITIALIZED, $status['storage']);
    $this->assertEquals(SimpleImport::DATA_IMPORT_DONE, $status['data_import']);

    $datastore->drop();
    $this->assertFalse($this->database->tableExist($datastore->getTableName()));

    $status = $datastore->getStatus();
    $this->assertEquals(SimpleImport::STORAGE_UNINITIALIZED, $status['storage']);
    $this->assertEquals(SimpleImport::DATA_IMPORT_UNINITIALIZED, $status['data_import']);
  }

  public function testOver1000() {
    $resource = new Resource(1, __DIR__ . "/data/Bike_Lane.csv");

    /* @var $datastore \Dkan\Datastore\Manager\SimpleImport\SimpleImport */
    $datastore = $this->getDatastore($resource);
    $datastore->import();

    $this->assertEquals(2969, $datastore->numberOfRecordsImported());

    $expected = '["2049","75000403","R","1","DESIGNATED","0.076","0.364","463.2487"]';
    $this->assertEquals($expected, $this->database->tables['dkan_datastore_1'][0]);

    $expected = '["2048","75000402","R","1","DESIGNATED","0.769","1.713","1528.0913"]';
    $this->assertEquals($expected, $this->database->tables['dkan_datastore_1'][2968]);

  }

}
