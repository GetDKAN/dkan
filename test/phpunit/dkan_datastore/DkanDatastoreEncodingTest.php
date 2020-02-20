<?php

use Dkan\Datastore\Manager\SimpleImport\SimpleImport;
use Dkan\Datastore\Resource;

/**
 * Class DkanDatastoreEncodingTest.
 */
class DkanDatastoreEncodingTest extends \PHPUnit_Framework_TestCase {

  private $resource_node;

  protected function setUp() {
    $node = (object) [];
    $node->title = "Datastore Resource Test Object 23525";
    $node->type = "resource";
    $node->field_link_remote_file['und'][0]['uri'] = "https://s3.amazonaws.com/dkan-default-content-files/district_centerpoints_small.csv";
    $node->status = 1;
    node_save($node);
    $this->resource_node = node_load($node->nid);
  }

  public function test() {

    $resource = new Resource($this->resource_node->nid, __DIR__ . "/data/win1252_encoding.csv");

    /* @var $datastore \Dkan\Datastore\Manager\SimpleImport\SimpleImport */
    $datastore = (new \Dkan\Datastore\Manager\Factory($resource))->get();

    $status = $datastore->getStatus();
    $this->assertEquals(SimpleImport::STORAGE_UNINITIALIZED, $status['storage']);
    $this->assertEquals(SimpleImport::DATA_IMPORT_UNINITIALIZED, $status['data_import']);

    $properties = $datastore->getConfigurableProperties();
    $this->assertEquals($properties['encoding']['PHP'], 'UTF-8');
    $properties['encoding']['PHP'] = 'Windows-1252';
    $datastore->setConfigurableProperties($properties);

    $datastore->import();

    $status = $datastore->getStatus();
    $this->assertEquals(SimpleImport::STORAGE_INITIALIZED, $status['storage']);
    $this->assertEquals(SimpleImport::DATA_IMPORT_DONE, $status['data_import']);


    $query = db_select($datastore->getTableName(), "d");
    $query->fields("d");
    $results = $query->execute();
    $results = $results->fetchAllAssoc("second_column");
    $json = json_encode($results);
    $this->assertEquals(
      "{\"1\":{\"title\":\"\u00a9\u00a5 special characters\",\"second_column\":\"1\",\"entry_id\":\"1\"},\"2\":{\"title\":\"\u00bc \u00bd \u00be special fraction\",\"second_column\":\"2\",\"entry_id\":\"2\"}}",
      $json);

    $this->assertEquals(2, $datastore->numberOfRecordsImported());

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
