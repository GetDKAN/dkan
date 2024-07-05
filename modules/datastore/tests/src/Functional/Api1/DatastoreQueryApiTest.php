<?php

namespace Drupal\Tests\datastore\Functional\Api1;

use Drupal\Tests\common\Functional\Api1TestBase;
use GuzzleHttp\RequestOptions;

class DatastoreQueryApiTest extends Api1TestBase {

  public function getEndpoint():string {
    return 'api/1/metastore/schemas/dataset/items';
  }

  public function testBasicQuery() {
    $dataset = $this->getSampleDataset();
    $this->post($dataset, FALSE);

    $dataset_id = $dataset->identifier;
    $response = $this->httpClient->get("api/1/datastore/query/$dataset_id/0", [
      RequestOptions::HTTP_ERRORS => FALSE,
    ]);
    $this->assertEquals(400, $response->getStatusCode());
    $this->validator->validate($response, "/api/1/datastore/query/{datasetId}/{index}", 'get');

    $dataset_info = \Drupal::service('dkan.common.dataset_info')->gather($dataset_id);
    $resource_id = $dataset_info['latest_revision']['distributions'][0]['resource_id'];
    \Drupal::service('dkan.datastore.service')->import($resource_id, FALSE);
    $response = $this->httpClient->get("api/1/datastore/query/$dataset_id/0", [
      RequestOptions::HTTP_ERRORS => FALSE,
    ]);
    $this->assertEquals(200, $response->getStatusCode());
    $this->validator->validate($response, "/api/1/datastore/query/{datasetId}/{index}", 'get');
  }
}