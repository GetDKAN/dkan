<?php

namespace Drupal\Tests\metastore\Functional\Api1;

use Drupal\Tests\common\Functional\Api1TestBase;
use GuzzleHttp\RequestOptions;

class DatasetItemTest extends Api1TestBase {

  public function getEndpoint():string {
    return 'api/1/metastore/schemas/dataset/items';
  }

  public function testList() {
    $this->post($this->getSampleDataset(0));
    $this->post($this->getSampleDataset(1));

    $responseSchema = $this->spec->paths->{'/api/1/metastore/schemas/{schema_id}/items'}
      ->get->responses->{"200"}->content->{"application/json"}->schema;
    $response = $this->http->request('GET', $this->endpoint);
    $responseBody = json_decode($response->getBody());
    $this->assertEquals(2, count($responseBody));
    $this->assertTrue(is_object($responseBody[1]));
    $this->assertJsonIsValid($responseSchema, $responseBody);
  }

  public function testGetItem() {
    $dataset = $this->getSampleDataset();
    $this->post($dataset);
    $this->assertDatasetGet($dataset);
    $datasetId = 'abc-123';
    $response = $this->http->get("$this->endpoint/$datasetId", [
      RequestOptions::HTTP_ERRORS => FALSE,
    ]);
    $this->assertEquals(404, $response->getStatusCode());

    $responseBody = json_decode($response->getBody());
    $responseSchema = $this->spec->components->responses->{"404IdNotFound"};
    $this->assertJsonIsValid($responseSchema, $responseBody);
  }

  public function testPost() {
    $dataset = $this->getSampleDataset();
    $response = $this->post($dataset);
    $this->assertEquals(201, $response->getStatusCode());

    $responseBody = json_decode($response->getBody());
    $responseSchema = $this->spec->components->responses->{"201MetadataCreated"}->content->{"application/json"}->schema;

    $this->assertJsonIsValid($responseSchema, $responseBody);
    $this->assertDatasetGet($dataset);

    // Now try a duplicate.
    $response = $this->post($dataset, FALSE);
    $this->assertEquals(409, $response->getStatusCode());
    // @todo Fuly validate response once documented.
  }

  public function testPatch() {
    $dataset = $this->getSampleDataset();
    $this->post($dataset);
    $datasetId = $dataset->identifier;

    $newTitle = (object) ['title' => 'Modified Title'];
    $response = $this->http->patch("$this->endpoint/$datasetId", [
      RequestOptions::JSON => $newTitle,
      RequestOptions::AUTH => $this->auth,
    ]);

    $this->assertEquals(200, $response->getStatusCode());

    $responseBody = json_decode($response->getBody());
    $responseSchema = $this->spec->components->responses->{"201MetadataCreated"}->content->{"application/json"}->schema;
    $this->assertJsonIsValid($responseSchema, $responseBody);

    $dataset->title = $newTitle->title;
    $this->assertDatasetGet($dataset);

    // Now, try with a non-existent identifier.
    $datasetId = "abc-123";
    $newTitle = (object) ['title' => 'Modified Title'];

    $response = $this->http->patch("$this->endpoint/$datasetId", [
      RequestOptions::HTTP_ERRORS => FALSE,
      RequestOptions::JSON => $newTitle,
      RequestOptions::AUTH => $this->auth,
    ]);

    $this->assertEquals(412, $response->getStatusCode());

    $responseBody = json_decode($response->getBody());
    $responseSchema = $this->spec->components->responses->{"412MetadataObjectNotFound"};
    $this->assertJsonIsValid($responseSchema, $responseBody);
  }

  public function testPut() {
    $dataset = $this->getSampleDataset();
    $this->post($dataset);

    $datasetId = $dataset->identifier;
    $newDataset = $this->getSampleDataset(1);
    $newDataset->identifier = $datasetId;

    $response = $this->http->put("$this->endpoint/$datasetId", [
      RequestOptions::JSON => $newDataset,
      RequestOptions::AUTH => $this->auth,
    ]);
    $this->assertEquals(200, $response->getStatusCode());
    $responseBody = json_decode($response->getBody());
    $responseSchema = $this->spec->components->responses->{"201MetadataCreated"}->content->{"application/json"}->schema;
    $this->assertJsonIsValid($responseSchema, $responseBody);
    $this->assertDatasetGet($newDataset);

    // Now try with mismatched identifiers.
    $datasetId = 'abc-123';
    $response = $this->http->put("$this->endpoint/$datasetId", [
      RequestOptions::JSON => $newDataset,
      RequestOptions::AUTH => $this->auth,
      RequestOptions::HTTP_ERRORS => FALSE,
    ]);
    $this->assertEquals(409, $response->getStatusCode());
  }

  private function assertDatasetGet($dataset) {
    $id = $dataset->identifier;
    $responseSchema = $this->spec->components->schemas->dataset;
    $response = $this->http->get("$this->endpoint/$id");
    $responseBody = json_decode($response->getBody());
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertJsonIsValid($responseSchema, $responseBody);
    $this->assertEquals($dataset, $responseBody);
  }

}