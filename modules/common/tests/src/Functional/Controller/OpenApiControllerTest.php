<?php

namespace Drupal\Tests\common\Functional\Controller;

use Drupal\common\Controller\OpenApiController;
use Drupal\Core\Serialization\Yaml;
use Drupal\Tests\BrowserTestBase;

/**
 * @coversDefaultClass \Drupal\common\Controller\OpenApiController
 *
 * @group dkan
 * @group common
 * @group functional
 * @group btb
 * @group functional1
 */
class OpenApiControllerTest extends BrowserTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'harvest',
    'node',
  ];

  protected $defaultTheme = 'stark';

  public function testGetVersions() {
    $controller = $this->getController();
    $response = $controller->getVersions();
    $this->assertEquals(200, $response->getStatusCode());
    $data = json_decode((string) $response->getContent());
    $this->assertEquals(1, $data->version);
  }

  public function testGetCompleteJson() {
    $controller = $this->getController();
    $response = $controller->getComplete();
    $this->assertEquals(200, $response->getStatusCode());
    $data = json_decode((string) $response->getContent(), TRUE);

    // Basic auth is included.
    $this->assertTrue(isset($data['components']['securitySchemes']['basic_auth']));

    // Some sample paths from different modules are there
    $this->assertArrayHasKey('/api/1/datastore/imports', $data['paths']);
    $this->assertArrayHasKey('/api/1/harvest/plans/{plan_id}', $data['paths']);
    $this->assertArrayHasKey('/api/1/metastore/schemas/{schema_id}', $data['paths']);
  }

  public function testGetCompleteYaml() {
    $controller = $this->getController();
    $response = $controller->getComplete('yaml');
    $this->assertEquals(200, $response->getStatusCode());
    $data = Yaml::decode($response->getContent(), TRUE);

    // Basic auth is included.
    $this->assertTrue(isset($data['components']['securitySchemes']['basic_auth']));

    // Some sample paths from different modules are there
    $this->assertArrayHasKey('/api/1/datastore/imports', $data['paths']);
    $this->assertArrayHasKey('/api/1/harvest/plans/{plan_id}', $data['paths']);
    $this->assertArrayHasKey('/api/1/metastore/schemas/{schema_id}', $data['paths']);
  }

  public function testGetNoAuth() {
    $controller = $this->getController(['authentication' => 'false']);
    $response = $controller->getComplete();

    // Simulate an authentication=false parameter in the request stack.
    $this->assertEquals(200, $response->getStatusCode());
    $data = json_decode((string) $response->getContent(), TRUE);

    // Basic auth is excluded.
    $this->assertFalse(isset($data['components']['securitySchemes']['basic_auth']));

    // Authorized paths are not there.
    $this->assertArrayNotHasKey('/api/1/datastore/imports', $data['paths']);
    $this->assertArrayNotHasKey('/api/1/harvest/plans/{plan_id}', $data['paths']);
    // Public paths still there.
    $this->assertArrayHasKey('/api/1/metastore/schemas/{schema_id}', $data['paths']);
  }

  private function getController(array $params = []) {
    if ($params) {
      /** @var \Symfony\Component\HttpFoundation\RequestStack $requestStack */
      $requestStack = $this->container->get('request_stack');
      $request = $requestStack->pop()->duplicate($params);
      $requestStack->push($request);
    }

    return OpenApiController::create($this->container);
  }

}
