<?php

namespace Drupal\Tests\common\Functional;

use Drupal\Tests\common\Traits\CleanUp;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
use weitzman\DrupalTestTraits\ExistingSiteBase;

abstract class Api1TestBase extends ExistingSiteBase {
  use CleanUp;

  protected $http;
  protected $baseUrl;
  protected $spec;
  protected $auth;
  protected $endpoint;

  public function setUp(): void {
    parent::setUp();
    $this->removeHarvests();
    $this->removeAllNodes();
    $this->removeAllMappedFiles();
    $this->removeAllFileFetchingJobs();
    $this->flushQueues();
    $this->removeFiles();
    $this->removeDatastoreTables();
    $this->baseUrl = getenv('SIMPLETEST_BASE_URL');
    $this->http = new Client(['base_uri' => $this->baseUrl]);
    $this->auth = ['testuser', '2jqzOAnXS9mmcLasy'];
    $this->endpoint = $this->getEndpoint();

    // Load the API spec for use by tests.
    $response = $this->http->request('GET', 'api/1');
    $this->spec = json_decode($response->getBody());
  }

  public function tearDown(): void {
    parent::setUp();
    $this->http = NULL;
  }

  protected function assertJsonIsValid($schema, $json) {
    $opiSchema = is_string($schema) ? Schema::fromJsonString($schema) : new Schema($schema);
    $validator = new Validator();
    $data = is_string($json) ? json_decode($json) : $json;
    $result = $validator->schemaValidation($data, $opiSchema);
    $this->assertTrue($result->isValid());
  }

  abstract public function getEndpoint(): string;

  protected function post($data, $httpErrors = TRUE) {
    return $this->http->post($this->endpoint, [
      RequestOptions::JSON => $data,
      RequestOptions::AUTH => $this->auth,
      RequestOptions::HTTP_ERRORS => $httpErrors,
    ]);
  }
}
