<?php

namespace Drupal\Tests\common\Unit\Util;

use Drupal\datastore\Controller\AbstractQueryController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class AbstractQueryControllerTest extends TestCase {

  /**
   * Make sure we get what we expect with a GET
   */
  public function testGetNormalizer() {
    $queryString = "conditions[0][property]=state&conditions[0][value]=AL&conditions[0][operator]==&conditions[1][property]=record_number&conditions[1][value]=%1%&conditions[1][operator]=LIKE&sort[0][property]=record_number&sort[0][order]=asc&sort[1][property]=state&sort[1][order]=desc&limit=50&offset=25&results=true";

    $request = Request::create("http://example.com?$queryString", "GET");
    $requestJson = AbstractQueryController::getPayloadJson($request);
    $this->assertEquals($requestJson, $this->getSampleJson());
  }

  /**
   * Make sure we get what we expect with a post
   */
  public function testPostNormalizer() {
    $sampleJson = $this->getSampleJson();
    $schema = $this->getSampleSchema();
    $request = Request::create("http://example.com", "POST", [], [], [], [], $sampleJson);
    $requestJson = AbstractQueryController::getPayloadJson($request, $schema);
    $this->assertEquals($requestJson, $sampleJson);
  }

  /**
   * Make sure we get what we expect with a patch
   */
  public function testPatchNormalizer() {
    $sampleJson = $this->getSampleJson();
    $schema = $this->getSampleSchema();

    $request = Request::create("http://example.com", "PATCH", [], [], [], [], $sampleJson);
    $requestJson = AbstractQueryController::getPayloadJson($request, $schema);
    $this->assertEquals($requestJson, $sampleJson);
  }

  /**
   * Make sure we get what we expect with a delete
   */
  public function testDeleteNormalizer() {
    $this->expectExceptionMessage("Only POST, PUT, PATCH and GET requests can be normalized");
    $schema = $this->getSampleSchema();

    $request = Request::create("http://example.com", "DELETE");
    AbstractQueryController::getPayloadJson($request, $schema);
  }

  /**
   * Make sure we get what we expect with a put
   */
  public function testPutNormalizer() {
    $sampleJson = $this->getSampleJson();
    $schema = $this->getSampleSchema();

    $request = Request::create("http://example.com", "PUT", [], [], [], [], $sampleJson);
    $requestJson = AbstractQueryController::getPayloadJson($request, $schema);
    $this->assertEquals($requestJson, $sampleJson);
  }

  private function getSampleJson() {
    return file_get_contents(__DIR__ . "/../../../data/query.json");
  }

  private function getSampleSchema() {
    return file_get_contents(__DIR__ . "/../../../data/querySchema.json");
  }

}
