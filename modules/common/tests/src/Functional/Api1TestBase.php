<?php

namespace Drupal\Tests\common\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\Role;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;

abstract class Api1TestBase extends BrowserTestBase {
  use UserCreationTrait;

  /**
   * HTTP Client.
   *
   * @var \GuzzleHttp\Client
   */
  protected Client $httpClient;

  protected $spec;
  protected $auth;
  protected $endpoint;

  protected $defaultTheme = 'stark';

  protected static $modules = [
    'common',
    'datastore',
    'dynamic_page_cache',
    'harvest',
    'metastore',
    'node',
    'user',
  ];

  /**
   * Set up test client, role and user, initialize spec.
   */
  public function setUp(): void {
    parent::setUp();

    $role = Role::create([
      'id' => 'api_user',
      'label' => "API User",
    ]);
    if ($role->save() === SAVED_NEW) {
      $permissions = ["post put delete datasets through the api"];
      $this->grantPermissions($role, $permissions);
    }

    $user = $this->createUser([], "testapiuser", FALSE, [
      'roles' => ['api_user'],
      'mail' => 'testapiuser@test.com',
    ]);

    $this->httpClient = $this->container->get('http_client_factory')
      ->fromOptions([
        'base_uri' => $this->baseUrl,
      ]);
    $this->auth = ['testapiuser', $user->pass_raw];
    $this->endpoint = $this->getEndpoint();

    // Load the API spec for use by tests.
    $response = $this->httpClient->request('GET', 'api/1');
    $this->spec = json_decode($response->getBody());
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
      RequestOptions::TIMEOUT => 10000,
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
