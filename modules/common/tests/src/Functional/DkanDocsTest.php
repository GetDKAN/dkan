<?php

namespace Drupal\Tests\common\Functional;

use Drupal\common\Controller\OpenApiController;
use Drupal\Core\Serialization\Yaml;
use weitzman\DrupalTestTraits\ExistingSiteBase;

class DkanDocsTest extends ExistingSiteBase {

  public function testGetVersions() {
    $controller = $this->getController();
    $response = $controller->getVersions();
    $this->assertEquals(200, $response->getStatusCode());
    $data = json_decode($response->getContent());
    $this->assertEquals(1, $data->version);
  }

  public function testGetCompleteJson() {
    $controller = $this->getController();
    $response = $controller->getComplete();
    $this->assertEquals(200, $response->getStatusCode());
    $data = json_decode($response->getContent(), TRUE);

    // Basic auth is included.
    $this->assertTrue(isset($data["components"]["securitySchemes"]["basic_auth"]));

    // Some sample paths from different modules are there
    $this->assertArrayHasKey('/api/1/datastore/imports', $data["paths"]);
    $this->assertArrayHasKey('/api/1/harvest/plans/{plan_id}', $data["paths"]);
    $this->assertArrayHasKey('/api/1/metastore/schemas/{schema_id}', $data["paths"]);
  }


  public function testGetCompleteYaml() {
    $controller = $this->getController();
    $response = $controller->getComplete('yaml');
    $this->assertEquals(200, $response->getStatusCode());
    $data = Yaml::decode($response->getContent(), TRUE);

    // Basic auth is included.
    $this->assertTrue(isset($data["components"]["securitySchemes"]["basic_auth"]));

    // Some sample paths from different modules are there
    $this->assertArrayHasKey('/api/1/datastore/imports', $data["paths"]);
    $this->assertArrayHasKey('/api/1/harvest/plans/{plan_id}', $data["paths"]);
    $this->assertArrayHasKey('/api/1/metastore/schemas/{schema_id}', $data["paths"]);
  }

  public function testGetNoAuth() {
    $controller = $this->getController(["authentication" => "false"]);
    $response = $controller->getComplete();

    // Simulate an authentication=false parameter in the request stack.
    $this->assertEquals(200, $response->getStatusCode());
    $data = json_decode($response->getContent(), TRUE);

    // Basic auth is excluded.
    $this->assertFalse(isset($data["components"]["securitySchemes"]["basic_auth"]));

    // Authorized paths are not there.
    $this->assertArrayNotHasKey('/api/1/datastore/imports', $data["paths"]);
    $this->assertArrayNotHasKey('/api/1/harvest/plans/{plan_id}', $data["paths"]);
    // Public paths still there.
    $this->assertArrayHasKey('/api/1/metastore/schemas/{schema_id}', $data["paths"]);
  }

  private function getController(array $params = []) {
    $requestStack = \Drupal::service('request_stack');
   
    if (!empty($params)) {
      $request = $requestStack->pop()->duplicate($params);
      $requestStack->push($request);
    }

    return new OpenApiController(
      $requestStack,
      \Drupal::service('dkan.common.docs_generator')
    );
  }

}
