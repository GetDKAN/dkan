<?php

namespace Drupal\Tests\metastore\Functional\Api1;

use Drupal\Tests\common\Functional\Api1TestBase;
use GuzzleHttp\RequestOptions;

class DatasetItemTest extends Api1TestBase {

  public function getEndpoint():string {
    return 'api/1/metastore/schemas/dataset/items';
  }

  public function testGet() {
    $dataset = $this->getSampleDataset();

    $response = $this->post($dataset, FALSE);
    $this->assertDatasetGet($dataset);

    $this->post($this->getSampleDataset(1));

    $response = $this->httpClient->request('GET', $this->endpoint);
    $responseBody = json_decode($response->getBody());
    $this->assertEquals(2, count($responseBody));
    $this->assertTrue(is_object($responseBody[1]));
    // Have to use this path because the endpoint as added is not in the spec.
    // @todo Simplify dataset vs {schema_id} items in the spec.
    $this->validator->validate($response, "api/1/metastore/schemas/{schema_id}/items", 'get');

    $datasetId = 'abc-123';
    $response = $this->httpClient->get("$this->endpoint/$datasetId", [
      RequestOptions::HTTP_ERRORS => FALSE,
    ]);
    $this->assertEquals(404, $response->getStatusCode());

    $this->validator->validate($response, "$this->endpoint/$datasetId", 'get');
  }

  public function testPost() {
    $dataset = $this->getSampleDataset();
    $response = $this->post($dataset);
    $this->assertEquals(201, $response->getStatusCode());

    $this->validator->validate($response, $this->endpoint, 'post');
    $this->assertDatasetGet($dataset);

    // Now try a duplicate.
    $response = $this->post($dataset, FALSE);
    $this->assertEquals(409, $response->getStatusCode());
    $this->validator->validate($response, $this->endpoint, 'post');
  }

  public function testPatch() {
    $dataset = $this->getSampleDataset();
    $this->post($dataset);
    $datasetId = $dataset->identifier;

    $newTitle = (object) ['title' => 'Modified Title'];
    $response = $this->httpClient->patch("$this->endpoint/$datasetId", [
      RequestOptions::JSON => $newTitle,
      RequestOptions::AUTH => $this->auth,
    ]);

    $this->assertEquals(200, $response->getStatusCode());

    $this->validator->validate($response, "$this->endpoint/$datasetId", 'patch');

    $dataset->title = $newTitle->title;
    $this->assertDatasetGet($dataset);

    // Now, try with a non-existent identifier.
    $datasetId = "abc-123";
    $newTitle = (object) ['title' => 'Modified Title'];

    $response = $this->httpClient->patch("$this->endpoint/$datasetId", [
      RequestOptions::HTTP_ERRORS => FALSE,
      RequestOptions::JSON => $newTitle,
      RequestOptions::AUTH => $this->auth,
    ]);

    $this->assertEquals(412, $response->getStatusCode());
    $this->validator->validate($response, "$this->endpoint/$datasetId", 'patch');

  }

  public function testPut() {
    $dataset = $this->getSampleDataset();
    $this->post($dataset);

    $datasetId = $dataset->identifier;
    $newDataset = $this->getSampleDataset(1);
    $newDataset->identifier = $datasetId;

    $response = $this->httpClient->put("$this->endpoint/$datasetId", [
      RequestOptions::JSON => $newDataset,
      RequestOptions::AUTH => $this->auth,
    ]);
    $this->assertEquals(200, $response->getStatusCode());
    $this->validator->validate($response, "$this->endpoint/$datasetId", 'put');
    $this->assertDatasetGet($newDataset);

    // Now try with mismatched identifiers.
    $datasetId = 'abc-123';
    $response = $this->httpClient->put("$this->endpoint/$datasetId", [
      RequestOptions::JSON => $newDataset,
      RequestOptions::AUTH => $this->auth,
      RequestOptions::HTTP_ERRORS => FALSE,
    ]);
    $this->assertEquals(409, $response->getStatusCode());
    $this->validator->validate($response, "$this->endpoint/$datasetId", 'put');
  }

  private function assertDatasetGet($dataset) {
    $id = $dataset->identifier;
    $response = $this->httpClient->get("$this->endpoint/$id");
    $responseBody = json_decode($response->getBody());
    $this->assertEquals(200, $response->getStatusCode());
    $this->validator->validate($response, "$this->endpoint/$id", 'get');
    $this->assertEquals($dataset, $responseBody);
  }

}