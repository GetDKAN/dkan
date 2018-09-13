<?php

use Dkan\Datastore\Manager\SimpleImport\SimpleImport;
use Dkan\Datastore\Resource;

/**
 * Class DkanDatastoreTest.
 */
class DkanDatastoreTest extends \PHPUnit_Framework_TestCase {

  private $resource_node;

  protected function setUp() {
    $node = (object) [];
    $node->title = "Datastore Resource Test Object 23523";
    $node->type = "resource";
    $node->field_link_remote_file['und'][0]['uri'] = "https://s3.amazonaws.com/dkan-default-content-files/district_centerpoints_small.csv";
    $node->status = 1;
    node_save($node);
    $this->resource_node = node_load($node->nid);
  }

  public function test() {

    $resource = new Resource($this->resource_node->nid, __DIR__ . "/data/countries.csv");

    /* @var $datastore \Dkan\Datastore\Manager\SimpleImport\SimpleImport */
    $datastore = (new \Dkan\Datastore\Manager\Factory($resource))->get();

    $status = $datastore->getStatus();
    $this->assertEquals(SimpleImport::STORAGE_UNINITIALIZED, $status['storage']);
    $this->assertEquals(SimpleImport::DATA_IMPORT_UNINITIALIZED, $status['data_import']);

    $datastore->import();

    $status = $datastore->getStatus();
    $this->assertEquals(SimpleImport::STORAGE_INITIALIZED, $status['storage']);
    $this->assertEquals(SimpleImport::DATA_IMPORT_DONE, $status['data_import']);


    $query = db_select($datastore->getTableName(), "d");
    $query->fields("d");
    $results = $query->execute();
    $results = $results->fetchAllAssoc("country");
    $json = json_encode($results);
    $this->assertEquals(
      "{\"US\":{\"country\":\"US\",\"population\":\"315209000\",\"id\":\"1\",\"timestamp\":\"1359062329\"},\"CA\":{\"country\":\"CA\",\"population\":\"35002447\",\"id\":\"2\",\"timestamp\":\"1359062329\"},\"AR\":{\"country\":\"AR\",\"population\":\"40117096\",\"id\":\"3\",\"timestamp\":\"1359062329\"},\"JP\":{\"country\":\"JP\",\"population\":\"127520000\",\"id\":\"4\",\"timestamp\":\"1359062329\"}}",
      $json);

    $this->assertEquals(4, $datastore->numberOfRecordsImported());

    $status = $datastore->getStatus();
    $this->assertEquals(SimpleImport::STORAGE_INITIALIZED, $status['storage']);
    $this->assertEquals(SimpleImport::DATA_IMPORT_DONE, $status['data_import']);

    $datastore->drop();
    $this->assertFalse(db_table_exists($datastore->getTableName()));

    $status = $datastore->getStatus();
    $this->assertEquals(SimpleImport::STORAGE_UNINITIALIZED, $status['storage']);
    $this->assertEquals(SimpleImport::DATA_IMPORT_UNINITIALIZED, $status['data_import']);
  }

  protected function tearDown() {
    node_delete($this->resource_node->nid);
  }

}
