<?php

namespace Drupal\Tests\common\Functional;

use Drupal\Tests\common\Traits\CleanUp;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
use weitzman\DrupalTestTraits\Entity\UserCreationTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

abstract class Api1TestBase extends ExistingSiteBase {
  use CleanUp;
  use UserCreationTrait;

  protected $http;
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
    $this->setDefaultModerationState($state = 'published');

    $user = $this->createUser([], "testapiuser", FALSE, ['roles' => ['api_user'], 'mail' => 'testapiuser@test.com']);

    $this->baseUrl = getenv('SIMPLETEST_BASE_URL');
    $this->http = new Client(['base_uri' => $this->baseUrl]);
    $this->auth = ['testapiuser', $user->pass_raw];
    $this->endpoint = $this->getEndpoint();

    // Load the API spec for use by tests.
    $response = $this->http->request('GET', 'api/1');
    $this->spec = json_decode($response->getBody());
  }

  public function tearDown(): void {
    parent::tearDown();
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

  protected function getSampleDataset(int $n = 0) {
    $sampleJson = file_get_contents(dirname(__DIR__, 4). '/sample_content/sample_content.json');
    $sampleDatasets = json_decode($sampleJson);
    return $sampleDatasets->dataset[$n];
  }

  protected function setDefaultModerationState($state = 'published') {
    /** @var \Drupal\Core\Config\ConfigFactory $config */
    $config = \Drupal::service('config.factory');
    $defaultModerationState = $config->getEditable('workflows.workflow.dkan_publishing');
    $defaultModerationState->set('type_settings.default_moderation_state', $state);
    $defaultModerationState->save();
  }
}
