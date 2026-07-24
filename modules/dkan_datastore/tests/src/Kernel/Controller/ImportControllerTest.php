<?php

namespace Drupal\Tests\dkan_datastore\Kernel\Controller;

use Drupal\KernelTests\KernelTestBase;
use Drupal\dkan_datastore\Controller\ImportController;
use Drupal\dkan_metastore\Reference\ReferenceLookup;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Drupal\dkan_datastore\Controller\ImportController
 * @coversDefaultClass \Drupal\dkan_datastore\Controller\ImportController
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class ImportControllerTest extends KernelTestBase {

  protected static $modules = [
    'dkan_common',
    'dkan_datastore',
    'dkan_metastore',
  ];

  /**
   * @covers ::import
   *
   * @todo The actual response here is a 400 status. Make more tests.
   */
  public function testMultipleImports() {
    $webServiceApi = ImportController::create($this->container);
    $request = Request::create('http://blah/api');
    $result = $webServiceApi->import($request);

    $this->assertInstanceOf(JsonResponse::class, $result);
  }

  /**
   * Test getDependencies method in referenced mode.
   *
   * @covers ::getDependencies
   *
   * @dataProvider resourceIdProvider
   */
  public function testGetDependenciesFromResourceId(string $ref_mode, string $resource_id, int $expected_count) {
    $controller = ImportController::create($this->container);

    // Mock the referenceLookup service with distribution findings.
    $mock_lookup = $this->createMock(ReferenceLookup::class);
    $mock_lookup->method('getReferencers')
      ->willReturnCallback(function ($schemaId, $referenceId) use ($ref_mode) {
        if ($schemaId === 'distribution' && $ref_mode == 'referenced') {
          return ['dist1', 'dist2'];
        }
        if ($schemaId === 'dataset' && $ref_mode == 'non-referenced') {
          return ['dataset1', 'dataset2'];
        }
        return [];
      });

    $ref_property = new \ReflectionProperty($controller, 'referenceLookup');
    $ref_property->setAccessible(TRUE);
    $ref_property->setValue($controller, $mock_lookup);

    $ref_method = new \ReflectionMethod($controller, 'getDependencies');
    $ref_method->setAccessible(TRUE);

    $result = $ref_method->invokeArgs($controller, [$resource_id]);

    $this->assertIsArray($result);
    $key = $ref_mode === 'referenced' ? 'distribution' : 'dataset';
    if ($expected_count > 0) {
      $this->assertEquals($expected_count, count($result[$key]));
      $this->assertArrayHasKey($key, $result);
    }
    else {
      // If we expect no referencers, we should just get the list dependencies.
      $this->assertArrayNotHasKey($key, $result);
      $this->assertContains($key, $result);
    }
  }

  public static function resourceIdProvider() {
    $cases = [
      'id-version-source' => ['a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6__1784691862__source', 2],
      'id-version' => ['a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6__1784691862', 2],
      'short-id' => ['b1c2d3e4f5g6h7i8j9k0l1m2n3o4p5q6', 0],
      'uuid' => ['550e8400-e29b-41d4-a716-446655440000', 1],
      'invalid' => ['invalid', 0],
    ];
    $modes = ['referenced', 'non-referenced'];
    $data = [];
    foreach ($modes as $mode) {
      foreach ($cases as $case_name => $case_data) {
        $data["{$mode}-{$case_name}"] = array_merge([$mode], $case_data);
      }
    }
    return $data;
  }

}

