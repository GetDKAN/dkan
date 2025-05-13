<?php

namespace Drupal\Tests\datastore\Functional\Api1;

use Drupal\Tests\common\Functional\Api1TestBase;
use GuzzleHttp\RequestOptions;

/**
 * Tests the Datastore Query API.
 *
 * @group dkan
 * @group datastore
 * @group api1
 * @group functional
 * @group functional2
 */
 class DatastoreQueryApiTest extends Api1TestBase {

  public function getEndpoint():string {
    return 'api/1/metastore/schemas/dataset/items';
  }

  /**
   * Confirm a 400 response prior to running the datastore_import.
   */
  public function testBasicQuery() {
    $dataset = $this->getSampleDataset();
    $this->post($dataset, FALSE);
    $dataset_id = $dataset->identifier;

    $response = $this->httpClient->get("api/1/datastore/query/$dataset_id/0", [
      RequestOptions::HTTP_ERRORS => FALSE,
    ]);
    $this->assertEquals(400, $response->getStatusCode());
    $this->validator->validate($response, "/api/1/datastore/query/{datasetId}/{index}", 'get');

    // Confirm a 200 response after the datastore_import has run.
    $dataset_info = \Drupal::service('dkan.common.dataset_info')->gather($dataset_id);
    $resource_id = $dataset_info['latest_revision']['distributions'][0]['resource_id'];
    \Drupal::service('dkan.datastore.service')->import($resource_id, FALSE);
    $response = $this->httpClient->get("api/1/datastore/query/$dataset_id/0", [
      RequestOptions::HTTP_ERRORS => FALSE,
    ]);
    $this->assertEquals(200, $response->getStatusCode());
    $this->validator->validate($response, "/api/1/datastore/query/{datasetId}/{index}", 'get');
  }

  /**
   * Query on in progress datastore updates.
   *
   * Confirm a 400 response after a resource is updated, localize_import
   * has run, but the datastore_import queue has not yet run.
   */
  public function testBasicQueryAfterTableDrop() {
    $dataset = $this->getSampleDataset();
    $this->post($dataset, FALSE);
    $dataset_id = $dataset->identifier;

    $dataset_info = \Drupal::service('dkan.common.dataset_info')->gather($dataset_id);
    $resource_id = $dataset_info['latest_revision']['distributions'][0]['resource_id'];
    \Drupal::service('dkan.datastore.service')->import($resource_id, FALSE);

    $dataset_info = \Drupal::service('dkan.common.dataset_info')->gather($dataset_id);
    $datastore_table = $dataset_info['latest_revision']['distributions'][0]['table_name'];
    \Drupal::database()->schema()->dropTable($datastore_table);

    $response = $this->httpClient->get("api/1/datastore/query/$dataset_id/0", [
      RequestOptions::HTTP_ERRORS => FALSE,
    ]);
    $this->assertEquals(400, $response->getStatusCode());
    $this->validator->validate($response, "/api/1/datastore/query/{datasetId}/{index}", 'get');
  }

}
