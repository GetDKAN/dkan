<?php

namespace Drupal\Tests\metastore\Functional\Api1;

use Drupal\metastore\DataDictionary\DataDictionaryDiscovery;
use Drupal\Tests\common\Functional\Api1TestBase;
use GuzzleHttp\RequestOptions;

class DistributionHandlingTest extends Api1TestBase {

  public function getEndpoint():string {
    return 'api/1/metastore/schemas/dataset/items';
  }

  /**
   * Post a data dictionary and reference from describedBy.
   */
  public function testDescribedByDataDictionary() {
    // Set data dictionary discovery mode to reference.
    $config = $this->config('metastore.settings');
    $config->set('data_dictionary_mode', DataDictionaryDiscovery::MODE_REFERENCE);
    $config->save();

    // Create a data dictionary.
    $dictionaryId = $this->postDataDictionary();

    // Post dataset with absolute URL in distribution's describedBy field.
    $datasetMetadata = $this->getSampleDataset(0);
    $uri = "dkan://metastore/schemas/data-dictionary/items/$dictionaryId";
    $url = \Drupal::service('dkan.metastore.url_generator')->absoluteString($uri);
    $this->assertEquals("{$this->baseUrl}/api/1/metastore/schemas/data-dictionary/items/$dictionaryId", $url);
    $datasetMetadata->distribution[0]->describedBy = $url;
    $datasetMetadata->distribution[0]->describedByType = 'application/vnd.tableschema+json';
    $response = $this->post($datasetMetadata, FALSE);
    $responseBody = json_decode((string) $response->getBody());
    $datasetId = $responseBody->identifier;
    $dataset_endpoint = $this->getEndpoint();
    $response = $this->httpClient->get("$dataset_endpoint/$datasetId", [RequestOptions::TIMEOUT => 10000]);
    $responseBody = json_decode($response->getBody());

    // Ensure that when we retrieve the dataset, the full URL is still shown.
    $this->assertEquals($url, $responseBody->distribution[0]->describedBy);

    // Now patch to change to dkan:// URI (domain-agnostic) in the
    // describedBy field.
    $patch = (object) ["distribution" => [clone $responseBody->distribution[0]]];
    $patch->distribution[0]->describedBy = $uri;
    $response = $this->httpClient->patch("$dataset_endpoint/$datasetId", [
      RequestOptions::JSON => $patch,
      RequestOptions::AUTH => $this->auth,
    ]);
    $this->assertEquals(200, $response->getStatusCode());
    $response = $this->httpClient->get("$dataset_endpoint/$datasetId");
    // When we GET the dataset, describedBy should still be expressed as
    // absolute URL.
    $responseBody = json_decode($response->getBody());
    $this->assertEquals($url, $responseBody->distribution[0]->describedBy);

    // Patch again with a bad ID in the URL.
    $patch->distribution[0]->describedBy = "dkan://metastore/schemas/data-dictionary/items/foobar";
    $response = $this->httpClient->patch("$dataset_endpoint/$datasetId", [
      RequestOptions::JSON => $patch,
      RequestOptions::AUTH => $this->auth,
      RequestOptions::HTTP_ERRORS => FALSE,
    ]);
    // This should cause a 400 bad request response.
    $this->assertEquals(400, $response->getStatusCode());
    $this->assertStringContainsString("is not a valid data-dictionary URI", $response->getBody());

    // Patch again with a completely foriegn URL.
    $pdf_url = "https://www.example.com/dictionary.pdf";
    $patch->distribution[0]->describedBy = $pdf_url;
    $response = $this->httpClient->patch("$dataset_endpoint/$datasetId", [
      RequestOptions::JSON => $patch,
      RequestOptions::AUTH => $this->auth,
    ]);
    // This should just be saved as-is.
    $this->assertEquals(200, $response->getStatusCode());
    $response = $this->httpClient->get("$dataset_endpoint/$datasetId");
    $responseBody = json_decode($response->getBody());
    $this->assertEquals($pdf_url, $responseBody->distribution[0]->describedBy);
  }

  /**
   * Create a data dictionary in the metastore.
   */
  private function postDataDictionary() {
    $dictionary = json_decode(file_get_contents(dirname(__DIR__, 2) . '/files/DataDictionary.json'));

    $response = $this->httpClient->post('api/1/metastore/schemas/data-dictionary/items', [
      RequestOptions::JSON => $dictionary,
      RequestOptions::AUTH => $this->auth,
    ]);

    $this->assertEquals(201, $response->getStatusCode());

    $responseBody = json_decode($response->getBody());

    // Unless JSON changes, we should always get same id back.
    $this->assertEquals("47f1d697-f469-5b41-a613-80cdfac7a326", $responseBody->identifier);

    return $responseBody->identifier;
  }

}
