<?php

namespace Drupal\Tests\dkan_datastore\Unit\Controller;

use Drupal\dkan_datastore\Controller\AbstractQueryController;
use Drupal\dkan_metastore\Reference\ReferenceLookup;
use Drupal\dkan_datastore\Service\DatastoreQuery;
use PHPUnit\Framework\TestCase;
use RootedData\RootedJsonData;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Drupal\dkan_datastore\Controller\AbstractQueryController
 * @coversDefaultClass \Drupal\dkan_datastore\Controller\AbstractQueryController
 * @group dkan
 * @group datastore
 * @group unit
 */
class AbstractQueryControllerTest extends TestCase {

  /**
   * Make sure we get what we expect with a GET.
   */
  public function testGetNormalizer() {
    $queryString = "conditions[0][property]=state&conditions[0][value]=AL&conditions[0][operator]==&conditions[1][property]=record_number&conditions[1][value]=%1%&conditions[1][operator]=LIKE&sort[0][property]=record_number&sort[0][order]=asc&sort[1][property]=state&sort[1][order]=desc&limit=50&offset=25&results=true";

    $request = Request::create("http://example.com?$queryString", "GET");
    $requestJson = AbstractQueryController::getPayloadJson($request);
    $this->assertEquals($requestJson, $this->getSampleJson());
  }

  /**
   * Make sure we get what we expect with a POST.
   */
  public function testPostNormalizer() {
    $sampleJson = $this->getSampleJson();
    $schema = $this->getSampleSchema();
    $request = Request::create("http://example.com", "POST", [], [], [], [], $sampleJson);
    $requestJson = AbstractQueryController::getPayloadJson($request, $schema);
    $this->assertEquals($requestJson, $sampleJson);
  }

  /**
   * Make sure we get what we expect with a PATCH.
   */
  public function testPatchNormalizer() {
    $sampleJson = $this->getSampleJson();
    $schema = $this->getSampleSchema();

    $request = Request::create("http://example.com", "PATCH", [], [], [], [], $sampleJson);
    $requestJson = AbstractQueryController::getPayloadJson($request, $schema);
    $this->assertEquals($requestJson, $sampleJson);
  }

  /**
   * Make sure we get what we expect with a DELETE.
   */
  public function testDeleteNormalizer() {
    $this->expectExceptionMessage("Only POST, PUT, PATCH and GET requests can be normalized");
    $schema = $this->getSampleSchema();

    $request = Request::create("http://example.com", "DELETE");
    AbstractQueryController::getPayloadJson($request, $schema);
  }

  /**
   * Make sure we get what we expect with a PUT.
   */
  public function testPutNormalizer() {
    $sampleJson = $this->getSampleJson();
    $schema = $this->getSampleSchema();

    $request = Request::create("http://example.com", "PUT", [], [], [], [], $sampleJson);
    $requestJson = AbstractQueryController::getPayloadJson($request, $schema);
    $this->assertEquals($requestJson, $sampleJson);
  }

  /**
   * Make sure we get what we expect with invalid JSON.
   */
  public function testInvalidJson() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid JSON');
    $sampleJson = $this->getBadJson();
    $schema = $this->getSampleSchema();
    $request = Request::create("http://example.com", "POST", [], [], [], [], $sampleJson);
    AbstractQueryController::getPayloadJson($request, $schema);
  }

  /**
   * Test the ::resolveResourceDependencies method using reflection.
   */
  public function testResolveResourceDependencies() {
    // Create mock for referenceLookup service.
    $mockReferenceLookup = $this->createMock(ReferenceLookup::class);
    $mockReferenceLookup->method('getReferencers')
      ->willReturnCallback(function ($schemaId, $identifier): array {
        return match ([$schemaId, $identifier]) {
          ['distribution', 'thereAreDistributions_1_source'] => ['distribution1', 'distribution2'],
          ['distribution', 'noDistributions_1_source'] => [],
          ['dataset', 'noDistributions_1_source'] => ['dataset1', 'dataset2'],
          default => [],
        };
      });

    // Create anonymous class extending AbstractQueryController for testing.
    $controller = new class($mockReferenceLookup) extends AbstractQueryController {

      public function __construct(protected ReferenceLookup $referenceLookup) {
        // No-op constructor for testing.
      }

      /**
       * Format response implementation.
       */
      public function formatResponse(
        DatastoreQuery $datastoreQuery,
        RootedJsonData $result,
        array $dependencies = [],
        ?ParameterBag $params = NULL,
      ) {
        // No-op for testing.
      }

    };

    $ref_method = new \ReflectionMethod($controller, 'resolveResourceDependencies');
    $ref_method->setAccessible(TRUE);

    $resource_id = 'thereAreDistributions_1_source';
    $result = $ref_method->invokeArgs($controller, [$resource_id]);
    $expected = ['distribution' => ['distribution1', 'distribution2']];
    $this->assertEquals($expected, $result);

    $resource_id = 'noDistributions_1_source';
    $result = $ref_method->invokeArgs($controller, [$resource_id]);
    $expected = ['dataset' => ['dataset1', 'dataset2']];
    $this->assertEquals($expected, $result);

    $valid_distribution_uuid = '123e4567-e89b-12d3-a456-426614174000';
    $result = $ref_method->invokeArgs($controller, [$valid_distribution_uuid]);
    $expected = ['distribution' => [$valid_distribution_uuid]];
    $this->assertEquals($expected, $result);
  }

  private function getSampleJson() {
    return file_get_contents(__DIR__ . "/../../../data/query.json");
  }

  private function getBadJson() {
    return file_get_contents(__DIR__ . "/../../../data/query/invalidJson.json");
  }

  private function getSampleSchema() {
    return file_get_contents(__DIR__ . "/../../../data/querySchema.json");
  }

}
