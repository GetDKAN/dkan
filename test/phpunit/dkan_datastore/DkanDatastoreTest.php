<?php

use Dkan\Datastore\Manager\SimpleImport;

/**
 * Class DkanDatastoreTest.
 */
class DkanDatastoreTest extends \PHPUnit_Framework_TestCase {

  public function test() {
    print_r(PHP_EOL);

    $resource = new \Dkan\Datastore\Resource("999999", __DIR__ . "/data/countries.csv");

    /* @var $datastore \Dkan\Datastore\Manager\SimpleImport */
    $datastore = \Dkan\Datastore\Manager\Factory::create($resource, SimpleImport::class);

    $status = $datastore->getStatus();
    $this->assertEquals(SimpleImport::STORAGE_UNINITIALIZED, $status['storage']);
    $this->assertEquals(SimpleImport::DATA_IMPORT_UNINITIALIZED, $status['data_import']);

    $datastore->import();

    $status = $datastore->getStatus();
    $this->assertEquals(SimpleImport::STORAGE_INITIALIZED, $status['storage']);
    $this->assertEquals(SimpleImport::DATA_IMPORT_DONE, $status['data_import']);


    $query = db_query("SELECT * FROM {$datastore->getTableName()};");
    $results = $query->fetchAllAssoc("country");
    $json = json_encode($results);
    $this->assertEquals(
      "{\"US\":{\"country\":\"US\",\"population\":\"315209000\",\"id\":\"1\",\"timestamp\":\"1359062329\"},\"CA\":{\"country\":\"CA\",\"population\":\"35002447\",\"id\":\"2\",\"timestamp\":\"1359062329\"},\"AR\":{\"country\":\"AR\",\"population\":\"40117096\",\"id\":\"3\",\"timestamp\":\"1359062329\"},\"JP\":{\"country\":\"JP\",\"population\":\"127520000\",\"id\":\"4\",\"timestamp\":\"1359062329 \"}}",
      $json);

    $this->assertEquals(4, $datastore->numberOfRecordsImported());

    $datastore->deleteRows();
    $this->assertEquals(0, $datastore->numberOfRecordsImported());

    $status = $datastore->getStatus();
    $this->assertEquals(SimpleImport::STORAGE_INITIALIZED, $status['storage']);
    $this->assertEquals(SimpleImport::DATA_IMPORT_UNINITIALIZED, $status['data_import']);

    $datastore->drop();
    $this->assertFalse(db_table_exists($datastore->getTableName()));

    $status = $datastore->getStatus();
    $this->assertEquals(SimpleImport::STORAGE_UNINITIALIZED, $status['storage']);
    $this->assertEquals(SimpleImport::DATA_IMPORT_UNINITIALIZED, $status['data_import']);
  }

}
