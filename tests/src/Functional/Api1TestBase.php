<?php

namespace Drupal\Tests\common\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
use Osteel\OpenApi\Testing\ValidatorBuilder;

abstract class Api1TestBase extends BrowserTestBase {
  use UserCreationTrait;

  /**
   * HTTP Client.
   */
  protected Client $httpClient;

  protected $spec;
  protected $auth;
  protected $endpoint;

  /**
   * OpenApi Validator.
   *
   * @var \Osteel\OpenApi\Testing\ValidatorInterface
   */
  protected $validator;

  protected $defaultTheme = 'stark';
  protected $strictConfigSchema = FALSE;

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
    'node',
    'sample_content',
  ];

  /**
   * Set up test client, role and user, initialize spec.
   */
  public function setUp(): void {
    parent::setUp();
    $user = $this->createUser(['post put delete datasets through the api'], 'testapiuser', FALSE);
    $this->httpClient = $this->container->get('http_client_factory')
      ->fromOptions([
        'base_uri' => $this->baseUrl,
      ]);
    $this->auth = ['testapiuser', $user->pass_raw];
    $this->endpoint = $this->getEndpoint();

    // Load the API spec for use by tests.
    $response = $this->httpClient->request('GET', 'api/1');
    $this->validator = ValidatorBuilder::fromJsonString($response->getBody())->getValidator();
    $this->spec = json_decode((string) $response->getBody());
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
    return $this->httpClient->post($this->endpoint, [
      RequestOptions::JSON => $data,
      RequestOptions::AUTH => $this->auth,
      RequestOptions::HTTP_ERRORS => $httpErrors,
    ]);
  }

  protected function getSampleDataset(int $n = 0) {
    /** @var \Drupal\sample_content\SampleContentService $sample_content_service */
    $sample_content_service = $this->container->get('dkan.sample_content.service');
    $sampleJson = $sample_content_service->createDatasetJsonFileFromTemplate();
    $sampleDatasets = json_decode(file_get_contents($sampleJson));
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
