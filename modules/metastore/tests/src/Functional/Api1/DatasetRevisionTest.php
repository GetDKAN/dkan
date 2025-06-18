<?php

namespace Drupal\Tests\metastore\Functional\Api1;

use Drupal\Tests\common\Functional\Api1TestBase;
use GuzzleHttp\RequestOptions;

/**
 * Tests the revision API.
 *
 * @group functional2
 *
 * @coversDefaultClass \Drupal\metastore\Plugin\rest\resource\DatasetItemResource
 */
class DatasetRevisionTest extends Api1TestBase {

  public function getEndpoint():string {
    $data = $this->getSampleDataset(0);
    return "/api/1/metastore/schemas/dataset/items/{$data->identifier}/revisions";
  }

  public function testList() {
    $data = $this->getSampleDataset(0);
    $response = $this->httpClient->post('/api/1/metastore/schemas/dataset/items', [
      RequestOptions::JSON => $data,
      RequestOptions::AUTH => $this->auth,
    ]);

    // Test individual item endpoint.
    $this->validator->validate($response, "api/1/metastore/schemas/dataset/items", 'post');

    $response = $this->httpClient->get($this->endpoint, [
      RequestOptions::AUTH => $this->auth,
    ]);
    $responseBody = json_decode($response->getBody());
    $listRevision = $responseBody[0];

    // Confirm we get the same object from the item get as the list.
    $response = $this->httpClient->get($this->endpoint . "/$listRevision->identifier", [
      RequestOptions::AUTH => $this->auth,
    ]);
    $this->assertEquals(200, $response->getStatusCode());
    $responseBody = json_decode($response->getBody());
    $this->assertEquals($listRevision, $responseBody);

    // Confirm error if we have a non-existant dataset ID.
    $badDatasetUrl = "/api/1/metastore/schemas/dataset/items/abc-123/revisions/$listRevision->identifier";
    $response = $this->httpClient->get($badDatasetUrl, [
      RequestOptions::HTTP_ERRORS => FALSE,
      RequestOptions::AUTH => $this->auth,
    ]);
    $this->assertEquals(404, $response->getStatusCode());
    $responseBody = json_decode($response->getBody());
    $this->assertStringContainsString("No dataset found", $responseBody->message);

    // Modify item.
    $this->httpClient->patch("/api/1/metastore/schemas/dataset/items/{$data->identifier}", [
      RequestOptions::JSON => ['title' => "Changing title"],
      RequestOptions::AUTH => $this->auth,
    ]);

    $response = $this->httpClient->get($this->endpoint, [
      RequestOptions::AUTH => $this->auth,
    ]);
    $responseBody = json_decode($response->getBody());
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals(2, count($responseBody));
    $this->assertTrue($responseBody[0]->identifier > $responseBody[1]->identifier);
    $this->assertTrue($responseBody[0]->published);

    // Test a bad dataset ID.
    $badDatasetUrl = "/api/1/metastore/schemas/dataset/items/abc-123/revisions";
    $response = $this->httpClient->get($badDatasetUrl, [
      RequestOptions::HTTP_ERRORS => FALSE,
      RequestOptions::AUTH => $this->auth,
    ]);
    $this->assertEquals(404, $response->getStatusCode());
    $responseBody = json_decode($response->getBody());
    $this->assertStringContainsString("No dataset found", $responseBody->message);

    // Confirm error if we have real but mismatched revision and dataset IDs.
    $secondData = $this->getSampleDataset(1);
    $this->httpClient->post('/api/1/metastore/schemas/dataset/items', [
      RequestOptions::JSON => $secondData,
      RequestOptions::AUTH => $this->auth,
    ]);
    $badDatasetUrl = "/api/1/metastore/schemas/dataset/items/$secondData->identifier/revisions/$listRevision->identifier";
    $response = $this->httpClient->get($badDatasetUrl, [
      RequestOptions::HTTP_ERRORS => FALSE,
      RequestOptions::AUTH => $this->auth,
    ]);
    $this->assertEquals(404, $response->getStatusCode());
    $responseBody = json_decode($response->getBody());
    $this->assertStringContainsString("has no revision", $responseBody->message);

    // Confirm we get an error if we have a non-existant revision ID.
    $response = $this->httpClient->get($this->endpoint . "/123456789", [
      RequestOptions::HTTP_ERRORS => FALSE,
      RequestOptions::AUTH => $this->auth,
    ]);
    $this->assertEquals(404, $response->getStatusCode());
    $responseBody = json_decode($response->getBody());
    $this->assertStringContainsString("has no revision", $responseBody->message);
  }

