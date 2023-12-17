<?php

namespace Drupal\Tests\metastore\Functional\Api1;

use Drupal\Tests\common\Functional\Api1TestBase;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * @group dkan
 * @group metastore
 * @group functional
 * @group api
 */
class DatasetItemTest extends Api1TestBase {

  public function getEndpoint():string {
    return 'api/1/metastore/schemas/dataset/items';
  }

  public function testGet() {
    $dataset = $this->getSampleDataset();

    $response = $this->post($dataset, FALSE);
    $this->assertDatasetGet($dataset);

    $this->post($this->getSampleDataset(1));

    $responseSchema = $this->spec->paths
      ->{'/api/1/metastore/schemas/{schema_id}/items'}
      ->get->responses->{"200"}->content->{"application/json"}->schema;
    $response = $this->httpClient->request('GET', $this->endpoint);
    // We should have missed the cache because we just posted the dataset.
    $this->assertXDrupalDynamicCache($response, 'MISS');
    $responseBody = json_decode($response->getBody());
    $this->assertEquals(2, count($responseBody));
    $this->assertTrue(is_object($responseBody[1]));
    $this->assertJsonIsValid($responseSchema, $responseBody);

    $datasetId = 'abc-123';
    $response = $this->httpClient->get("$this->endpoint/$datasetId", [
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
    $responseSchema = $this->spec->components->responses
      ->{"201MetadataCreated"}->content->{"application/json"}->schema;

    $this->assertJsonIsValid($responseSchema, $responseBody);
    // The dataset get should miss the cache because we just posted the dataset.
    $this->assertDatasetGet($dataset, 'MISS');

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
    $response = $this->httpClient->patch("$this->endpoint/$datasetId", [
      RequestOptions::JSON => $newTitle,
      RequestOptions::AUTH => $this->auth,
    ]);

    $this->assertEquals(200, $response->getStatusCode());

    $responseBody = json_decode($response->getBody());
    $responseSchema = $this->spec->components->responses
      ->{"201MetadataCreated"}->content->{"application/json"}->schema;
    $this->assertJsonIsValid($responseSchema, $responseBody);

    $dataset->title = $newTitle->title;
    // The dataset get should miss the cache because we just patched the dataset.
    $this->assertDatasetGet($dataset, 'MISS');

    // Now, try with a non-existent identifier.
    $datasetId = "abc-123";
    $newTitle = (object) ['title' => 'Modified Title'];

    $response = $this->httpClient->patch("$this->endpoint/$datasetId", [
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

    $response = $this->httpClient->put("$this->endpoint/$datasetId", [
      RequestOptions::JSON => $newDataset,
      RequestOptions::AUTH => $this->auth,
    ]);
    $this->assertEquals(200, $response->getStatusCode());
    $responseBody = json_decode($response->getBody());
    $responseSchema = $this->spec->components->responses
      ->{"201MetadataCreated"}->content->{"application/json"}->schema;
    $this->assertJsonIsValid($responseSchema, $responseBody);
    $this->assertDatasetGet($newDataset, 'MISS');

    // Now try with mismatched identifiers.
    $datasetId = 'abc-123';
    $response = $this->httpClient->put("$this->endpoint/$datasetId", [
      RequestOptions::JSON => $newDataset,
      RequestOptions::AUTH => $this->auth,
      RequestOptions::HTTP_ERRORS => FALSE,
    ]);
    $this->assertEquals(409, $response->getStatusCode());
  }

  private function assertDatasetGet($dataset, $drupal_cache_hit = NULL): ResponseInterface {
    $id = $dataset->identifier;
    $responseSchema = $this->spec->components->schemas->dataset;
    $response = $this->httpClient->get("$this->endpoint/$id");
    $this->assertEquals(200, $response->getStatusCode());
    if ($drupal_cache_hit !== NULL) {
      $this->assertXDrupalDynamicCache($response, $drupal_cache_hit);
    }
    $responseBody = json_decode($response->getBody());
    $this->assertJsonIsValid($responseSchema, $responseBody);
    $this->assertEquals($dataset, $responseBody);
    return $response;
  }

}
