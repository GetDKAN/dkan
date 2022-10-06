<?php

namespace Drupal\Tests\metastore\Functional\Api1;

use Drupal\Tests\common\Functional\Api1TestBase;

class SchemaTest extends Api1TestBase {

  public function getEndpoint(): string {
    return 'api/1/metastore/schemas';
  }

  public function testList() {
    $response = $this->http->request('GET', $this->endpoint);
    $responseBody = json_decode($response->getBody());
    $this->assertEquals("http://dkan/api/v1/schema/dataset", $responseBody->dataset->id);
  }

  public function testGetItem() {
    $response = $this->http->request('GET', "$this->endpoint/dataset");
    $responseBody = json_decode($response->getBody());
    $this->assertEquals("http://dkan/api/v1/schema/dataset", $responseBody->id);
  }

}