  public function testPost() {
    $this->setDefaultModerationState('draft');
    $data = $this->getSampleDataset(0);
    $this->httpClient->post('/api/1/metastore/schemas/dataset/items', [
      RequestOptions::JSON => $data,
      RequestOptions::AUTH => $this->auth,
    ]);

    // Array with states as keys and whether publicly visible as values.
    $states = [
      'draft' => FALSE,
      'published' => TRUE,
      'orphaned' => FALSE,
      'archived' => FALSE,
      'hidden' => TRUE,
    ];

    $count = 1;
    foreach ($states as $state => $public) {
      // Create a new revision with the workflow state.
      $response = $this->newRevision($state);
      $this->assertEquals(201, $response->getStatusCode());
      $count++;

      // Validate response object.
      $responseBody = json_decode((string) $response->getBody());
      $this->validator->validate($response, $this->endpoint, 'post');

      // Validate URL and contents of response object.
      $response = $this->httpClient->get($responseBody->endpoint, [
        RequestOptions::AUTH => $this->auth,
      ]);
      $responseBody = json_decode($response->getBody());
      // Message and state match the values submitted.
      $this->assertStringContainsString($state, $responseBody->message);
      $this->assertEquals($state, $responseBody->state);

      // Confirm revisions list has increased by one item.
      $response = $this->httpClient->get($this->endpoint, [
        RequestOptions::AUTH => $this->auth,
      ]);
      $responseBody = json_decode($response->getBody());
      $this->assertEquals($count, count($responseBody));

      // Confirm dataset visibility matches expected.
      $expectedCode = $public ? 200 : 404;
      $datasetUrl = "/api/1/metastore/schemas/dataset/items/{$data->identifier}";
      $response = $this->httpClient->get($datasetUrl, [
        RequestOptions::HTTP_ERRORS => FALSE,
      ]);
      $this->assertEquals($expectedCode, $response->getStatusCode());
    }

    // Test a bad workflow state.
    $response = $this->newRevision('foo');
    $this->assertEquals(400, $response->getStatusCode());
    $responseBody = json_decode((string) $response->getBody());
    $this->assertStringContainsString('does not exist in workflow', $responseBody->message);
    $response = $this->httpClient->get($this->endpoint, [
      RequestOptions::AUTH => $this->auth,
    ]);
    $responseBody = json_decode($response->getBody());
    $this->assertEquals($count, count($responseBody));

    // Test a bad dataset ID.
    $newRevision = (object) [
      'message' => "New published revision.",
      'state' => 'published',
    ];
    $response = $this->httpClient->post('/api/1/metastore/schemas/dataset/items/abc-123/revisions', [
      RequestOptions::JSON => $newRevision,
      RequestOptions::AUTH => $this->auth,
      RequestOptions::HTTP_ERRORS => FALSE,
    ]);
    $this->assertEquals(400, $response->getStatusCode());
    $responseBody = json_decode($response->getBody());
    $this->assertStringContainsString('No dataset found', $responseBody->message);
    $response = $this->httpClient->get($this->endpoint, [
      RequestOptions::AUTH => $this->auth,
    ]);
    $responseBody = json_decode($response->getBody());
    $this->assertEquals($count, count($responseBody));

}

  private function newRevision($state) {
    $newRevision = (object) [
      'message' => "New $state revision.",
      'state' => $state,
    ];
    return $this->httpClient->post($this->endpoint, [
      RequestOptions::JSON => $newRevision,
      RequestOptions::AUTH => $this->auth,
      RequestOptions::HTTP_ERRORS => FALSE,
    ]);
  }

}
