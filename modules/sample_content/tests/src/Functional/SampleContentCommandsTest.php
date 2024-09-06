<?php

namespace Drupal\Tests\sample_content\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\User;
use Drush\TestTraits\DrushTestTrait;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

/**
 * @coversDefaultClass \Drupal\sample_content\Drush
 *
 * @group dkan
 * @group sample_content
 * @group functional
 */
class SampleContentCommandsTest extends BrowserTestBase {

  use DrushTestTrait;

  protected $defaultTheme = 'stark';

  protected $strictConfigSchema = FALSE;

  protected static $modules = [
    'node',
    'sample_content',
  ];

  /**
   * Get a Guzzle Client object ready for sending HTTP requests.
   *
   * @param \Drupal\user\Entity\User|null $authUser
   *   (Optional) A user object to use for authentication.
   * @param bool $http_errors
   *   (Optional) Whether 4xx or 5xx response codes should throw an exception.
   *   Defaults to FALSE.
   *
   * @return \GuzzleHttp\Client
   *   Client ready for HTTP requests.
   *
   * @todo Move this to a trait or base class.
   */
  protected function getApiClient(?User $authUser = NULL, $http_errors = FALSE): Client {
    $options = [
      'base_uri' => $this->baseUrl,
      RequestOptions::HTTP_ERRORS => $http_errors,
    ];
    if ($authUser) {
      $options[RequestOptions::AUTH] = [$authUser->getAccountName(), $authUser->pass_raw];
    }
    return $this->container->get('http_client_factory')->fromOptions($options);
  }

  public function test() {
    // No errors thrown on --help.
    foreach ([
      'dkan:sample-content:create',
      'dkan:sample-content:remove',
    ] as $command) {
      $this->drush($command . ' --help');
      $this->assertEmpty(
        $this->getSimplifiedErrorOutput()
      );
    }

    $harvest_plan_name = 'sample_content';
    /** @var \Drupal\harvest\HarvestService $harvest_service */
    $harvest_service = $this->container->get('dkan.harvest.service');

    // Run the create command.
    $this->drush('dkan:sample-content:create');
    $output = $this->getOutput();

    // Start asserting. Admittedly, not the best set of assertions.
    foreach ([
      'run_id',
      'processed',
      'created',
      'updated',
      'errors',
      $harvest_service->getLastHarvestRunId($harvest_plan_name),
      // The number of datasets we expect to create.
      '10',
    ] as $expected) {
      $this->assertStringContainsString($expected, $output);
    }

    // Ask the API.
    $this->assertCount(1, $harvest_service->getAllHarvestIds());
    $this->assertNotNull($harvest_service->getHarvestPlanObject($harvest_plan_name));
    $this->assertNotEmpty($run_id = $harvest_service->getLastHarvestRunId($harvest_plan_name));
    $this->assertNotEmpty(
      $run_info = json_decode($harvest_service->getHarvestRunInfo($harvest_plan_name, $run_id), TRUE)
    );
    $this->assertCount(10, $run_info['status']['extracted_items_ids'] ?? []);
    $this->assertEmpty($run_info['error'] ?? []);

    // What does the RESTful API say?
    $response = $this->getApiClient()->get('/api/1/metastore/schemas/dataset/items');
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertCount(10, json_decode($response->getBody()->getContents()));

    // Do the import.
    $this->drush('queue:run localize_import');
    $this->assertStringContainsString(
      'Processed 10 items from the localize_import queue',
      $this->getSimplifiedErrorOutput()
    );
    $this->drush('queue:run datastore_import');
    $this->assertStringContainsString(
      'Processed 10 items from the datastore_import queue',
      $this->getSimplifiedErrorOutput()
    );

    // Run the remove command.
    $this->drush('dkan:sample-content:remove');
    // Logged output counts as an error, even if it's not an error.
    $output = $this->getErrorOutput();
    // Assert the output.
    foreach ([
      'Reverting harvest plan: ' . $harvest_plan_name,
      'Deregistering harvest plan: ' . $harvest_plan_name,
    ] as $expected) {
      $this->assertStringContainsString($expected, $output);
    }

    $response = $this->getApiClient()->get('/api/1/metastore/schemas/dataset/items');
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertCount(0, json_decode($response->getBody()->getContents()));
  }

}
