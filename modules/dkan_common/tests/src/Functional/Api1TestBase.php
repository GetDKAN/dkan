<?php

namespace Drupal\Tests\common\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;
use Osteel\OpenApi\Testing\ValidatorBuilder;

/**
 * Base class for API v1 tests.
 *
 * Provides common functionality for API 1 tests, including HTTP client setup,
 * OpenAPI spec validation, and user creation.
 */
abstract class Api1TestBase extends BrowserTestBase {
  use UserCreationTrait;

  /**
   * HTTP Client.
   */
  protected Client $httpClient;

  /**
   * Decoded OpenAPI spec.
   */
  protected object $spec;

  /**
   * Login credentials for the API, in format ['username', 'password'].
   */
  protected array $auth;

  /**
   * Another set of credentials, for a user without proper perms.
   */
  protected array $authNoPerms;

  /**
   * Base URL for the API.
   *
   */
  protected string $endpoint;

  /**
   * OpenApi Validator.
   *
   * @var \Osteel\OpenApi\Testing\ValidatorInterface
   */
  protected $validator;

  protected $defaultTheme = 'stark';

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
    'node',
    'sample_content',
    'workflows',
  ];

  /**
   * Set up test client, role and user, initialize spec.
   */
  public function setUp(): void {
    parent::setUp();
    $user = $this->createUser([
      'access content',
      'create data content',
      'edit own data content',
      'delete own data content',
      'use dkan_publishing transition publish',
      'use dkan_publishing transition archive',
      'use dkan_publishing transition hidden',
      'use dkan_publishing transition restore',
      'view data revisions',
      'view any unpublished content',
    ], 'testapiuser', FALSE);
    $user2 = $this->createUser(['access content'], 'testnopermsuser', FALSE);

    $this->httpClient = $this->container->get('http_client_factory')
      ->fromOptions([
        'base_uri' => $this->baseUrl,
      ]);
    $this->auth = ['testapiuser', $user->pass_raw];
    $this->authNoPerms = ['testnopermsuser', $user2->pass_raw];

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
